<?php
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
 * This page allows actions to be performed an an array of bugs
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'custom_field_api.php' );

\Flickerbox\Auth::ensure_user_authenticated();
\Flickerbox\Helper::begin_long_process();

$f_action	= \Flickerbox\GPC::get_string( 'action' );
$f_custom_field_id = \Flickerbox\GPC::get_int( 'custom_field_id', 0 );
$f_bug_arr	= \Flickerbox\GPC::get_int_array( 'bug_arr', array() );
$f_bug_notetext = \Flickerbox\GPC::get_string( 'bugnote_text', '' );
$f_bug_noteprivate = \Flickerbox\GPC::get_bool( 'private' );
$t_form_name = 'bug_actiongroup_' . $f_action;
\Flickerbox\Form::security_validate( $t_form_name );

$t_custom_group_actions = \Flickerbox\Config::mantis_get( 'custom_group_actions' );

foreach( $t_custom_group_actions as $t_custom_group_action ) {
	if( $f_action == $t_custom_group_action['action'] ) {
		require_once( $t_custom_group_action['action_page'] );
		exit;
	}
}

$t_failed_ids = array();

if( 0 != $f_custom_field_id ) {
	$t_custom_field_def = custom_field_get_definition( $f_custom_field_id );
}

foreach( $f_bug_arr as $t_bug_id ) {
	\Flickerbox\Bug::ensure_exists( $t_bug_id );
	$t_bug = \Flickerbox\Bug::get( $t_bug_id, true );

	if( $t_bug->project_id != \Flickerbox\Helper::get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
		# @todo (thraxisp) the next line goes away if the cache was smarter and used project
		\Flickerbox\Config::flush_cache(); # flush the config cache so that configs are refetched
	}

	$t_status = $t_bug->status;

	switch( $f_action ) {
		case 'CLOSE':
			$t_closed = \Flickerbox\Config::mantis_get( 'bug_closed_status_threshold' );
			if( \Flickerbox\Access::can_close_bug( $t_bug ) ) {
				if( ( $t_status < $t_closed ) &&
					\Flickerbox\Bug::check_workflow( $t_status, $t_closed ) ) {

				# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $f_bug_id, $t_bug_data, $f_bugnote_text ) );
				\Flickerbox\Bug::close( $t_bug_id, $f_bug_notetext, $f_bug_noteprivate );
				\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_status' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'DELETE':
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'delete_bug_threshold' ), $t_bug_id ) ) {
				\Flickerbox\Event::signal( 'EVENT_BUG_DELETED', array( $t_bug_id ) );
				\Flickerbox\Bug::delete( $t_bug_id );
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'MOVE':
			$f_project_id = \Flickerbox\GPC::get_int( 'project_id' );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'move_bug_threshold' ), $t_bug_id ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'report_bug_threshold', null, null, $f_project_id ), $f_project_id ) ) {
				# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
				\Flickerbox\Bug::move_bug( $t_bug_id, $f_project_id );
				\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'COPY':
			$f_project_id = \Flickerbox\GPC::get_int( 'project_id' );

			if( \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'report_bug_threshold' ), $f_project_id ) ) {
				# Copy everything except history
				\Flickerbox\Bug::copy_bug( $t_bug_id, $f_project_id, true, true, false, true, true, true );
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'ASSIGN':
			$f_assign = \Flickerbox\GPC::get_int( 'assign' );
			if( ON == \Flickerbox\Config::mantis_get( 'auto_set_status_to_assigned' ) ) {
				$t_assign_status = \Flickerbox\Config::mantis_get( 'bug_assigned_status' );
			} else {
				$t_assign_status = $t_status;
			}
			# check that new handler has rights to handle the issue, and
			#  that current user has rights to assign the issue
			$t_threshold = \Flickerbox\Access::get_status_threshold( $t_assign_status, $t_bug->project_id );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_assign_threshold', \Flickerbox\Config::mantis_get( 'update_bug_threshold' ) ), $t_bug_id ) ) {
				if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'handle_bug_threshold' ), $t_bug_id, $f_assign ) ) {
					if( \Flickerbox\Bug::check_workflow( $t_status, $t_assign_status ) ) {
						# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
						\Flickerbox\Bug::assign( $t_bug_id, $f_assign, $f_bug_notetext, $f_bug_noteprivate );
						\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
					} else {
						$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_status' );
					}
				} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_handler' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'RESOLVE':
			$t_resolved_status = \Flickerbox\Config::mantis_get( 'bug_resolved_status_threshold' );
				if( \Flickerbox\Access::has_bug_level( \Flickerbox\Access::get_status_threshold( $t_resolved_status, $t_bug->project_id ), $t_bug_id ) ) {
					if( ( $t_status < $t_resolved_status ) &&
						\Flickerbox\Bug::check_workflow( $t_status, $t_resolved_status ) ) {
				$f_resolution = \Flickerbox\GPC::get_int( 'resolution' );
				$f_fixed_in_version = \Flickerbox\GPC::get_string( 'fixed_in_version', '' );
				# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
				\Flickerbox\Bug::resolve( $t_bug_id, $f_resolution, $f_fixed_in_version, $f_bug_notetext, null, null, $f_bug_noteprivate );
				\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_status' );
				}
				} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'UP_PRIOR':
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				$f_priority = \Flickerbox\GPC::get_int( 'priority' );
				# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
				\Flickerbox\Bug::set_field( $t_bug_id, 'priority', $f_priority );
				\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
				\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'UP_STATUS':
			$f_status = \Flickerbox\GPC::get_int( 'status' );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Access::get_status_threshold( $f_status, $t_bug->project_id ), $t_bug_id ) ) {
				if( true == \Flickerbox\Bug::check_workflow( $t_status, $f_status ) ) {
					# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
					\Flickerbox\Bug::set_field( $t_bug_id, 'status', $f_status );

					# Add bugnote if supplied
					if( !\Flickerbox\Utility::is_blank( $f_bug_notetext ) ) {
						\Flickerbox\Bug\Note::add( $t_bug_id, $f_bug_notetext, null, $f_bug_noteprivate );
						# No need to call \Flickerbox\Email::generic(), \Flickerbox\Bug\Note::add() does it
					} else {
						\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
					}

					\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_status' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'UP_CATEGORY':
			$f_category_id = \Flickerbox\GPC::get_int( 'category' );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				if( \Flickerbox\Category::exists( $f_category_id ) ) {
					# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
					\Flickerbox\Bug::set_field( $t_bug_id, 'category_id', $f_category_id );
					\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
					\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_category' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'UP_PRODUCT_VERSION':
			$f_product_version = \Flickerbox\GPC::get_string( 'product_version' );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				if( $f_product_version === '' || \Flickerbox\Version::get_id( $f_product_version, $t_bug->project_id ) !== false ) {
					/** @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) ); */
					\Flickerbox\Bug::set_field( $t_bug_id, 'version', $f_product_version );
					\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
					\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_version' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'UP_FIXED_IN_VERSION':
			$f_fixed_in_version = \Flickerbox\GPC::get_string( 'fixed_in_version' );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold' ), $t_bug_id ) ) {
				if( $f_fixed_in_version === '' || \Flickerbox\Version::get_id( $f_fixed_in_version, $t_bug->project_id ) !== false ) {
					# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
					\Flickerbox\Bug::set_field( $t_bug_id, 'fixed_in_version', $f_fixed_in_version );
					\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
					\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
					} else {
						$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_version' );
				}
				} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'UP_TARGET_VERSION':
			$f_target_version = \Flickerbox\GPC::get_string( 'target_version' );
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'roadmap_update_threshold' ), $t_bug_id ) ) {
				if( $f_target_version === '' || \Flickerbox\Version::get_id( $f_target_version, $t_bug->project_id ) !== false ) {
					# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
					\Flickerbox\Bug::set_field( $t_bug_id, 'target_version', $f_target_version );
					\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
					\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				} else {
					$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_version' );
				}
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'VIEW_STATUS':
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'change_view_status_threshold' ), $t_bug_id ) ) {
				$f_view_status = \Flickerbox\GPC::get_int( 'view_status' );
				# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
				\Flickerbox\Bug::set_field( $t_bug_id, 'view_state', $f_view_status );
				\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
				\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'SET_STICKY':
			if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'set_bug_sticky_threshold' ), $t_bug_id ) ) {
				$f_sticky = \Flickerbox\Bug::get_field( $t_bug_id, 'sticky' );
				# The new value is the inverted old value
				# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
				\Flickerbox\Bug::set_field( $t_bug_id, 'sticky', intval( !$f_sticky ) );
				\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			} else {
				$t_failed_ids[$t_bug_id] = \Flickerbox\Lang::get( 'bug_actiongroup_access' );
			}
			break;
		case 'CUSTOM':
			if( 0 === $f_custom_field_id ) {
				trigger_error( ERROR_GENERIC, ERROR );
			}

			# @todo we need to issue a \Flickerbox\Helper::call_custom_function( 'issue_update_validate', array( $t_bug_id, $t_bug_data, $f_bugnote_text ) );
			$t_form_var = 'custom_field_' . $f_custom_field_id;
			$t_custom_field_value = \Flickerbox\GPC::get_custom_field( $t_form_var, $t_custom_field_def['type'], null );
			custom_field_set_value( $f_custom_field_id, $t_bug_id, $t_custom_field_value );
			\Flickerbox\Bug::update_date( $t_bug_id );
			\Flickerbox\Email::generic( $t_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
			\Flickerbox\Helper::call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
			break;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}

	# Bug Action Event
	\Flickerbox\Event::signal( 'EVENT_BUG_ACTION', array( $f_action, $t_bug_id ) );
}

\Flickerbox\Form::security_purge( $t_form_name );

$t_redirect_url = 'view_all_bug_page.php';

if( count( $t_failed_ids ) > 0 ) {
	\Flickerbox\HTML::page_top();

	echo '<div><br />';
	echo '<table class="width75">';
	$t_separator = \Flickerbox\Lang::get( 'word_separator' );
	foreach( $t_failed_ids as $t_id => $t_reason ) {
		$t_label = sprintf( \Flickerbox\Lang::get( 'label' ), \Flickerbox\String::get_bug_view_link( $t_id ) ) . $t_separator;
		printf( "<tr><td width=\"50%%\">%s%s</td><td>%s</td></tr>\n", $t_label, \Flickerbox\Bug::get_field( $t_id, 'summary' ), $t_reason );
	}
	echo '</table><br />';
	\Flickerbox\Print_Util::bracket_link( $t_redirect_url, \Flickerbox\Lang::get( 'proceed' ) );
	echo '</div>';

	\Flickerbox\HTML::page_bottom();
} else {
	\Flickerbox\Print_Util::header_redirect( $t_redirect_url );
}
