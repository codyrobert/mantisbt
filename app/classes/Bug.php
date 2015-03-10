<?php
namespace Flickerbox;


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
 * Bug API
 *
 * @package CoreAPI
 * @subpackage BugAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bugnote_api.php
 * @uses bug_revision_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses date_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses tag_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'custom_field_api.php' );



class Bug
{

	/**
	 * Cache a database result-set containing full contents of bug_table row.
	 * @param array $p_bug_database_result Database row containing all columns from mantis_bug_table.
	 * @param array $p_stats               An optional array representing bugnote statistics.
	 * @return array returns an array representing the bug row if bug exists
	 * @access public
	 */
	static function cache_database_result( array $p_bug_database_result, array $p_stats = null ) {
		global $g_cache_bug;
	
		if( !is_array( $p_bug_database_result ) || isset( $g_cache_bug[(int)$p_bug_database_result['id']] ) ) {
			return $g_cache_bug[(int)$p_bug_database_result['id']];
		}
	
		return \Flickerbox\Bug::add_to_cache( $p_bug_database_result, $p_stats );
	}
	
	/**
	 * Cache a bug row if necessary and return the cached copy
	 * @param integer $p_bug_id         Identifier of bug to cache from mantis_bug_table.
	 * @param boolean $p_trigger_errors Set to true to trigger an error if the bug does not exist.
	 * @return boolean|array returns an array representing the bug row if bug exists or false if bug does not exist
	 * @access public
	 * @uses database_api.php
	 */
	static function cache_row( $p_bug_id, $p_trigger_errors = true ) {
		global $g_cache_bug;
	
		if( isset( $g_cache_bug[$p_bug_id] ) ) {
			return $g_cache_bug[$p_bug_id];
		}
	
		$c_bug_id = (int)$p_bug_id;
	
		$t_query = 'SELECT * FROM {bug} WHERE id=' . \Flickerbox\Database::param();
		$t_result = \Flickerbox\Database::query( $t_query, array( $c_bug_id ) );
	
		$t_row = \Flickerbox\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			$g_cache_bug[$c_bug_id] = false;
	
			if( $p_trigger_errors ) {
				\Flickerbox\Error::parameters( $p_bug_id );
				trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}
	
		return \Flickerbox\Bug::add_to_cache( $t_row );
	}
	
	/**
	 * Cache a set of bugs
	 * @param array $p_bug_id_array Integer array representing bug identifiers to cache.
	 * @return void
	 * @access public
	 * @uses database_api.php
	 */
	static function cache_array_rows( array $p_bug_id_array ) {
		global $g_cache_bug;
		$c_bug_id_array = array();
	
		foreach( $p_bug_id_array as $t_bug_id ) {
			if( !isset( $g_cache_bug[(int)$t_bug_id] ) ) {
				$c_bug_id_array[] = (int)$t_bug_id;
			}
		}
	
		if( empty( $c_bug_id_array ) ) {
			return;
		}
	
		$t_query = 'SELECT * FROM {bug} WHERE id IN (' . implode( ',', $c_bug_id_array ) . ')';
		$t_result = \Flickerbox\Database::query( $t_query );
	
		while( $t_row = \Flickerbox\Database::fetch_array( $t_result ) ) {
			\Flickerbox\Bug::add_to_cache( $t_row );
		}
		return;
	}
	
	/**
	 * Inject a bug into the bug cache
	 * @param array $p_bug_row A bug row to cache.
	 * @param array $p_stats   Bugnote stats to cache.
	 * @return array
	 * @access private
	 */
	static function add_to_cache( array $p_bug_row, array $p_stats = null ) {
		global $g_cache_bug;
	
		$g_cache_bug[(int)$p_bug_row['id']] = $p_bug_row;
	
		if( !is_null( $p_stats ) ) {
			$g_cache_bug[(int)$p_bug_row['id']]['_stats'] = $p_stats;
		}
	
		return $g_cache_bug[(int)$p_bug_row['id']];
	}
	
	/**
	 * Clear a bug from the cache or all bugs if no bug id specified.
	 * @param integer $p_bug_id A bug identifier to clear (optional).
	 * @return boolean
	 * @access public
	 */
	static function clear_cache( $p_bug_id = null ) {
		global $g_cache_bug;
	
		if( null === $p_bug_id ) {
			$g_cache_bug = array();
		} else {
			unset( $g_cache_bug[(int)$p_bug_id] );
		}
	
		return true;
	}
	
	/**
	 * Cache a bug text row if necessary and return the cached copy
	 * @param integer $p_bug_id         Integer bug id to retrieve text for.
	 * @param boolean $p_trigger_errors If the second parameter is true (default), trigger an error if bug text not found.
	 * @return boolean|array returns false if not bug text found or array of bug text
	 * @access public
	 * @uses database_api.php
	 */
	static function text_cache_row( $p_bug_id, $p_trigger_errors = true ) {
		global $g_cache_bug_text;
	
		$c_bug_id = (int)$p_bug_id;
	
		if( isset( $g_cache_bug_text[$c_bug_id] ) ) {
			return $g_cache_bug_text[$c_bug_id];
		}
	
		$t_query = 'SELECT bt.* FROM {bug_text} bt, {bug} b
					  WHERE b.id=' . \Flickerbox\Database::param() . ' AND b.bug_text_id = bt.id';
		$t_result = \Flickerbox\Database::query( $t_query, array( $c_bug_id ) );
	
		$t_row = \Flickerbox\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			$g_cache_bug_text[$c_bug_id] = false;
	
			if( $p_trigger_errors ) {
				\Flickerbox\Error::parameters( $p_bug_id );
				trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}
	
		$g_cache_bug_text[$c_bug_id] = $t_row;
	
		return $t_row;
	}
	
	/**
	 * Clear a bug's bug text from the cache or all bug text if no bug id specified.
	 * @param integer $p_bug_id A bug identifier to clear (optional).
	 * @return boolean
	 * @access public
	 */
	static function text_clear_cache( $p_bug_id = null ) {
		global $g_cache_bug_text;
	
		if( null === $p_bug_id ) {
			$g_cache_bug_text = array();
		} else {
			unset( $g_cache_bug_text[(int)$p_bug_id] );
		}
	
		return true;
	}
	
	/**
	 * Check if a bug exists
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean true if bug exists, false otherwise
	 * @access public
	 */
	static function exists( $p_bug_id ) {
		if( false == \Flickerbox\Bug::cache_row( $p_bug_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Check if a bug exists. If it doesn't then trigger an error
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return void
	 * @access public
	 */
	static function ensure_exists( $p_bug_id ) {
		if( !\Flickerbox\Bug::exists( $p_bug_id ) ) {
			\Flickerbox\Error::parameters( $p_bug_id );
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		}
	}
	
	/**
	 * check if the given user is the reporter of the bug
	 * @param integer $p_bug_id  Integer representing bug identifier.
	 * @param integer $p_user_id Integer representing a user identifier.
	 * @return boolean return true if the user is the reporter, false otherwise
	 * @access public
	 */
	static function is_user_reporter( $p_bug_id, $p_user_id ) {
		if( \Flickerbox\Bug::get_field( $p_bug_id, 'reporter_id' ) == $p_user_id ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * check if the given user is the handler of the bug
	 * @param integer $p_bug_id  Integer representing bug identifier.
	 * @param integer $p_user_id Integer representing a user identifier.
	 * @return boolean return true if the user is the handler, false otherwise
	 * @access public
	 */
	static function is_user_handler( $p_bug_id, $p_user_id ) {
		if( \Flickerbox\Bug::get_field( $p_bug_id, 'handler_id' ) == $p_user_id ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check if the bug is readonly and shouldn't be modified
	 * For a bug to be readonly the status has to be >= bug_readonly_status_threshold and
	 * current user access level < update_readonly_bug_threshold.
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean
	 * @access public
	 * @uses access_api.php
	 * @uses config_api.php
	 */
	static function is_readonly( $p_bug_id ) {
		$t_status = \Flickerbox\Bug::get_field( $p_bug_id, 'status' );
		if( $t_status < \Flickerbox\Config::mantis_get( 'bug_readonly_status_threshold' ) ) {
			return false;
		}
	
		if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_readonly_bug_threshold' ), $p_bug_id ) ) {
			return false;
		}
	
		return true;
	}
	
	/**
	 * Check if a given bug is resolved
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean true if bug is resolved, false otherwise
	 * @access public
	 * @uses config_api.php
	 */
	static function is_resolved( $p_bug_id ) {
		$t_bug = \Flickerbox\Bug::get( $p_bug_id );
		return( $t_bug->status >= \Flickerbox\Config::mantis_get( 'bug_resolved_status_threshold', null, null, $t_bug->project_id ) );
	}
	
	/**
	 * Check if a given bug is closed
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean true if bug is closed, false otherwise
	 * @access public
	 * @uses config_api.php
	 */
	static function is_closed( $p_bug_id ) {
		$t_bug = \Flickerbox\Bug::get( $p_bug_id );
		return( $t_bug->status >= \Flickerbox\Config::mantis_get( 'bug_closed_status_threshold', null, null, $t_bug->project_id ) );
	}
	
	/**
	 * Check if a given bug is overdue
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean true if bug is overdue, false otherwise
	 * @access public
	 * @uses database_api.php
	 */
	static function is_overdue( $p_bug_id ) {
		$t_due_date = \Flickerbox\Bug::get_field( $p_bug_id, 'due_date' );
		if( !\Flickerbox\Date::is_null( $t_due_date ) ) {
			$t_now = \Flickerbox\Database::now();
			if( $t_now > $t_due_date ) {
				if( !\Flickerbox\Bug::is_resolved( $p_bug_id ) ) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Validate workflow state to see if bug can be moved to requested state
	 * @param integer $p_bug_status    Current bug status.
	 * @param integer $p_wanted_status New bug status.
	 * @return boolean
	 * @access public
	 * @uses config_api.php
	 * @uses utility_api.php
	 */
	static function check_workflow( $p_bug_status, $p_wanted_status ) {
		$t_status_enum_workflow = \Flickerbox\Config::mantis_get( 'status_enum_workflow' );
	
		if( count( $t_status_enum_workflow ) < 1 ) {
			# workflow not defined, use default enum
			return true;
		}
	
		if( $p_bug_status == $p_wanted_status ) {
			# no change in state, allow the transition
			return true;
		}
	
		# There should always be a possible next status, if not defined, then allow all.
		if( !isset( $t_status_enum_workflow[$p_bug_status] ) ) {
			return true;
		}
	
		# workflow defined - find allowed states
		$t_allowed_states = $t_status_enum_workflow[$p_bug_status];
	
		return \Flickerbox\MantisEnum::hasValue( $t_allowed_states, $p_wanted_status );
	}
	
	/**
	 * Copy a bug from one project to another. Also make copies of issue notes, attachments, history,
	 * email notifications etc.
	 * @param integer $p_bug_id                A bug identifier.
	 * @param integer $p_target_project_id     A target project identifier.
	 * @param boolean $p_copy_custom_fields    Whether to copy custom fields.
	 * @param boolean $p_copy_relationships    Whether to copy relationships.
	 * @param boolean $p_copy_history          Whether to copy history.
	 * @param boolean $p_copy_attachments      Whether to copy attachments.
	 * @param boolean $p_copy_bugnotes         Whether to copy bugnotes.
	 * @param boolean $p_copy_monitoring_users Whether to copy monitoring users.
	 * @return integer representing the new bug identifier
	 * @access public
	 */
	static function copy_bug( $p_bug_id, $p_target_project_id = null, $p_copy_custom_fields = false, $p_copy_relationships = false, $p_copy_history = false, $p_copy_attachments = false, $p_copy_bugnotes = false, $p_copy_monitoring_users = false ) {
		global $g_db;
	
		$t_bug_id = (int)$p_bug_id;
		$t_target_project_id = (int)$p_target_project_id;
	
		$t_bug_data = \Flickerbox\Bug::get( $t_bug_id, true );
	
		# retrieve the project id associated with the bug
		if( ( $p_target_project_id == null ) || \Flickerbox\Utility::is_blank( $p_target_project_id ) ) {
			$t_target_project_id = $t_bug_data->project_id;
		}
	
		$t_bug_data->project_id = $t_target_project_id;
		$t_bug_data->reporter_id = \Flickerbox\Auth::get_current_user_id();
		$t_bug_data->date_submitted = \Flickerbox\Database::now();
		$t_bug_data->last_updated = \Flickerbox\Database::now();
	
		$t_new_bug_id = $t_bug_data->create();
	
		# MASC ATTENTION: IF THE SOURCE BUG HAS TO HANDLER THE bug_create FUNCTION CAN TRY TO AUTO-ASSIGN THE BUG
		# WE FORCE HERE TO DUPLICATE THE SAME HANDLER OF THE SOURCE BUG
		# @todo VB: Shouldn't we check if the handler in the source project is also a handler in the destination project?
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'handler_id', $t_bug_data->handler_id );
	
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'duplicate_id', $t_bug_data->duplicate_id );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'status', $t_bug_data->status );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'resolution', $t_bug_data->resolution );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'projection', $t_bug_data->projection );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'eta', $t_bug_data->eta );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'fixed_in_version', $t_bug_data->fixed_in_version );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'target_version', $t_bug_data->target_version );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'sponsorship_total', 0 );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'sticky', 0 );
		\Flickerbox\Bug::set_field( $t_new_bug_id, 'due_date', $t_bug_data->due_date );
	
		# COPY CUSTOM FIELDS
		if( $p_copy_custom_fields ) {
			$t_query = 'SELECT field_id, bug_id, value FROM {custom_field_string} WHERE bug_id=' . \Flickerbox\Database::param();
			$t_result = \Flickerbox\Database::query( $t_query, array( $t_bug_id ) );
	
			while( $t_bug_custom = \Flickerbox\Database::fetch_array( $t_result ) ) {
				$c_field_id = (int)$t_bug_custom['field_id'];
				$c_new_bug_id = (int)$t_new_bug_id;
				$c_value = $t_bug_custom['value'];
	
				$t_query = 'INSERT INTO {custom_field_string}
							   ( field_id, bug_id, value )
							   VALUES (' . \Flickerbox\Database::param() . ', ' . \Flickerbox\Database::param() . ', ' . \Flickerbox\Database::param() . ')';
				\Flickerbox\Database::query( $t_query, array( $c_field_id, $c_new_bug_id, $c_value ) );
			}
		}
	
		# Copy Relationships
		if( $p_copy_relationships ) {
			\Flickerbox\Relationship::copy_all( $t_bug_id, $t_new_bug_id );
		}
	
		# Copy bugnotes
		if( $p_copy_bugnotes ) {
			$t_query = 'SELECT * FROM {bugnote} WHERE bug_id=' . \Flickerbox\Database::param();
			$t_result = \Flickerbox\Database::query( $t_query, array( $t_bug_id ) );
	
			while( $t_bug_note = \Flickerbox\Database::fetch_array( $t_result ) ) {
				$t_bugnote_text_id = $t_bug_note['bugnote_text_id'];
	
				$t_query2 = 'SELECT * FROM {bugnote_text} WHERE id=' . \Flickerbox\Database::param();
				$t_result2 = \Flickerbox\Database::query( $t_query2, array( $t_bugnote_text_id ) );
	
				$t_bugnote_text_insert_id = -1;
				if( $t_bugnote_text = \Flickerbox\Database::fetch_array( $t_result2 ) ) {
					$t_query2 = 'INSERT INTO {bugnote_text}
								   ( note )
								   VALUES ( ' . \Flickerbox\Database::param() . ' )';
					\Flickerbox\Database::query( $t_query2, array( $t_bugnote_text['note'] ) );
					$t_bugnote_text_insert_id = \Flickerbox\Database::insert_id( \Flickerbox\Database::get_table( 'bugnote_text' ) );
				}
	
				$t_query2 = 'INSERT INTO {bugnote}
							   ( bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
							   VALUES ( ' . \Flickerbox\Database::param() . ',
							   			' . \Flickerbox\Database::param() . ',
							   			' . \Flickerbox\Database::param() . ',
							   			' . \Flickerbox\Database::param() . ',
							   			' . \Flickerbox\Database::param() . ',
							   			' . \Flickerbox\Database::param() . ')';
				\Flickerbox\Database::query( $t_query2, array( $t_new_bug_id, $t_bug_note['reporter_id'], $t_bugnote_text_insert_id, $t_bug_note['view_state'], $t_bug_note['date_submitted'], $t_bug_note['last_modified'] ) );
			}
		}
	
		# Copy attachments
		if( $p_copy_attachments ) {
		    \Flickerbox\File::copy_attachments( $t_bug_id, $t_new_bug_id );
		}
	
		# Copy users monitoring bug
		if( $p_copy_monitoring_users ) {
			\Flickerbox\Bug::monitor_copy( $t_bug_id, $t_new_bug_id );
		}
	
		# COPY HISTORY
		\Flickerbox\History::delete( $t_new_bug_id );	# should history only be deleted inside the if statement below?
		if( $p_copy_history ) {
			# @todo problem with this code: the generated history trail is incorrect because the note IDs are those of the original bug, not the copied ones
			# @todo actually, does it even make sense to copy the history ?
			$t_query = 'SELECT * FROM {bug_history} WHERE bug_id = ' . \Flickerbox\Database::param();
			$t_result = \Flickerbox\Database::query( $t_query, array( $t_bug_id ) );
	
			while( $t_bug_history = \Flickerbox\Database::fetch_array( $t_result ) ) {
				$t_query = 'INSERT INTO {bug_history}
							  ( user_id, bug_id, date_modified, field_name, old_value, new_value, type )
							  VALUES ( ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
							  		   ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
							  		   ' . \Flickerbox\Database::param() . ' );';
				\Flickerbox\Database::query( $t_query, array( $t_bug_history['user_id'], $t_new_bug_id, $t_bug_history['date_modified'], $t_bug_history['field_name'], $t_bug_history['old_value'], $t_bug_history['new_value'], $t_bug_history['type'] ) );
			}
		} else {
			# Create a "New Issue" history entry
			\Flickerbox\History::log_event_special( $t_new_bug_id, NEW_BUG );
		}
	
		# Create history entries to reflect the copy operation
		\Flickerbox\History::log_event_special( $t_new_bug_id, BUG_CREATED_FROM, '', $t_bug_id );
		\Flickerbox\History::log_event_special( $t_bug_id, BUG_CLONED_TO, '', $t_new_bug_id );
	
		return $t_new_bug_id;
	}
	
	/**
	 * Moves an issue from a project to another.
	 *
	 * @todo Validate with sub-project / category inheritance scenarios.
	 * @param integer $p_bug_id            The bug to be moved.
	 * @param integer $p_target_project_id The target project to move the bug to.
	 * @return void
	 * @access public
	 */
	static function move( $p_bug_id, $p_target_project_id ) {
		# Attempt to move disk based attachments to new project file directory.
		\Flickerbox\File::move_bug_attachments( $p_bug_id, $p_target_project_id );
	
		# Move the issue to the new project.
		\Flickerbox\Bug::set_field( $p_bug_id, 'project_id', $p_target_project_id );
	
		# Update the category if needed
		$t_category_id = \Flickerbox\Bug::get_field( $p_bug_id, 'category_id' );
	
		# Bug has no category
		if( $t_category_id == 0 ) {
			# Category is required in target project, set it to default
			if( ON != \Flickerbox\Config::mantis_get( 'allow_no_category', null, null, $p_target_project_id ) ) {
				\Flickerbox\Bug::set_field( $p_bug_id, 'category_id', \Flickerbox\Config::mantis_get( 'default_category_for_moves', null, null, $p_target_project_id ) );
			}
		} else {
			# Check if the category is global, and if not attempt mapping it to the new project
			$t_category_project_id = \Flickerbox\Category::get_field( $t_category_id, 'project_id' );
	
			if( $t_category_project_id != ALL_PROJECTS
			  && !in_array( $t_category_project_id, \Flickerbox\Project\Hierarchy::inheritance( $p_target_project_id ) )
			) {
				# Map by name
				$t_category_name = \Flickerbox\Category::get_field( $t_category_id, 'name' );
				$t_target_project_category_id = \Flickerbox\Category::get_id_by_name( $t_category_name, $p_target_project_id, false );
				if( $t_target_project_category_id === false ) {
					# Use target project's default category for moves, since there is no match by name.
					$t_target_project_category_id = \Flickerbox\Config::mantis_get( 'default_category_for_moves', null, null, $p_target_project_id );
				}
				\Flickerbox\Bug::set_field( $p_bug_id, 'category_id', $t_target_project_category_id );
			}
		}
	}
	
	/**
	 * allows bug deletion :
	 * delete the bug, bugtext, bugnote, and bugtexts selected
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return void
	 * @access public
	 */
	static function delete( $p_bug_id ) {
		$c_bug_id = (int)$p_bug_id;
	
		# call pre-deletion custom function
		\Flickerbox\Helper::call_custom_function( 'issue_delete_validate', array( $p_bug_id ) );
	
		# log deletion of bug
		\Flickerbox\History::log_event_special( $p_bug_id, BUG_DELETED, \Flickerbox\Bug::format_id( $p_bug_id ) );
	
		\Flickerbox\Email::generic( $p_bug_id, 'deleted', 'email_notification_title_for_action_bug_deleted' );
	
		# call post-deletion custom function.  We call this here to allow the custom function to access the details of the bug before
		# they are deleted from the database given it's id.  The other option would be to move this to the end of the function and
		# provide it with bug data rather than an id, but this will break backward compatibility.
		\Flickerbox\Helper::call_custom_function( 'issue_delete_notify', array( $p_bug_id ) );
	
		# Unmonitor bug for all users
		\Flickerbox\Bug::unmonitor( $p_bug_id, null );
	
		# Delete custom fields
		custom_field_delete_all_values( $p_bug_id );
	
		# Delete bugnotes
		\Flickerbox\Bug\Note::delete_all( $p_bug_id );
	
		# Delete all sponsorships
		\Flickerbox\Sponsorship::delete_all( $p_bug_id );
	
		# MASC RELATIONSHIP
		# we delete relationships even if the feature is currently off.
		\Flickerbox\Relationship::delete_all( $p_bug_id );
	
		# MASC RELATIONSHIP
		# Delete files
		\Flickerbox\File::delete_attachments( $p_bug_id );
	
		# Detach tags
		\Flickerbox\Tag::bug_detach_all( $p_bug_id, false );
	
		# Delete the bug history
		\Flickerbox\History::delete( $p_bug_id );
	
		# Delete bug info revisions
		\Flickerbox\Bug\Revision::delete( $p_bug_id );
	
		# Delete the bugnote text
		$t_bug_text_id = \Flickerbox\Bug::get_field( $p_bug_id, 'bug_text_id' );
	
		$t_query = 'DELETE FROM {bug_text} WHERE id=' . \Flickerbox\Database::param();
		\Flickerbox\Database::query( $t_query, array( $t_bug_text_id ) );
	
		# Delete the bug entry
		$t_query = 'DELETE FROM {bug} WHERE id=' . \Flickerbox\Database::param();
		\Flickerbox\Database::query( $t_query, array( $c_bug_id ) );
	
		\Flickerbox\Bug::clear_cache( $p_bug_id );
		\Flickerbox\Bug::text_clear_cache( $p_bug_id );
	}
	
	/**
	 * Delete all bugs associated with a project
	 * @param integer $p_project_id Integer representing a project identifier.
	 * @access public
	 * @uses database_api.php
	 * @return void
	 */
	static function delete_all( $p_project_id ) {
		$c_project_id = (int)$p_project_id;
	
		$t_query = 'SELECT id FROM {bug} WHERE project_id=' . \Flickerbox\Database::param();
		$t_result = \Flickerbox\Database::query( $t_query, array( $c_project_id ) );
	
		while( $t_row = \Flickerbox\Database::fetch_array( $t_result ) ) {
			\Flickerbox\Bug::delete( $t_row['id'] );
		}
	
		# @todo should we check the return value of each \Flickerbox\Bug::delete() and
		#  return false if any of them return false? Presumable \Flickerbox\Bug::delete()
		#  will eventually trigger an error on failure so it won't matter...
	}
	
	/**
	 * Returns the extended record of the specified bug, this includes
	 * the bug text fields
	 * @todo include reporter name and handler name, the problem is that
	 *      handler can be 0, in this case no corresponding name will be
	 *      found.  Use equivalent of (+) in Oracle.
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return array
	 * @access public
	 */
	static function get_extended_row( $p_bug_id ) {
		$t_base = \Flickerbox\Bug::cache_row( $p_bug_id );
		$t_text = \Flickerbox\Bug::text_cache_row( $p_bug_id );
	
		# merge $t_text first so that the 'id' key has the bug id not the bug text id
		return array_merge( $t_text, $t_base );
	}
	
	/**
	 * Returns the record of the specified bug
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return array
	 * @access public
	 */
	static function get_row( $p_bug_id ) {
		return \Flickerbox\Bug::cache_row( $p_bug_id );
	}
	
	/**
	 * Returns an object representing the specified bug
	 * @param integer $p_bug_id       Integer representing bug identifier.
	 * @param boolean $p_get_extended Whether to include extended information (including bug_text).
	 * @return BugData BugData Object
	 * @access public
	 */
	static function get( $p_bug_id, $p_get_extended = false ) {
		if( $p_get_extended ) {
			$t_row = \Flickerbox\Bug::get_extended_row( $p_bug_id );
		} else {
			$t_row = \Flickerbox\Bug::get_row( $p_bug_id );
		}
	
		$t_bug_data = new \Flickerbox\BugData;
		$t_bug_data->loadrow( $t_row );
		return $t_bug_data;
	}
	
	/**
	 * Convert row [from database] to bug object
	 * @param array $p_row Bug database row.
	 * @return BugData
	 */
	static function row_to_object( array $p_row ) {
		$t_bug_data = new \Flickerbox\BugData;
		$t_bug_data->loadrow( $p_row );
		return $t_bug_data;
	}
	
	/**
	 * return the specified field of the given bug
	 *  if the field does not exist, display a warning and return ''
	 * @param integer $p_bug_id     Integer representing bug identifier.
	 * @param string  $p_field_name Field name to retrieve.
	 * @return string
	 * @access public
	 */
	static function get_field( $p_bug_id, $p_field_name ) {
		$t_row = \Flickerbox\Bug::get_row( $p_bug_id );
	
		if( isset( $t_row[$p_field_name] ) ) {
			return $t_row[$p_field_name];
		} else {
			\Flickerbox\Error::parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	
	/**
	 * return the specified text field of the given bug
	 *  if the field does not exist, display a warning and return ''
	 * @param integer $p_bug_id     Integer representing bug identifier.
	 * @param string  $p_field_name Field name to retrieve.
	 * @return string
	 * @access public
	 */
	static function get_text_field( $p_bug_id, $p_field_name ) {
		$t_row = \Flickerbox\Bug::text_cache_row( $p_bug_id );
	
		if( isset( $t_row[$p_field_name] ) ) {
			return $t_row[$p_field_name];
		} else {
			\Flickerbox\Error::parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	
	/**
	 * return the bug summary
	 *  this is a wrapper for the custom function
	 * @param integer $p_bug_id  Integer representing bug identifier.
	 * @param integer $p_context Representing SUMMARY_CAPTION, SUMMARY_FIELD.
	 * @return string
	 * @access public
	 * @uses helper_api.php
	 */
	static function format_summary( $p_bug_id, $p_context ) {
		return \Flickerbox\Helper::call_custom_function( 'format_issue_summary', array( $p_bug_id, $p_context ) );
	}
	
	/**
	 * return the timestamp for the most recent time at which a bugnote
	 *  associated with the bug was modified
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean|integer false or timestamp in integer format representing newest bugnote timestamp
	 * @access public
	 * @uses database_api.php
	 */
	static function get_newest_bugnote_timestamp( $p_bug_id ) {
		$c_bug_id = (int)$p_bug_id;
	
		$t_query = 'SELECT last_modified FROM {bugnote} WHERE bug_id=' . \Flickerbox\Database::param() . ' ORDER BY last_modified DESC';
		$t_result = \Flickerbox\Database::query( $t_query, array( $c_bug_id ), 1 );
		$t_row = \Flickerbox\Database::result( $t_result );
	
		if( false === $t_row ) {
			return false;
		} else {
			return $t_row;
		}
	}
	
	/**
	 * return the timestamp for the most recent time at which a bugnote
	 * associated with the bug was modified and the total bugnote
	 * count in one db query
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return object consisting of bugnote stats
	 * @access public
	 * @uses database_api.php
	 */
	static function get_bugnote_stats( $p_bug_id ) {
		global $g_cache_bug;
		$c_bug_id = (int)$p_bug_id;
	
		if( array_key_exists( '_stats', $g_cache_bug[$c_bug_id] ) ) {
			return $g_cache_bug[$c_bug_id]['_stats'];
		}
	
		# @todo - optimise - max(), count()
		$t_query = 'SELECT last_modified FROM {bugnote} WHERE bug_id=' . \Flickerbox\Database::param() . ' ORDER BY last_modified ASC';
		$t_result = \Flickerbox\Database::query( $t_query, array( $c_bug_id ) );
	
		$t_bugnote_count = 0;
		while( $t_row = \Flickerbox\Database::fetch_array( $t_result ) ) {
			$t_bugnote_count++;
		}
	
		if( $t_bugnote_count === 0 ) {
			return false;
		}
	
		$t_stats['last_modified'] = $t_row['last_modified'];
		$t_stats['count'] = $t_bugnote_count;
	
		return $t_stats;
	}
	
	/**
	 * Get array of attachments associated with the specified bug id.  The array will be
	 * sorted in terms of date added (ASC).  The array will include the following fields:
	 * id, title, diskfile, filename, filesize, file_type, date_added, user_id.
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return array array of results or empty array
	 * @access public
	 * @uses database_api.php
	 * @uses file_api.php
	 */
	static function get_attachments( $p_bug_id ) {
		$t_query = 'SELECT id, title, diskfile, filename, filesize, file_type, date_added, user_id
			                FROM {bug_file}
			                WHERE bug_id=' . \Flickerbox\Database::param() . '
			                ORDER BY date_added';
		$t_db_result = \Flickerbox\Database::query( $t_query, array( $p_bug_id ) );
	
		$t_result = array();
	
		while( $t_row = \Flickerbox\Database::fetch_array( $t_db_result ) ) {
			$t_result[] = $t_row;
		}
	
		return $t_result;
	}
	
	/**
	 * Set the value of a bug field
	 * @param integer                $p_bug_id     Integer representing bug identifier.
	 * @param string                 $p_field_name Pre-defined field name.
	 * @param boolean|integer|string $p_value      Value to set.
	 * @return boolean (always true)
	 * @access public
	 * @uses database_api.php
	 * @uses history_api.php
	 */
	static function set_field( $p_bug_id, $p_field_name, $p_value ) {
		$c_bug_id = (int)$p_bug_id;
		$c_value = null;
	
		switch( $p_field_name ) {
			# boolean
			case 'sticky':
				$c_value = $p_value;
				break;
	
			# integer
			case 'project_id':
			case 'reporter_id':
			case 'handler_id':
			case 'duplicate_id':
			case 'priority':
			case 'severity':
			case 'reproducibility':
			case 'status':
			case 'resolution':
			case 'projection':
			case 'category_id':
			case 'eta':
			case 'view_state':
			case 'profile_id':
			case 'sponsorship_total':
				$c_value = (int)$p_value;
				break;
	
			# string
			case 'os':
			case 'os_build':
			case 'platform':
			case 'version':
			case 'fixed_in_version':
			case 'target_version':
			case 'build':
			case 'summary':
				$c_value = $p_value;
				break;
	
			# dates
			case 'last_updated':
			case 'date_submitted':
			case 'due_date':
				if( !is_numeric( $p_value ) ) {
					trigger_error( ERROR_GENERIC, ERROR );
				}
				$c_value = $p_value;
				break;
	
			default:
				trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
				break;
		}
	
		$t_current_value = \Flickerbox\Bug::get_field( $p_bug_id, $p_field_name );
	
		# return if status is already set
		if( $c_value == $t_current_value ) {
			return true;
		}
	
		# Update fields
		$t_query = 'UPDATE {bug} SET ' . $p_field_name . '=' . \Flickerbox\Database::param() . ' WHERE id=' . \Flickerbox\Database::param();
		\Flickerbox\Database::query( $t_query, array( $c_value, $c_bug_id ) );
	
		# updated the last_updated date
		if( $p_field_name != 'last_updated' ) {
			\Flickerbox\Bug::update_date( $p_bug_id );
		}
	
		# log changes except for duplicate_id which is obsolete and should be removed in
		# MantisBT 1.3.
		switch( $p_field_name ) {
			case 'duplicate_id':
				break;
	
			case 'category_id':
				\Flickerbox\History::log_event_direct( $p_bug_id, 'category', \Flickerbox\Category::full_name( $t_current_value, false ), \Flickerbox\Category::full_name( $c_value, false ) );
				break;
	
			default:
				\Flickerbox\History::log_event_direct( $p_bug_id, $p_field_name, $t_current_value, $c_value );
		}
	
		\Flickerbox\Bug::clear_cache( $p_bug_id );
	
		return true;
	}
	
	/**
	 * assign the bug to the given user
	 * @param integer $p_bug_id          A bug identifier.
	 * @param integer $p_user_id         A user identifier.
	 * @param string  $p_bugnote_text    The bugnote text.
	 * @param boolean $p_bugnote_private Indicate whether bugnote is private.
	 * @return boolean
	 * @access public
	 * @uses database_api.php
	 */
	static function assign( $p_bug_id, $p_user_id, $p_bugnote_text = '', $p_bugnote_private = false ) {
		if( ( $p_user_id != NO_USER ) && !\Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'handle_bug_threshold' ), $p_bug_id, $p_user_id ) ) {
			trigger_error( ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS );
		}
	
		# extract current information into history variables
		$h_status = \Flickerbox\Bug::get_field( $p_bug_id, 'status' );
		$h_handler_id = \Flickerbox\Bug::get_field( $p_bug_id, 'handler_id' );
	
		if( ( ON == \Flickerbox\Config::mantis_get( 'auto_set_status_to_assigned' ) ) && ( NO_USER != $p_user_id ) ) {
			$t_ass_val = \Flickerbox\Config::mantis_get( 'bug_assigned_status' );
		} else {
			$t_ass_val = $h_status;
		}
	
		if( ( $t_ass_val != $h_status ) || ( $p_user_id != $h_handler_id ) ) {
	
			# get user id
			$t_query = 'UPDATE {bug}
						  SET handler_id=' . \Flickerbox\Database::param() . ', status=' . \Flickerbox\Database::param() . '
						  WHERE id=' . \Flickerbox\Database::param();
			\Flickerbox\Database::query( $t_query, array( $p_user_id, $t_ass_val, $p_bug_id ) );
	
			# log changes
			\Flickerbox\History::log_event_direct( $p_bug_id, 'status', $h_status, $t_ass_val );
			\Flickerbox\History::log_event_direct( $p_bug_id, 'handler_id', $h_handler_id, $p_user_id );
	
			# Add bugnote if supplied ignore false return
			\Flickerbox\Bug\Note::add( $p_bug_id, $p_bugnote_text, 0, $p_bugnote_private, 0, '', null, false );
	
			# updated the last_updated date
			\Flickerbox\Bug::update_date( $p_bug_id );
	
			\Flickerbox\Bug::clear_cache( $p_bug_id );
	
			# send assigned to email
			\Flickerbox\Email::generic( $p_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
		}
	
		return true;
	}
	
	/**
	 * close the given bug
	 * @param integer $p_bug_id          A bug identifier.
	 * @param string  $p_bugnote_text    The bugnote text.
	 * @param boolean $p_bugnote_private Whether the bugnote is private.
	 * @param string  $p_time_tracking   Time tracking value.
	 * @return boolean (always true)
	 * @access public
	 */
	static function close( $p_bug_id, $p_bugnote_text = '', $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
		$p_bugnote_text = trim( $p_bugnote_text );
	
		# Add bugnote if supplied ignore a false return
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		\Flickerbox\Bug\Note::add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', null, false );
	
		\Flickerbox\Bug::set_field( $p_bug_id, 'status', \Flickerbox\Config::mantis_get( 'bug_closed_status_threshold' ) );
	
		\Flickerbox\Email::generic( $p_bug_id, 'closed', 'The following issue has been CLOSED' );
		\Flickerbox\Email::relationship_child_closed( $p_bug_id );
	
		return true;
	}
	
	/**
	 * resolve the given bug
	 * @param integer $p_bug_id           A bug identifier.
	 * @param integer $p_resolution       Resolution status.
	 * @param string  $p_fixed_in_version Fixed in version.
	 * @param string  $p_bugnote_text     The bugnote text.
	 * @param integer $p_duplicate_id     A duplicate identifier.
	 * @param integer $p_handler_id       A handler identifier.
	 * @param boolean $p_bugnote_private  Whether this is a private bugnote.
	 * @param string  $p_time_tracking    Time tracking value.
	 * @access public
	 * @return boolean
	 */
	static function resolve( $p_bug_id, $p_resolution, $p_fixed_in_version = '', $p_bugnote_text = '', $p_duplicate_id = null, $p_handler_id = null, $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
		$c_resolution = (int)$p_resolution;
		$p_bugnote_text = trim( $p_bugnote_text );
	
		# Add bugnote if supplied
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		\Flickerbox\Bug\Note::add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', null, false );
	
		$t_duplicate = !\Flickerbox\Utility::is_blank( $p_duplicate_id ) && ( $p_duplicate_id != 0 );
		if( $t_duplicate ) {
			if( $p_bug_id == $p_duplicate_id ) {
				trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
	
				# never returns
			}
	
			# the related bug exists...
			\Flickerbox\Bug::ensure_exists( $p_duplicate_id );
	
			# check if there is other relationship between the bugs...
			$t_id_relationship = \Flickerbox\Relationship::same_type_exists( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );
	
			 if( $t_id_relationship > 0 ) {
				# Update the relationship
				\Flickerbox\Relationship::update( $t_id_relationship, $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );
	
				# Add log line to the history (both bugs)
				\Flickerbox\History::log_event_special( $p_bug_id, BUG_REPLACE_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id );
				\Flickerbox\History::log_event_special( $p_duplicate_id, BUG_REPLACE_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id );
			} else if( $t_id_relationship != -1 ) {
				# Add the new relationship
				\Flickerbox\Relationship::add( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );
	
				# Add log line to the history (both bugs)
				\Flickerbox\History::log_event_special( $p_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id );
				\Flickerbox\History::log_event_special( $p_duplicate_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id );
			} # else relationship is -1 - same type exists, do nothing
	
			# Copy list of users monitoring the duplicate bug to the original bug
			$t_old_reporter_id = \Flickerbox\Bug::get_field( $p_bug_id, 'reporter_id' );
			$t_old_handler_id = \Flickerbox\Bug::get_field( $p_bug_id, 'handler_id' );
			if( \Flickerbox\User::exists( $t_old_reporter_id ) ) {
				\Flickerbox\Bug::monitor( $p_duplicate_id, $t_old_reporter_id );
			}
			if( \Flickerbox\User::exists( $t_old_handler_id ) ) {
				\Flickerbox\Bug::monitor( $p_duplicate_id, $t_old_handler_id );
			}
			\Flickerbox\Bug::monitor_copy( $p_bug_id, $p_duplicate_id );
	
			\Flickerbox\Bug::set_field( $p_bug_id, 'duplicate_id', (int)$p_duplicate_id );
		}
	
		\Flickerbox\Bug::set_field( $p_bug_id, 'status', \Flickerbox\Config::mantis_get( 'bug_resolved_status_threshold' ) );
		\Flickerbox\Bug::set_field( $p_bug_id, 'fixed_in_version', $p_fixed_in_version );
		\Flickerbox\Bug::set_field( $p_bug_id, 'resolution', $c_resolution );
	
		# only set handler if specified explicitly or if bug was not assigned to a handler
		if( null == $p_handler_id ) {
			if( \Flickerbox\Bug::get_field( $p_bug_id, 'handler_id' ) == 0 ) {
				$p_handler_id = \Flickerbox\Auth::get_current_user_id();
				\Flickerbox\Bug::set_field( $p_bug_id, 'handler_id', $p_handler_id );
			}
		} else {
			\Flickerbox\Bug::set_field( $p_bug_id, 'handler_id', $p_handler_id );
		}
	
		\Flickerbox\Email::generic( $p_bug_id, 'resolved', 'The following issue has been RESOLVED.' );
		\Flickerbox\Email::relationship_child_resolved( $p_bug_id );
	
		return true;
	}
	
	/**
	 * reopen the given bug
	 * @param integer $p_bug_id          A bug identifier.
	 * @param string  $p_bugnote_text    The bugnote text.
	 * @param string  $p_time_tracking   Time tracking value.
	 * @param boolean $p_bugnote_private Whether this is a private bugnote.
	 * @return boolean (always true)
	 * @access public
	 * @uses database_api.php
	 * @uses email_api.php
	 * @uses bugnote_api.php
	 * @uses config_api.php
	 */
	static function reopen( $p_bug_id, $p_bugnote_text = '', $p_time_tracking = '0:00', $p_bugnote_private = false ) {
		$p_bugnote_text = trim( $p_bugnote_text );
	
		# Add bugnote if supplied
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		\Flickerbox\Bug\Note::add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', null, false );
	
		\Flickerbox\Bug::set_field( $p_bug_id, 'status', \Flickerbox\Config::mantis_get( 'bug_reopen_status' ) );
		\Flickerbox\Bug::set_field( $p_bug_id, 'resolution', \Flickerbox\Config::mantis_get( 'bug_reopen_resolution' ) );
	
		\Flickerbox\Email::generic( $p_bug_id, 'reopened', 'email_notification_title_for_action_bug_reopened' );
	
		return true;
	}
	
	/**
	 * updates the last_updated field
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return boolean (always true)
	 * @access public
	 * @uses database_api.php
	 */
	static function update_date( $p_bug_id ) {
		$t_query = 'UPDATE {bug} SET last_updated=' . \Flickerbox\Database::param() . ' WHERE id=' . \Flickerbox\Database::param();
		\Flickerbox\Database::query( $t_query, array( \Flickerbox\Database::now(), $p_bug_id ) );
	
		\Flickerbox\Bug::clear_cache( $p_bug_id );
	
		return true;
	}
	
	/**
	 * enable monitoring of this bug for the user
	 * @param integer $p_bug_id  Integer representing bug identifier.
	 * @param integer $p_user_id Integer representing user identifier.
	 * @return boolean true if successful, false if unsuccessful
	 * @access public
	 * @uses database_api.php
	 * @uses history_api.php
	 * @uses user_api.php
	 */
	static function monitor( $p_bug_id, $p_user_id ) {
		$c_bug_id = (int)$p_bug_id;
		$c_user_id = (int)$p_user_id;
	
		# Make sure we aren't already monitoring this bug
		if( \Flickerbox\User::is_monitoring_bug( $c_user_id, $c_bug_id ) ) {
			return true;
		}
	
		# Don't let the anonymous user monitor bugs
		if( \Flickerbox\User::is_anonymous( $c_user_id ) ) {
			return false;
		}
	
		# Insert monitoring record
		$t_query = 'INSERT INTO {bug_monitor} ( user_id, bug_id ) VALUES (' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ')';
		\Flickerbox\Database::query( $t_query, array( $c_user_id, $c_bug_id ) );
	
		# log new monitoring action
		\Flickerbox\History::log_event_special( $c_bug_id, BUG_MONITOR, $c_user_id );
	
		# updated the last_updated date
		\Flickerbox\Bug::update_date( $p_bug_id );
	
		\Flickerbox\Email::monitor_added( $p_bug_id, $p_user_id );
	
		return true;
	}
	
	/**
	 * Returns the list of users monitoring the specified bug
	 *
	 * @param integer $p_bug_id Integer representing bug identifier.
	 * @return array
	 */
	static function get_monitors( $p_bug_id ) {
		if( ! \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'show_monitor_list_threshold' ), $p_bug_id ) ) {
			return array();
		}
	
		# get the bugnote data
		$t_query = 'SELECT user_id, enabled
				FROM {bug_monitor} m, {user} u
				WHERE m.bug_id=' . \Flickerbox\Database::param() . ' AND m.user_id = u.id
				ORDER BY u.realname, u.username';
		$t_result = \Flickerbox\Database::query( $t_query, array( $p_bug_id ) );
	
		$t_users = array();
		while( $t_row = \Flickerbox\Database::fetch_array( $t_result ) ) {
			$t_users[] = $t_row['user_id'];
		}
	
		\Flickerbox\User::cache_array_rows( $t_users );
	
		return $t_users;
	}
	
	/**
	 * Copy list of users monitoring a bug to the monitor list of a second bug
	 * @param integer $p_source_bug_id Integer representing the bug identifier of the source bug.
	 * @param integer $p_dest_bug_id   Integer representing the bug identifier of the destination bug.
	 * @return void
	 * @access public
	 * @uses database_api.php
	 * @uses history_api.php
	 * @uses user_api.php
	 */
	static function monitor_copy( $p_source_bug_id, $p_dest_bug_id ) {
		$c_source_bug_id = (int)$p_source_bug_id;
		$c_dest_bug_id = (int)$p_dest_bug_id;
	
		$t_query = 'SELECT user_id FROM {bug_monitor} WHERE bug_id = ' . \Flickerbox\Database::param();
		$t_result = \Flickerbox\Database::query( $t_query, array( $c_source_bug_id ) );
	
		while( $t_bug_monitor = \Flickerbox\Database::fetch_array( $t_result ) ) {
			if( \Flickerbox\User::exists( $t_bug_monitor['user_id'] ) &&
				!\Flickerbox\User::is_monitoring_bug( $t_bug_monitor['user_id'], $c_dest_bug_id ) ) {
				$t_query = 'INSERT INTO {bug_monitor} ( user_id, bug_id )
					VALUES ( ' . \Flickerbox\Database::param() . ', ' . \Flickerbox\Database::param() . ' )';
				\Flickerbox\Database::query( $t_query, array( $t_bug_monitor['user_id'], $c_dest_bug_id ) );
				\Flickerbox\History::log_event_special( $c_dest_bug_id, BUG_MONITOR, $t_bug_monitor['user_id'] );
			}
		}
	}
	
	/**
	 * disable monitoring of this bug for the user
	 * if $p_user_id = null, then bug is unmonitored for all users.
	 * @param integer $p_bug_id  Integer representing bug identifier.
	 * @param integer $p_user_id Integer representing user identifier.
	 * @return boolean (always true)
	 * @access public
	 * @uses database_api.php
	 * @uses history_api.php
	 */
	static function unmonitor( $p_bug_id, $p_user_id ) {
		# Delete monitoring record
		$t_query = 'DELETE FROM {bug_monitor} WHERE bug_id = ' . \Flickerbox\Database::param();
		$t_db_query_params[] = $p_bug_id;
	
		if( $p_user_id !== null ) {
			$t_query .= ' AND user_id = ' . \Flickerbox\Database::param();
			$t_db_query_params[] = $p_user_id;
		}
	
		\Flickerbox\Database::query( $t_query, $t_db_query_params );
	
		# log new un-monitor action
		\Flickerbox\History::log_event_special( $p_bug_id, BUG_UNMONITOR, (int)$p_user_id );
	
		# updated the last_updated date
		\Flickerbox\Bug::update_date( $p_bug_id );
	
		return true;
	}
	
	/**
	 * Pads the bug id with the appropriate number of zeros.
	 * @param integer $p_bug_id A bug identifier.
	 * @return string
	 * @access public
	 * @uses config_api.php
	 */
	static function format_id( $p_bug_id ) {
		$t_padding = \Flickerbox\Config::mantis_get( 'display_bug_padding' );
		$t_string = sprintf( '%0' . (int)$t_padding . 'd', $p_bug_id );
	
		return \Flickerbox\Event::signal( 'EVENT_DISPLAY_BUG_ID', $t_string, array( $p_bug_id ) );
	}


}