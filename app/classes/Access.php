<?php
namespace Core;


# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Access API
 *
 * @package CoreAPI
 * @subpackage AccessAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 */



class Access
{

	/**
	 * Function to be called when a user is attempting to access a page that
	 * he/she is not authorised to.  This outputs an access denied message then
	 * re-directs to the mainpage.
	 *
	 * @return void
	 */
	static function denied() {
		if( !\Core\Auth::is_user_authenticated() ) {
			if( basename( $_SERVER['SCRIPT_NAME'] ) != 'login_page.php' ) {
				$t_return_page = $_SERVER['SCRIPT_NAME'];
				if( isset( $_SERVER['QUERY_STRING'] ) ) {
					$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
				}
				$t_return_page = \Core\String::url( \Core\String::sanitize_url( $t_return_page ) );
				\Core\Print_Util::header_redirect( 'login_page.php?return=' . $t_return_page );
			}
		} else {
			if( \Core\Current_User::is_anonymous() ) {
				if( basename( $_SERVER['SCRIPT_NAME'] ) != 'login_page.php' ) {
					$t_return_page = $_SERVER['SCRIPT_NAME'];
					if( isset( $_SERVER['QUERY_STRING'] ) ) {
						$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
					}
					$t_return_page = \Core\String::url( \Core\String::sanitize_url( $t_return_page ) );
					echo '<p class="center">' . \Core\Error::string( ERROR_ACCESS_DENIED ) . '</p><p class="center">';
					\Core\Print_Util::bracket_link( \Core\Helper::mantis_url( 'login_page.php' ) . '?return=' . $t_return_page, \Core\Lang::get( 'click_to_login' ) );
					echo '</p><p class="center">';
					\Core\Print_Util::bracket_link( \Core\Helper::mantis_url( 'main_page.php' ), \Core\Lang::get( 'proceed' ) );
					echo '</p>';
				}
			} else {
				echo '<p class="center">' . \Core\Error::string( ERROR_ACCESS_DENIED ) . '</p>';
				echo '<p class="center">';
				\Core\Print_Util::bracket_link( \Core\Helper::mantis_url( 'main_page.php' ), \Core\Lang::get( 'proceed' ) );
				echo '</p>';
			}
		}
		exit;
	}
	
	/**
	 * retrieves and returns access matrix for a project from cache or caching if required.
	 * @param integer $p_project_id Integer representing project identifier.
	 * @return array returns an array of users->accesslevel for the given user
	 * @access private
	 */
	static function cache_matrix_project( $p_project_id ) {
		global $g_cache_access_matrix, $g_cache_access_matrix_project_ids;
	
		if( ALL_PROJECTS == (int)$p_project_id ) {
			return array();
		}
	
		if( !in_array( (int)$p_project_id, $g_cache_access_matrix_project_ids ) ) {
			$t_query = 'SELECT user_id, access_level FROM {project_user_list} WHERE project_id=' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( (int)$p_project_id ) );
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$g_cache_access_matrix[(int)$t_row['user_id']][(int)$p_project_id] = (int)$t_row['access_level'];
			}
	
			$g_cache_access_matrix_project_ids[] = (int)$p_project_id;
		}
	
		$t_results = array();
	
		foreach( $g_cache_access_matrix as $t_user ) {
			if( isset( $t_user[(int)$p_project_id] ) ) {
				$t_results[(int)$p_project_id] = $t_user[(int)$p_project_id];
			}
		}
	
		return $t_results;
	}
	
	/**
	 * retrieves and returns access matrix for a user from cache or caching if required.
	 * @param integer $p_user_id Integer representing user identifier.
	 * @return array returns an array of projects->accesslevel for the given user
	 * @access private
	 */
	static function cache_matrix_user( $p_user_id ) {
		global $g_cache_access_matrix, $g_cache_access_matrix_user_ids;
	
		if( !in_array( (int)$p_user_id, $g_cache_access_matrix_user_ids ) ) {
			$t_query = 'SELECT project_id, access_level FROM {project_user_list} WHERE user_id=' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( (int)$p_user_id ) );
	
			# make sure we always have an array to return
			$g_cache_access_matrix[(int)$p_user_id] = array();
	
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$g_cache_access_matrix[(int)$p_user_id][(int)$t_row['project_id']] = (int)$t_row['access_level'];
			}
	
			$g_cache_access_matrix_user_ids[] = (int)$p_user_id;
		}
	
		return $g_cache_access_matrix[(int)$p_user_id];
	}
	
	/**
	 * Check the a user's access against the given "threshold" and return true
	 * if the user can access, false otherwise.
	 * $p_threshold may be a single value, or an array. If it is a single
	 * value, treat it as a threshold so return true if user is >= threshold.
	 * If it is an array, look for exact matches to one of the values
	 * @param integer       $p_user_access_level User access level.
	 * @param integer|array $p_threshold         Access threshold, defaults to NOBODY.
	 * @return boolean true or false depending on whether given access level matches the threshold
	 * @access public
	 */
	static function compare_level( $p_user_access_level, $p_threshold = NOBODY ) {
		if( is_array( $p_threshold ) ) {
			return( in_array( $p_user_access_level, $p_threshold ) );
		} else {
			return( $p_user_access_level >= $p_threshold );
		}
	}
	
	/**
	 * This function only checks the user's global access level, ignoring any
	 * overrides they might have at a project level
	 * @param integer|null $p_user_id Integer representing user identifier, defaults to null to use current user.
	 * @return integer global access level
	 * @access public
	 */
	static function get_global_level( $p_user_id = null ) {
		if( $p_user_id === null ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		# Deal with not logged in silently in this case
		# @@@ we may be able to remove this and just error
		#     and once we default to anon login, we can remove it for sure
		if( empty( $p_user_id ) && !\Core\Auth::is_user_authenticated() ) {
			return false;
		}
	
		return \Core\User::get_field( $p_user_id, 'access_level' );
	}
	
	/**
	 * Check the current user's access against the given value and return true
	 * if the user's access is equal to or higher, false otherwise.
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @return boolean whether user has access level specified
	 * @access public
	 */
	static function has_global_level( $p_access_level, $p_user_id = null ) {
		# Short circuit the check in this case
		if( NOBODY == $p_access_level ) {
			return false;
		}
	
		if( $p_user_id === null ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		$t_access_level = \Core\Access::get_global_level( $p_user_id );
	
		return \Core\Access::compare_level( $t_access_level, $p_access_level );
	}
	
	/**
	 * Check if the user has the specified global access level
	 * and deny access to the page if not
	 * @see access_has_global_level
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @access public
	 * @return void
	 */
	static function ensure_global_level( $p_access_level, $p_user_id = null ) {
		if( !\Core\Access::has_global_level( $p_access_level, $p_user_id ) ) {
			\Core\Access::denied();
		}
	}
	
	/**
	 * This function checks the project access level first (for the current project
	 * if none is specified) and if the user is not listed, it falls back on the
	 * user's global access level.
	 * @param integer      $p_project_id Integer representing project id to check access against.
	 * @param integer|null $p_user_id    Integer representing user id, defaults to null to use current user.
	 * @return integer access level user has to given project
	 * @access public
	 */
	static function get_project_level( $p_project_id = null, $p_user_id = null ) {
		if( null === $p_user_id ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		# Deal with not logged in silently in this case
		# @todo we may be able to remove this and just error and once we default to anon login, we can remove it for sure
		if( empty( $p_user_id ) && !\Core\Auth::is_user_authenticated() ) {
			return ANYBODY;
		}
	
		if( null === $p_project_id ) {
			$p_project_id = \Core\Helper::get_current_project();
		}
	
		$t_global_access_level = \Core\Access::get_global_level( $p_user_id );
	
		if( ALL_PROJECTS == $p_project_id || \Core\User::is_administrator( $p_user_id ) ) {
			return $t_global_access_level;
		} else {
			$t_project_access_level = \Core\Access::get_local_level( $p_user_id, $p_project_id );
			$t_project_view_state = \Core\Project::get_field( $p_project_id, 'view_state' );
	
			# Try to use the project access level.
			# If the user is not listed in the project, then try to fall back
			#  to the global access level
			if( false === $t_project_access_level ) {
				# If the project is private and the user isn't listed, then they
				# must have the private_project_threshold access level to get in.
				if( VS_PRIVATE == $t_project_view_state ) {
					if( \Core\Access::compare_level( $t_global_access_level, \Core\Config::mantis_get( 'private_project_threshold', null, null, ALL_PROJECTS ) ) ) {
						return $t_global_access_level;
					} else {
						return ANYBODY;
					}
				} else {
					# project access not set, but the project is public
					return $t_global_access_level;
				}
			} else {
				# project specific access was set
				return $t_project_access_level;
			}
		}
	}
	
	/**
	 * Check the current user's access against the given value and return true
	 * if the user's access is equal to or higher, false otherwise.
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer      $p_project_id   Integer representing project id to check access against.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @return boolean whether user has access level specified
	 * @access public
	 */
	static function has_project_level( $p_access_level, $p_project_id = null, $p_user_id = null ) {
		# Short circuit the check in this case
		if( NOBODY == $p_access_level ) {
			return false;
		}
	
		if( null === $p_user_id ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
		if( null === $p_project_id ) {
			$p_project_id = \Core\Helper::get_current_project();
		}
	
		$t_access_level = \Core\Access::get_project_level( $p_project_id, $p_user_id );
	
		return \Core\Access::compare_level( $t_access_level, $p_access_level );
	}
	
	/**
	 * Check if the user has the specified access level for the given project
	 * and deny access to the page if not
	 * @see access_has_project_level
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer|null $p_project_id   Integer representing project id to check access against, defaults to null to use current project.
	 * @param integer|null $p_user_id      Integer representing user identifier, defaults to null to use current user.
	 * @access public
	 * @return void
	 */
	static function ensure_project_level( $p_access_level, $p_project_id = null, $p_user_id = null ) {
		if( !\Core\Access::has_project_level( $p_access_level, $p_project_id, $p_user_id ) ) {
			\Core\Access::denied();
		}
	}
	
	/**
	 * Check whether the user has the specified access level for any project project
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @return boolean whether user has access level specified
	 * @access public
	 */
	static function has_any_project( $p_access_level, $p_user_id = null ) {
		# Short circuit the check in this case
		if( NOBODY == $p_access_level ) {
			return false;
		}
	
		if( null === $p_user_id ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		$t_projects = \Core\Project::get_all_rows();
		foreach( $t_projects as $t_project ) {
			if( \Core\Access::has_project_level( $p_access_level, $t_project['id'], $p_user_id ) ) {
				return true;
			}
		}
	
		return false;
	}
	
	/**
	 * Check the current user's access against the given value and return true
	 * if the user's access is equal to or higher, false otherwise.
	 * This function looks up the bug's project and performs an access check
	 * against that project
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer      $p_bug_id       Integer representing bug id to check access against.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @return boolean whether user has access level specified
	 * @access public
	 */
	static function has_bug_level( $p_access_level, $p_bug_id, $p_user_id = null ) {
		if( $p_user_id === null ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		# Deal with not logged in silently in this case
		# @@@ we may be able to remove this and just error
		#     and once we default to anon login, we can remove it for sure
		if( empty( $p_user_id ) && !\Core\Auth::is_user_authenticated() ) {
			return false;
		}
	
		$t_project_id = \Core\Bug::get_field( $p_bug_id, 'project_id' );
		$t_bug_is_user_reporter = \Core\Bug::is_user_reporter( $p_bug_id, $p_user_id );
		$t_access_level = \Core\Access::get_project_level( $t_project_id, $p_user_id );
	
		# check limit_Reporter (Issue #4769)
		# reporters can view just issues they reported
		$t_limit_reporters = \Core\Config::mantis_get( 'limit_reporters', null, $p_user_id, $t_project_id );
		if( $t_limit_reporters && !$t_bug_is_user_reporter ) {
			# Here we only need to check that the current user has an access level
			# higher than the lowest needed to report issues (report_bug_threshold).
			# To improve performance, esp. when processing for several projects, we
			# build a static array holding that threshold for each project
			static $s_thresholds = array();
			if( !isset( $s_thresholds[$t_project_id] ) ) {
				$t_report_bug_threshold = \Core\Config::mantis_get( 'report_bug_threshold', null, $p_user_id, $t_project_id );
				if( !is_array( $t_report_bug_threshold ) ) {
					$s_thresholds[$t_project_id] = $t_report_bug_threshold + 1;
				} else if( empty( $t_report_bug_threshold ) ) {
					$s_thresholds[$t_project_id] = NOBODY;
				} else {
					sort( $t_report_bug_threshold );
					$s_thresholds[$t_project_id] = $t_report_bug_threshold[0] + 1;
				}
			}
			if( !\Core\Access::compare_level( $t_access_level, $s_thresholds[$t_project_id] ) ) {
				return false;
			}
		}
	
		# If the bug is private and the user is not the reporter, then
		# they must also have higher access than private_bug_threshold
		if( !$t_bug_is_user_reporter && \Core\Bug::get_field( $p_bug_id, 'view_state' ) == VS_PRIVATE ) {
			$t_private_bug_threshold = \Core\Config::mantis_get( 'private_bug_threshold', null, $p_user_id, $t_project_id );
			return \Core\Access::compare_level( $t_access_level, $t_private_bug_threshold )
				&& \Core\Access::compare_level( $t_access_level, $p_access_level );
		}
	
		return \Core\Access::compare_level( $t_access_level, $p_access_level );
	}
	
	/**
	 * Check if the user has the specified access level for the given bug
	 * and deny access to the page if not
	 * @see access_has_bug_level
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer      $p_bug_id       Integer representing bug id to check access against.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @return void
	 * @access public
	 */
	static function ensure_bug_level( $p_access_level, $p_bug_id, $p_user_id = null ) {
		if( !\Core\Access::has_bug_level( $p_access_level, $p_bug_id, $p_user_id ) ) {
			\Core\Access::denied();
		}
	}
	
	/**
	 * Check the current user's access against the given value and return true
	 * if the user's access is equal to or higher, false otherwise.
	 * This function looks up the bugnote's bug and performs an access check
	 * against that bug
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer      $p_bugnote_id   Integer representing bugnote id to check access against.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @return boolean whether user has access level specified
	 * @access public
	 */
	static function has_bugnote_level( $p_access_level, $p_bugnote_id, $p_user_id = null ) {
		if( null === $p_user_id ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		$t_bug_id = \Core\Bug\Note::get_field( $p_bugnote_id, 'bug_id' );
		$t_project_id = \Core\Bug::get_field( $t_bug_id, 'project_id' );
	
		# If the bug is private and the user is not the reporter, then the
		# the user must also have higher access than private_bug_threshold
		if( \Core\Bug\Note::get_field( $p_bugnote_id, 'view_state' ) == VS_PRIVATE && !\Core\Bug\Note::is_user_reporter( $p_bugnote_id, $p_user_id ) ) {
			$t_private_bugnote_threshold = \Core\Config::mantis_get( 'private_bugnote_threshold', null, $p_user_id, $t_project_id );
			$p_access_level = max( $p_access_level, $t_private_bugnote_threshold );
		}
	
		return \Core\Access::has_bug_level( $p_access_level, $t_bug_id, $p_user_id );
	}
	
	/**
	 * Check if the user has the specified access level for the given bugnote
	 * and deny access to the page if not
	 * @see access_has_bugnote_level
	 * @param integer      $p_access_level Integer representing access level.
	 * @param integer      $p_bugnote_id   Integer representing bugnote id to check access against.
	 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
	 * @access public
	 * @return void
	 */
	static function ensure_bugnote_level( $p_access_level, $p_bugnote_id, $p_user_id = null ) {
		if( !\Core\Access::has_bugnote_level( $p_access_level, $p_bugnote_id, $p_user_id ) ) {
			\Core\Access::denied();
		}
	}
	
	/**
	 * Check if the specified bug can be closed
	 * @param \Core\BugData      $p_bug     Bug to check access against.
	 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
	 * @return boolean true if user can close the bug
	 * @access public
	 */
	static function can_close_bug( \Core\BugData $p_bug, $p_user_id = null ) {
		if( \Core\Bug::is_closed( $p_bug->id ) ) {
			# Can't close a bug that's already closed
			return false;
		}
	
		if( null === $p_user_id ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		# If allow_reporter_close is enabled, then reporters can close their own bugs
		# if they are in resolved status
		if( ON == \Core\Config::mantis_get( 'allow_reporter_close', null, null, $p_bug->project_id )
			&& \Core\Bug::is_user_reporter( $p_bug->id, $p_user_id )
			&& \Core\Bug::is_resolved( $p_bug->id )
		) {
			return true;
		}
	
		$t_closed_status = \Core\Config::mantis_get( 'bug_closed_status_threshold', null, null, $p_bug->project_id );
		$t_closed_status_threshold = \Core\Access::get_status_threshold( $t_closed_status, $p_bug->project_id );
		return \Core\Access::has_bug_level( $t_closed_status_threshold, $p_bug->id, $p_user_id );
	}
	
	/**
	 * Make sure that the user can close the specified bug
	 * @see access_can_close_bug
	 * @param \Core\BugData      $p_bug     Bug to check access against.
	 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
	 * @access public
	 * @return void
	 */
	static function ensure_can_close_bug( \Core\BugData $p_bug, $p_user_id = null ) {
		if( !\Core\Access::can_close_bug( $p_bug, $p_user_id ) ) {
			\Core\Access::denied();
		}
	}
	
	/**
	 * Check if the specified bug can be reopened
	 * @param \Core\BugData      $p_bug     Bug to check access against.
	 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
	 * @return boolean whether user has access to reopen bugs
	 * @access public
	 */
	static function can_reopen_bug( \Core\BugData $p_bug, $p_user_id = null ) {
		if( !\Core\Bug::is_resolved( $p_bug->id ) ) {
			# Can't reopen a bug that's not resolved
			return false;
		}
	
		if( $p_user_id === null ) {
			$p_user_id = \Core\Auth::get_current_user_id();
		}
	
		# If allow_reporter_reopen is enabled, then reporters can always reopen
		# their own bugs as long as their access level is reporter or above
		if( ON == \Core\Config::mantis_get( 'allow_reporter_reopen', null, null, $p_bug->project_id )
			&& \Core\Bug::is_user_reporter( $p_bug->id, $p_user_id )
			&& \Core\Access::has_project_level( \Core\Config::mantis_get( 'report_bug_threshold', null, $p_user_id, $p_bug->project_id ), $p_bug->project_id, $p_user_id )
		) {
			return true;
		}
	
		# Other users's access level must allow them to reopen bugs
		$t_reopen_bug_threshold = \Core\Config::mantis_get( 'reopen_bug_threshold', null, null, $p_bug->project_id );
		if( \Core\Access::has_bug_level( $t_reopen_bug_threshold, $p_bug->id, $p_user_id ) ) {
			$t_reopen_status = \Core\Config::mantis_get( 'bug_reopen_status', null, null, $p_bug->project_id );
	
			# User must be allowed to change status to reopen status
			$t_reopen_status_threshold = \Core\Access::get_status_threshold( $t_reopen_status, $p_bug->project_id );
			return \Core\Access::has_bug_level( $t_reopen_status_threshold, $p_bug->id, $p_user_id );
		}
	
		return false;
	}
	
	/**
	 * Make sure that the user can reopen the specified bug.
	 * Calls access_denied if user has no access to terminate script
	 * @see access_can_reopen_bug
	 * @param \Core\BugData      $p_bug     Bug to check access against.
	 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
	 * @access public
	 * @return void
	 */
	static function ensure_can_reopen_bug( \Core\BugData $p_bug, $p_user_id = null ) {
		if( !\Core\Access::can_reopen_bug( $p_bug, $p_user_id ) ) {
			\Core\Access::denied();
		}
	}
	
	/**
	 * get the user's access level specific to this project.
	 * return false (0) if the user has no access override here
	 * @param integer $p_user_id    Integer representing user id.
	 * @param integer $p_project_id Integer representing project id.
	 * @return boolean|integer returns false (if no access) or an integer representing level of access
	 * @access public
	 */
	static function get_local_level( $p_user_id, $p_project_id ) {
		global $g_cache_access_matrix, $g_cache_access_matrix_project_ids;
	
		$p_project_id = (int)$p_project_id;
		$p_user_id = (int)$p_user_id;
	
		if( in_array( $p_project_id, $g_cache_access_matrix_project_ids ) ) {
			if( isset( $g_cache_access_matrix[$p_user_id][$p_project_id] ) ) {
				return $g_cache_access_matrix[$p_user_id][$p_project_id];
			} else {
				return false;
			}
		}
	
		$t_project_level = \Core\Access::cache_matrix_user( $p_user_id );
	
		if( isset( $t_project_level[$p_project_id] ) ) {
			return $t_project_level[$p_project_id];
		} else {
			return false;
		}
	}
	
	/**
	 * get the access level required to change the issue to the new status
	 * If there is no specific differentiated access level, use the
	 * generic update_bug_status_threshold.
	 * @param integer $p_status     Status.
	 * @param integer $p_project_id Default value ALL_PROJECTS.
	 * @return integer integer representing user level e.g. DEVELOPER
	 * @access public
	 */
	static function get_status_threshold( $p_status, $p_project_id = ALL_PROJECTS ) {
		$t_thresh_array = \Core\Config::mantis_get( 'set_status_threshold', null, null, $p_project_id );
		if( isset( $t_thresh_array[(int)$p_status] ) ) {
			return (int)$t_thresh_array[(int)$p_status];
		} else {
			if( $p_status == \Core\Config::mantis_get( 'bug_submit_status', null, null, $p_project_id ) ) {
				return \Core\Config::mantis_get( 'report_bug_threshold', null, null, $p_project_id );
			} else {
				return \Core\Config::mantis_get( 'update_bug_status_threshold', null, null, $p_project_id );
			}
		}
	}

}