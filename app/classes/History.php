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
 * History API
 *
 * @package CoreAPI
 * @subpackage HistoryAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bug_revision_api.php
 * @uses bugnote_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'custom_field_api.php' );


class History
{
	
	/**
	 * log the changes (old / new value are supplied to reduce db access)
	 * events should be logged *after* the modification
	 * @param integer $p_bug_id     The bug identifier of the bug being modified.
	 * @param string  $p_field_name The field name of the field being modified.
	 * @param string  $p_old_value  The old value of the field.
	 * @param string  $p_new_value  The new value of the field.
	 * @param integer $p_user_id    The user identifier of the user modifying the bug.
	 * @param integer $p_type       The type of the modification.
	 * @return void
	 */
	static function log_event_direct( $p_bug_id, $p_field_name, $p_old_value, $p_new_value, $p_user_id = null, $p_type = 0 ) {
		# Only log events that change the value
		if( $p_new_value != $p_old_value ) {
			if( null === $p_user_id ) {
				$p_user_id = \Core\Auth::get_current_user_id();
			}
	
			$c_field_name = $p_field_name;
			$c_old_value = ( is_null( $p_old_value ) ? '' : (string)$p_old_value );
			$c_new_value = ( is_null( $p_new_value ) ? '' : (string)$p_new_value );
	
			$t_query = 'INSERT INTO {bug_history}
							( user_id, bug_id, date_modified, field_name, old_value, new_value, type )
						VALUES
							( ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ' )';
			\Core\Database::query( $t_query, array( $p_user_id, $p_bug_id, \Core\Database::now(), $c_field_name, $c_old_value, $c_new_value, $p_type ) );
		}
	}
	
	/**
	 * log the changes
	 * events should be logged *after* the modification
	 * @param integer $p_bug_id     The bug identifier of the bug being modified.
	 * @param string  $p_field_name The field name of the field being modified.
	 * @param string  $p_old_value  The old value of the field.
	 * @return void
	 */
	static function log_event( $p_bug_id, $p_field_name, $p_old_value ) {
		\Core\History::log_event_direct( $p_bug_id, $p_field_name, $p_old_value, \Core\Bug::get_field( $p_bug_id, $p_field_name ) );
	}
	
	/**
	 * log the changes
	 * events should be logged *after* the modification
	 * These are special case logs (new bug, deleted bugnote, etc.)
	 * @param integer $p_bug_id    The bug identifier of the bug being modified.
	 * @param integer $p_type      The type of the modification.
	 * @param string  $p_old_value The optional value to store in the old_value field.
	 * @param string  $p_new_value The optional value to store in the new_value field.
	 * @return void
	 */
	static function log_event_special( $p_bug_id, $p_type, $p_old_value = '', $p_new_value = '' ) {
		$t_user_id = \Core\Auth::get_current_user_id();
	
		if( is_null( $p_old_value ) ) {
			$p_old_value = '';
		}
		if( is_null( $p_new_value ) ) {
			$p_new_value = '';
		}
	
		$t_query = 'INSERT INTO {bug_history}
						( user_id, bug_id, date_modified, type, old_value, new_value, field_name )
					VALUES
						( ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ',' . \Core\Database::param() . ', ' . \Core\Database::param() . ')';
		\Core\Database::query( $t_query, array( $t_user_id, $p_bug_id, \Core\Database::now(), $p_type, $p_old_value, $p_new_value, '' ) );
	}
	
	/**
	 * Retrieves the history events for the specified bug id and returns it in an array
	 * The array is indexed from 0 to N-1.  The second dimension is: 'date', 'username',
	 * 'note', 'change'.
	 * @param integer $p_bug_id  A valid bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @return array
	 */
	static function get_events_array( $p_bug_id, $p_user_id = null ) {
		$t_normal_date_format = \Core\Config::mantis_get( 'normal_date_format' );
	
		$t_raw_history = \Core\History::get_raw_events_array( $p_bug_id, $p_user_id );
		$t_history = array();
	
		foreach( $t_raw_history as $k => $t_item ) {
			extract( $t_item, EXTR_PREFIX_ALL, 'v' );
			$t_history[$k] = \Core\History::localize_item( $v_field, $v_type, $v_old_value, $v_new_value );
			$t_history[$k]['date'] = date( $t_normal_date_format, $v_date );
			$t_history[$k]['userid'] = $v_userid;
			$t_history[$k]['username'] = $v_username;
		}
	
		return( $t_history );
	}
	
	/**
	 * Retrieves the raw history events for the specified bug id and returns it in an array
	 * The array is indexed from 0 to N-1.  The second dimension is: 'date', 'userid', 'username',
	 * 'field','type','old_value','new_value'
	 * @param integer $p_bug_id  A valid bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @param integer $p_start_time The start time to filter by, or null for all.
	 * @param integer $p_end_time   The end time to filter by, or null for all.
	 * @return array
	 */
	static function get_raw_events_array( $p_bug_id, $p_user_id = null, $p_start_time = null, $p_end_time = null ) {
		$t_history_order = \Core\Config::mantis_get( 'history_order' );
	
		$t_user_id = (( null === $p_user_id ) ? \Core\Auth::get_current_user_id() : $p_user_id );
	
		$t_roadmap_view_access_level = \Core\Config::mantis_get( 'roadmap_view_threshold' );
		$t_due_date_view_threshold = \Core\Config::mantis_get( 'due_date_view_threshold' );
	
		# grab history and display by date_modified then field_name
		# @@@ by MASC I guess it's better by id then by field_name. When we have more history lines with the same
		# date, it's better to respect the storing order otherwise we should risk to mix different information
		# I give you an example. We create a child of a bug with different custom fields. In the history of the child
		# bug we will find the line related to the relationship mixed with the custom fields (the history is creted
		# for the new bug with the same timestamp...)
	
		$t_params = array( $p_bug_id );
	
		$t_query = 'SELECT * FROM {bug_history} WHERE bug_id=' . \Core\Database::param();
	
		$t_where = array();
		if ( $p_start_time !== null ) {
			$t_where[] = 'date_modified >= ' . \Core\Database::param();
			$t_params[] = $p_start_time;
		}
	
		if ( $p_end_time !== null ) {
			$t_where[] = 'date_modified < ' . \Core\Database::param();
			$t_params[] = $p_end_time;
		}
	
		if ( count( $t_where ) > 0 ) {
			$t_query .= ' AND ' . implode( ' AND ', $t_where );
		}
	
		$t_query .= ' ORDER BY date_modified ' . $t_history_order . ',id';
	
		$t_result = \Core\Database::query( $t_query, $t_params );
		$t_raw_history = array();
	
		$t_private_bugnote_visible = \Core\Access::has_bug_level( \Core\Config::mantis_get( 'private_bugnote_threshold' ), $p_bug_id, $t_user_id );
		$t_tag_view_threshold = \Core\Config::mantis_get( 'tag_view_threshold' );
		$t_view_attachments_threshold = \Core\Config::mantis_get( 'view_attachments_threshold' );
		$t_show_monitor_list_threshold = \Core\Config::mantis_get( 'show_monitor_list_threshold' );
		$t_show_handler_threshold = \Core\Config::mantis_get( 'view_handler_threshold' );
	
		$t_standard_fields = \Core\Columns::get_standard();
		$j = 0;
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			extract( $t_row, EXTR_PREFIX_ALL, 'v' );
	
			if( $v_type == NORMAL_TYPE ) {
				if( !in_array( $v_field_name, $t_standard_fields ) ) {
					# check that the item should be visible to the user
					$t_field_id = custom_field_get_id_from_name( $v_field_name );
					if( false !== $t_field_id && !custom_field_has_read_access( $t_field_id, $p_bug_id, $t_user_id ) ) {
						continue;
					}
				}
	
				if( ( $v_field_name == 'target_version' ) && !\Core\Access::has_bug_level( $t_roadmap_view_access_level, $p_bug_id, $t_user_id ) ) {
					continue;
				}
	
				if( ( $v_field_name == 'due_date' ) && !\Core\Access::has_bug_level( $t_due_date_view_threshold, $p_bug_id, $t_user_id ) ) {
					continue;
				}
	
				if( ( $v_field_name == 'handler_id' ) && !\Core\Access::has_bug_level( $t_show_handler_threshold, $p_bug_id, $t_user_id ) ) {
					continue;
				}
			}
	
			# bugnotes
			if( $t_user_id != $v_user_id ) {
				# bypass if user originated note
				if( ( $v_type == BUGNOTE_ADDED ) || ( $v_type == BUGNOTE_UPDATED ) || ( $v_type == BUGNOTE_DELETED ) ) {
					if( !$t_private_bugnote_visible && ( \Core\Bug\Note::get_field( $v_old_value, 'view_state' ) == VS_PRIVATE ) ) {
						continue;
					}
				}
	
				if( $v_type == BUGNOTE_STATE_CHANGED ) {
					if( !$t_private_bugnote_visible && ( \Core\Bug\Note::get_field( $v_new_value, 'view_state' ) == VS_PRIVATE ) ) {
						continue;
					}
				}
			}
	
			# tags
			if( $v_type == TAG_ATTACHED || $v_type == TAG_DETACHED || $v_type == TAG_RENAMED ) {
				if( !\Core\Access::has_bug_level( $t_tag_view_threshold, $p_bug_id, $t_user_id ) ) {
					continue;
				}
			}
	
			# attachments
			if( $v_type == FILE_ADDED || $v_type == FILE_DELETED ) {
				if( !\Core\Access::has_bug_level( $t_view_attachments_threshold, $p_bug_id, $t_user_id ) ) {
					continue;
				}
			}
	
			# monitoring
			if( $v_type == BUG_MONITOR || $v_type == BUG_UNMONITOR ) {
				if( !\Core\Access::has_bug_level( $t_show_monitor_list_threshold, $p_bug_id, $t_user_id ) ) {
					continue;
				}
			}
	
			# relationships
			if( $v_type == BUG_ADD_RELATIONSHIP || $v_type == BUG_DEL_RELATIONSHIP || $v_type == BUG_REPLACE_RELATIONSHIP ) {
				$t_related_bug_id = $v_new_value;
	
				# If bug doesn't exist, then we don't know whether to expose it or not based on the fact whether it was
				# accessible to user or not.  This also simplifies client code that is accessing the history log.
				if( !\Core\Bug::exists( $t_related_bug_id ) || !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'view_bug_threshold' ), $t_related_bug_id, $t_user_id ) ) {
					continue;
				}
			}
	
			$t_raw_history[$j]['date'] = $v_date_modified;
			$t_raw_history[$j]['userid'] = $v_user_id;
	
			# user_get_name handles deleted users, and username vs realname
			$t_raw_history[$j]['username'] = \Core\User::get_name( $v_user_id );
	
			$t_raw_history[$j]['field'] = $v_field_name;
			$t_raw_history[$j]['type'] = $v_type;
			$t_raw_history[$j]['old_value'] = $v_old_value;
			$t_raw_history[$j]['new_value'] = $v_new_value;
	
			$j++;
		}
	
		# end for loop
	
		return $t_raw_history;
	}
	
	/**
	 * Localizes one raw history item specified by set the next parameters: $p_field_name, $p_type, $p_old_value, $p_new_value
	 * Returns array with two elements indexed as 'note' and 'change'
	 * @param string  $p_field_name The field name of the field being localized.
	 * @param integer $p_type       The type of the history entry.
	 * @param string  $p_old_value  The old value of the field.
	 * @param string  $p_new_value  The new value of the field.
	 * @param boolean $p_linkify    Whether to return a string containing hyperlinks.
	 * @return array
	 */
	static function localize_item( $p_field_name, $p_type, $p_old_value, $p_new_value, $p_linkify = true ) {
		$t_note = '';
		$t_change = '';
		$t_field_localized = $p_field_name;
		$t_raw = true;
	
		if( PLUGIN_HISTORY == $p_type ) {
			$t_note = \Core\Lang::get_defaulted( 'plugin_' . $p_field_name, $p_field_name );
			$t_change = ( isset( $p_new_value ) ? $p_old_value . ' => ' . $p_new_value : $p_old_value );
	
			return array( 'note' => $t_note, 'change' => $t_change, 'raw' => true );
		}
	
		switch( $p_field_name ) {
			case 'category':
				$t_field_localized = \Core\Lang::get( 'category' );
				break;
			case 'status':
				$p_old_value = \Core\Helper::get_enum_element( 'status', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'status', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'status' );
				break;
			case 'severity':
				$p_old_value = \Core\Helper::get_enum_element( 'severity', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'severity', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'severity' );
				break;
			case 'reproducibility':
				$p_old_value = \Core\Helper::get_enum_element( 'reproducibility', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'reproducibility', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'reproducibility' );
				break;
			case 'resolution':
				$p_old_value = \Core\Helper::get_enum_element( 'resolution', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'resolution', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'resolution' );
				break;
			case 'priority':
				$p_old_value = \Core\Helper::get_enum_element( 'priority', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'priority', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'priority' );
				break;
			case 'eta':
				$p_old_value = \Core\Helper::get_enum_element( 'eta', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'eta', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'eta' );
				break;
			case 'view_state':
				$p_old_value = \Core\Helper::get_enum_element( 'view_state', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'view_state', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'view_status' );
				break;
			case 'projection':
				$p_old_value = \Core\Helper::get_enum_element( 'projection', $p_old_value );
				$p_new_value = \Core\Helper::get_enum_element( 'projection', $p_new_value );
				$t_field_localized = \Core\Lang::get( 'projection' );
				break;
			case 'sticky':
				$p_old_value = \Core\GPC::string_to_bool( $p_old_value ) ? \Core\Lang::get( 'yes' ) : \Core\Lang::get( 'no' );
				$p_new_value = \Core\GPC::string_to_bool( $p_new_value ) ? \Core\Lang::get( 'yes' ) : \Core\Lang::get( 'no' );
				$t_field_localized = \Core\Lang::get( 'sticky_issue' );
				break;
			case 'project_id':
				if( \Core\Project::exists( $p_old_value ) ) {
					$p_old_value = \Core\Project::get_field( $p_old_value, 'name' );
				} else {
					$p_old_value = '@' . $p_old_value . '@';
				}
	
				# Note that the new value maybe an intermediately project and not the
				# current one.
				if( \Core\Project::exists( $p_new_value ) ) {
					$p_new_value = \Core\Project::get_field( $p_new_value, 'name' );
				} else {
					$p_new_value = '@' . $p_new_value . '@';
				}
				$t_field_localized = \Core\Lang::get( 'email_project' );
				break;
			case 'handler_id':
				$t_field_localized = \Core\Lang::get( 'assigned_to' );
			case 'reporter_id':
				if( 'reporter_id' == $p_field_name ) {
					$t_field_localized = \Core\Lang::get( 'reporter' );
				}
				if( 0 == $p_old_value ) {
					$p_old_value = '';
				} else {
					$p_old_value = \Core\User::get_name( $p_old_value );
				}
	
				if( 0 == $p_new_value ) {
					$p_new_value = '';
				} else {
					$p_new_value = \Core\User::get_name( $p_new_value );
				}
				break;
			case 'version':
				$t_field_localized = \Core\Lang::get( 'product_version' );
				break;
			case 'fixed_in_version':
				$t_field_localized = \Core\Lang::get( 'fixed_in_version' );
				break;
			case 'target_version':
				$t_field_localized = \Core\Lang::get( 'target_version' );
				break;
			case 'date_submitted':
				$p_old_value = date( \Core\Config::mantis_get( 'normal_date_format' ), $p_old_value );
				$p_new_value = date( \Core\Config::mantis_get( 'normal_date_format' ), $p_new_value );
				$t_field_localized = \Core\Lang::get( 'date_submitted' );
				break;
			case 'last_updated':
				$p_old_value = date( \Core\Config::mantis_get( 'normal_date_format' ), $p_old_value );
				$p_new_value = date( \Core\Config::mantis_get( 'normal_date_format' ), $p_new_value );
				$t_field_localized = \Core\Lang::get( 'last_update' );
				break;
			case 'os':
				$t_field_localized = \Core\Lang::get( 'os' );
				break;
			case 'os_build':
				$t_field_localized = \Core\Lang::get( 'os_version' );
				break;
			case 'build':
				$t_field_localized = \Core\Lang::get( 'build' );
				break;
			case 'platform':
				$t_field_localized = \Core\Lang::get( 'platform' );
				break;
			case 'summary':
				$t_field_localized = \Core\Lang::get( 'summary' );
				break;
			case 'duplicate_id':
				$t_field_localized = \Core\Lang::get( 'duplicate_id' );
				break;
			case 'sponsorship_total':
				$t_field_localized = \Core\Lang::get( 'sponsorship_total' );
				break;
			case 'due_date':
				if( $p_old_value !== '' ) {
					$p_old_value = date( \Core\Config::mantis_get( 'normal_date_format' ), (int)$p_old_value );
				}
				if( $p_new_value !== '' ) {
					$p_new_value = date( \Core\Config::mantis_get( 'normal_date_format' ), (int)$p_new_value );
				}
				$t_field_localized = \Core\Lang::get( 'due_date' );
				break;
			default:
	
				# assume it's a custom field name
				$t_field_id = custom_field_get_id_from_name( $p_field_name );
				if( false !== $t_field_id ) {
					$t_cf_type = custom_field_type( $t_field_id );
					if( '' != $p_old_value ) {
						$p_old_value = string_custom_field_value_for_email( $p_old_value, $t_cf_type );
					}
					$p_new_value = string_custom_field_value_for_email( $p_new_value, $t_cf_type );
					$t_field_localized = \Core\Lang::get_defaulted( $p_field_name );
				}
			}
	
			if( NORMAL_TYPE != $p_type ) {
				switch( $p_type ) {
					case NEW_BUG:
						$t_note = \Core\Lang::get( 'new_bug' );
						break;
					case BUGNOTE_ADDED:
						$t_note = \Core\Lang::get( 'bugnote_added' ) . ': ' . $p_old_value;
						break;
					case BUGNOTE_UPDATED:
						$t_note = \Core\Lang::get( 'bugnote_edited' ) . ': ' . $p_old_value;
						$t_old_value = (int)$p_old_value;
						$t_new_value = (int)$p_new_value;
						if( $p_linkify && \Core\Bug\Revision::exists( $t_new_value ) ) {
							if( \Core\Bug\Note::exists( $t_old_value ) ) {
								$t_bug_revision_view_page_argument = 'bugnote_id=' . $t_old_value . '#r' . $t_new_value;
							} else {
								$t_bug_revision_view_page_argument = 'rev_id=' . $t_new_value;
							}
							$t_change = '<a href="bug_revision_view_page.php?' . $t_bug_revision_view_page_argument . '">' .
								\Core\Lang::get( 'view_revisions' ) . '</a>';
							$t_raw = false;
						}
						break;
					case BUGNOTE_DELETED:
						$t_note = \Core\Lang::get( 'bugnote_deleted' ) . ': ' . $p_old_value;
						break;
					case DESCRIPTION_UPDATED:
						$t_note = \Core\Lang::get( 'description_updated' );
						$t_old_value = (int)$p_old_value;
						if( $p_linkify && \Core\Bug\Revision::exists( $t_old_value ) ) {
							$t_change = '<a href="bug_revision_view_page.php?rev_id=' . $t_old_value . '#r' . $t_old_value . '">' .
								\Core\Lang::get( 'view_revisions' ) . '</a>';
							$t_raw = false;
						}
						break;
					case ADDITIONAL_INFO_UPDATED:
						$t_note = \Core\Lang::get( 'additional_information_updated' );
						$t_old_value = (int)$p_old_value;
						if( $p_linkify && \Core\Bug\Revision::exists( $t_old_value ) ) {
							$t_change = '<a href="bug_revision_view_page.php?rev_id=' . $t_old_value . '#r' . $t_old_value . '">' .
								\Core\Lang::get( 'view_revisions' ) . '</a>';
							$t_raw = false;
						}
						break;
					case STEP_TO_REPRODUCE_UPDATED:
						$t_note = \Core\Lang::get( 'steps_to_reproduce_updated' );
						$t_old_value = (int)$p_old_value;
						if( $p_linkify && \Core\Bug\Revision::exists( $t_old_value ) ) {
							$t_change = '<a href="bug_revision_view_page.php?rev_id=' . $t_old_value . '#r' . $t_old_value . '">' .
								\Core\Lang::get( 'view_revisions' ) . '</a>';
							$t_raw = false;
						}
						break;
					case FILE_ADDED:
						$t_note = \Core\Lang::get( '\\Core\\File::add(ed' ) . ': ' . $p_old_value;
						break;
					case FILE_DELETED:
						$t_note = \Core\Lang::get( 'file_deleted' ) . ': ' . $p_old_value;
						break;
					case BUGNOTE_STATE_CHANGED:
						$p_old_value = \Core\Helper::get_enum_element( 'view_state', $p_old_value );
						$t_note = \Core\Lang::get( 'bugnote_view_state' ) . ': ' . $p_new_value . ': ' . $p_old_value;
						break;
					case BUG_MONITOR:
						$p_old_value = \Core\User::get_name( $p_old_value );
						$t_note = \Core\Lang::get( 'bug_monitor' ) . ': ' . $p_old_value;
						break;
					case BUG_UNMONITOR:
						if( $p_old_value !== '' ) {
							$p_old_value = \Core\User::get_name( $p_old_value );
						}
						$t_note = \Core\Lang::get( 'bug_end_monitor' ) . ': ' . $p_old_value;
						break;
					case BUG_DELETED:
						$t_note = \Core\Lang::get( 'bug_deleted' ) . ': ' . $p_old_value;
						break;
					case BUG_ADD_SPONSORSHIP:
						$t_note = \Core\Lang::get( 'sponsorship_added' );
						$t_change = \Core\User::get_name( $p_old_value ) . ': ' . \Core\Sponsorship::format_amount( $p_new_value );
						break;
					case BUG_UPDATE_SPONSORSHIP:
						$t_note = \Core\Lang::get( 'sponsorship_updated' );
						$t_change = \Core\User::get_name( $p_old_value ) . ': ' . \Core\Sponsorship::format_amount( $p_new_value );
						break;
					case BUG_DELETE_SPONSORSHIP:
						$t_note = \Core\Lang::get( 'sponsorship_deleted' );
						$t_change = \Core\User::get_name( $p_old_value ) . ': ' . \Core\Sponsorship::format_amount( $p_new_value );
						break;
					case BUG_PAID_SPONSORSHIP:
						$t_note = \Core\Lang::get( 'sponsorship_paid' );
						$t_change = \Core\User::get_name( $p_old_value ) . ': ' . \Core\Helper::get_enum_element( 'sponsorship', $p_new_value );
						break;
					case BUG_ADD_RELATIONSHIP:
						$t_note = \Core\Lang::get( 'relationship_added' );
						$t_change = \Core\Relationship::get_description_for_history( $p_old_value ) . ' ' . \Core\Bug::format_id( $p_new_value );
						break;
					case BUG_REPLACE_RELATIONSHIP:
						$t_note = \Core\Lang::get( 'relationship_replaced' );
						$t_change = \Core\Relationship::get_description_for_history( $p_old_value ) . ' ' . \Core\Bug::format_id( $p_new_value );
						break;
					case BUG_DEL_RELATIONSHIP:
						$t_note = \Core\Lang::get( 'relationship_deleted' );
	
						# Fix for #7846: There are some cases where old value is empty, this may be due to an old bug.
						if( !\Core\Utility::is_blank( $p_old_value ) && $p_old_value > 0 ) {
							$t_change = \Core\Relationship::get_description_for_history( $p_old_value ) . ' ' . \Core\Bug::format_id( $p_new_value );
						} else {
							$t_change = \Core\Bug::format_id( $p_new_value );
						}
						break;
					case BUG_CLONED_TO:
						$t_note = \Core\Lang::get( 'bug_cloned_to' ) . ': ' . \Core\Bug::format_id( $p_new_value );
						break;
					case BUG_CREATED_FROM:
						$t_note = \Core\Lang::get( 'bug_created_from' ) . ': ' . \Core\Bug::format_id( $p_new_value );
						break;
					case TAG_ATTACHED:
						$t_note = \Core\Lang::get( 'tag_history_attached' ) . ': ' . $p_old_value;
						break;
					case TAG_DETACHED:
						$t_note = \Core\Lang::get( 'tag_history_detached' ) . ': ' . $p_old_value;
						break;
					case TAG_RENAMED:
						$t_note = \Core\Lang::get( 'tag_history_renamed' );
						$t_change = $p_old_value . ' => ' . $p_new_value;
						break;
					case BUG_REVISION_DROPPED:
						$t_note = \Core\Lang::get( 'bug_revision_dropped_history' ) . ': ' . \Core\Bug\Revision::get_type_name( $p_new_value ) . ': ' . $p_old_value;
						break;
					case BUGNOTE_REVISION_DROPPED:
						$t_note = \Core\Lang::get( 'bugnote_revision_dropped_history' ) . ': ' . $p_new_value . ': ' . $p_old_value;
						break;
				}
		}
	
		# output special cases
		if( NORMAL_TYPE == $p_type ) {
			$t_note = $t_field_localized;
			$t_change = $p_old_value . ' => ' . $p_new_value;
		}
	
		# end if DEFAULT
		return array( 'note' => $t_note, 'change' => $t_change, 'raw' => $t_raw );
	}
	
	/**
	 * delete all history associated with a bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function delete( $p_bug_id ) {
		$t_query = 'DELETE FROM {bug_history} WHERE bug_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_bug_id ) );
	}


}