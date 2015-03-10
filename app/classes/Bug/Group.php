<?php
namespace Flickerbox\Bug;


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
 * Bug Group Action API
 *
 * @package CoreAPI
 * @subpackage BugGroupActionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses string_api.php
 */


\Flickerbox\HTML::require_css( 'status_config.php' );


class Group
{

	/**
	 * Initialise bug action group api
	 * @param string $p_action Custom action to run.
	 * @return void
	 */
	static function action_init( $p_action ) {
		$t_valid_actions = \Flickerbox\Bug\Group::action_get_commands( \Flickerbox\Current_User::get_accessible_projects() );
		$t_action = strtoupper( $p_action );
	
		if( !isset( $t_valid_actions[$t_action] ) &&
			!isset( $t_valid_actions['EXT_' . $t_action] )
			) {
			trigger_error( ERROR_GENERIC, ERROR );
		}
	
		$t_include_file = \Flickerbox\Config::get_global( 'absolute_path' ) . 'bug_actiongroup_' . $p_action . '_inc.php';
		if( !file_exists( $t_include_file ) ) {
			trigger_error( ERROR_GENERIC, ERROR );
		} else {
			require_once( $t_include_file );
		}
	}
	
	/**
	 * Print the top part for the bug action group page.
	 * @return void
	 */
	static function action_print_top() {
		\Flickerbox\HTML::page_top();
	}
	
	/**
	 * Print the bottom part for the bug action group page.
	 * @return void
	 */
	static function action_print_bottom() {
		\Flickerbox\HTML::page_bottom();
	}
	
	/**
	 * Print the list of selected issues and the legend for the status colors.
	 *
	 * @param array $p_bug_ids_array An array of issue ids.
	 * @return void
	 */
	static function action_print_bug_list( array $p_bug_ids_array ) {
		$t_legend_position = \Flickerbox\Config::mantis_get( 'status_legend_position' );
	
		if( STATUS_LEGEND_POSITION_TOP == $t_legend_position ) {
			\Flickerbox\HTML::status_legend();
			echo '<br />';
		}
	
		echo '<div id="action-group-issues-div">';
		echo '<table>';
		echo '<tr class="row-1">';
		echo '<th class="category" colspan="2">';
		echo \Flickerbox\Lang::get( 'actiongroup_bugs' );
		echo '</th>';
		echo '</tr>';
	
		$t_i = 1;
	
		foreach( $p_bug_ids_array as $t_bug_id ) {
			# choose color based on status
			$t_status_label = \Flickerbox\HTML::get_status_css_class( \Flickerbox\Bug::get_field( $t_bug_id, 'status' ), auth_get_current_user_id(), \Flickerbox\Bug::get_field( $t_bug_id, 'project_id' ) );
	
			echo sprintf( "<tr class=\"%s\"> <td>%s</td> <td>%s</td> </tr>\n", $t_status_label, \Flickerbox\String::get_bug_view_link( $t_bug_id ), \Flickerbox\String::attribute( \Flickerbox\Bug::get_field( $t_bug_id, 'summary' ) ) );
		}
	
		echo '</table>';
		echo '</div>';
	
		if( STATUS_LEGEND_POSITION_BOTTOM == $t_legend_position ) {
			echo '<br />';
			\Flickerbox\HTML::status_legend();
		}
	}
	
	/**
	 * Print the array of issue ids via hidden fields in the form to be passed on to
	 * the bug action group action page.
	 *
	 * @param array $p_bug_ids_array An array of issue ids.
	 * @return void
	 */
	static function action_print_hidden_fields( array $p_bug_ids_array ) {
		foreach( $p_bug_ids_array as $t_bug_id ) {
			echo '<input type="hidden" name="bug_arr[]" value="' . $t_bug_id . '" />' . "\n";
		}
	}
	
	/**
	 * Prints the list of fields in the custom action form.  These are the user inputs
	 * and the submit button.  This ends up calling action_<action>_print_fields()
	 * from bug_actiongroup_<action>_inc.php
	 *
	 * @param string $p_action The custom action name without the "EXT_" prefix.
	 * @return void
	 */
	static function action_print_action_fields( $p_action ) {
		$t_function_name = 'action_' . $p_action . '_print_fields';
		$t_function_name();
	}
	
	/**
	 * Prints some title text for the custom action page.  This ends up calling
	 * action_<action>_print_title() from bug_actiongroup_<action>_inc.php
	 *
	 * @param string $p_action The custom action name without the "EXT_" prefix.
	 * @return void
	 */
	static function action_print_title( $p_action ) {
		$t_function_name = 'action_' . $p_action . '_print_title';
		$t_function_name();
	}
	
	/**
	 * Validates the combination of an action and a bug.  This ends up calling
	 * action_<action>_validate() from bug_actiongroup_<action>_inc.php
	 *
	 * @param string  $p_action The custom action name without the "EXT_" prefix.
	 * @param integer $p_bug_id The id of the bug to validate the action on.
	 *
	 * @return boolean|array true if action can be applied or array of ( bug_id => reason for failure to validate )
	 */
	static function action_validate( $p_action, $p_bug_id ) {
		$t_function_name = 'action_' . $p_action . '_validate';
		return $t_function_name( $p_bug_id );
	}
	
	/**
	 * Executes an action on a bug.  This ends up calling
	 * action_<action>_process() from bug_actiongroup_<action>_inc.php
	 *
	 * @param string  $p_action The custom action name without the "EXT_" prefix.
	 * @param integer $p_bug_id The id of the bug to validate the action on.
	 * @return boolean|array Action can be applied., ( bug_id => reason for failure to process )
	 */
	static function action_process( $p_action, $p_bug_id ) {
		$t_function_name = 'action_' . $p_action . '_process';
		return $t_function_name( $p_bug_id );
	}
	
	/**
	 * Get a list of bug group actions available to the current user for one or
	 * more projects.
	 * @param array $p_project_ids An array containing one or more project IDs.
	 * @return array
	 */
	static function action_get_commands( array $p_project_ids = null ) {
		if( $p_project_ids === null || count( $p_project_ids ) == 0 ) {
			$p_project_ids = array( ALL_PROJECTS );
		}
	
		$t_commands = array();
		foreach( $p_project_ids as $t_project_id ) {
	
			if( !isset( $t_commands['MOVE'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'move_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['MOVE'] = \Flickerbox\Lang::get( 'actiongroup_menu_move' );
			}
	
			if( !isset( $t_commands['COPY'] ) &&
				\Flickerbox\Access::has_any_project( \Flickerbox\Config::mantis_get( 'report_bug_threshold', null, null, $t_project_id ) ) ) {
				$t_commands['COPY'] = \Flickerbox\Lang::get( 'actiongroup_menu_copy' );
			}
	
			if( !isset( $t_commands['ASSIGN'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_assign_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				if( ON == \Flickerbox\Config::mantis_get( 'auto_set_status_to_assigned', null, null, $t_project_id ) &&
					\Flickerbox\Access::has_project_level( \Flickerbox\Access::get_status_threshold( \Flickerbox\Config::mantis_get( 'bug_assigned_status', null, null, $t_project_id ), $t_project_id ), $t_project_id ) ) {
					$t_commands['ASSIGN'] = \Flickerbox\Lang::get( 'actiongroup_menu_assign' );
				} else {
					$t_commands['ASSIGN'] = \Flickerbox\Lang::get( 'actiongroup_menu_assign' );
				}
			}
	
			if( !isset( $t_commands['CLOSE'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_status_threshold', null, null, $t_project_id ), $t_project_id ) &&
				( \Flickerbox\Access::has_project_level( \Flickerbox\Access::get_status_threshold( \Flickerbox\Config::mantis_get( 'bug_closed_status_threshold', null, null, $t_project_id ), $t_project_id ), $t_project_id ) ||
					\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'allow_reporter_close', null, null, $t_project_id ), $t_project_id ) ) ) {
				$t_commands['CLOSE'] = \Flickerbox\Lang::get( 'actiongroup_menu_close' );
			}
	
			if( !isset( $t_commands['DELETE'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'delete_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['DELETE'] = \Flickerbox\Lang::get( 'actiongroup_menu_delete' );
			}
	
			if( !isset( $t_commands['RESOLVE'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_status_threshold', null, null, $t_project_id ), $t_project_id ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Access::get_status_threshold( \Flickerbox\Config::mantis_get( 'bug_resolved_status_threshold', null, null, $t_project_id ), $t_project_id ), $t_project_id ) ) {
				$t_commands['RESOLVE'] = \Flickerbox\Lang::get( 'actiongroup_menu_resolve' );
			}
	
			if( !isset( $t_commands['SET_STICKY'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'set_bug_sticky_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['SET_STICKY'] = \Flickerbox\Lang::get( 'actiongroup_menu_set_sticky' );
			}
	
			if( !isset( $t_commands['UP_PRIOR'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['UP_PRIOR'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_priority' );
			}
	
			if( !isset( $t_commands['EXT_UPDATE_SEVERITY'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['EXT_UPDATE_SEVERITY'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_severity' );
			}
	
			if( !isset( $t_commands['UP_STATUS'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_status_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['UP_STATUS'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_status' );
			}
	
			if( !isset( $t_commands['UP_CATEGORY'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['UP_CATEGORY'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_category' );
			}
	
			if( !isset( $t_commands['VIEW_STATUS'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'change_view_status_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['VIEW_STATUS'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_view_status' );
			}
	
			if( !isset( $t_commands['EXT_UPDATE_PRODUCT_BUILD'] ) &&
				\Flickerbox\Config::mantis_get( 'enable_product_build', null, null, $t_project_id ) == ON &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['EXT_UPDATE_PRODUCT_BUILD'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_product_build' );
			}
	
			if( !isset( $t_commands['EXT_ADD_NOTE'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'add_bugnote_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['EXT_ADD_NOTE'] = \Flickerbox\Lang::get( 'actiongroup_menu_add_note' );
			}
	
			if( !isset( $t_commands['EXT_ATTACH_TAGS'] ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'tag_attach_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['EXT_ATTACH_TAGS'] = \Flickerbox\Lang::get( 'actiongroup_menu_attach_tags' );
			}
	
			if( !isset( $t_commands['UP_PRODUCT_VERSION'] ) &&
				\Flickerbox\Version::should_show_product_version( $t_project_id ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['UP_PRODUCT_VERSION'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_product_version' );
			}
	
			if( !isset( $t_commands['UP_FIXED_IN_VERSION'] ) &&
				\Flickerbox\Version::should_show_product_version( $t_project_id ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['UP_FIXED_IN_VERSION'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_fixed_in_version' );
			}
	
			if( !isset( $t_commands['UP_TARGET_VERSION'] ) &&
				\Flickerbox\Version::should_show_product_version( $t_project_id ) &&
				\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'roadmap_update_threshold', null, null, $t_project_id ), $t_project_id ) ) {
				$t_commands['UP_TARGET_VERSION'] = \Flickerbox\Lang::get( 'actiongroup_menu_update_target_version' );
			}
	
			$t_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
			foreach( $t_custom_field_ids as $t_custom_field_id ) {
				if( !custom_field_has_write_access_to_project( $t_custom_field_id, $t_project_id ) ) {
					continue;
				}
				$t_custom_field_def = custom_field_get_definition( $t_custom_field_id );
				$t_command_id = 'custom_field_' . $t_custom_field_id;
				$t_command_caption = sprintf( \Flickerbox\Lang::get( 'actiongroup_menu_update_field' ), \Flickerbox\Lang::get_defaulted( $t_custom_field_def['name'] ) );
				$t_commands[$t_command_id] = \Flickerbox\String::display( $t_command_caption );
			}
		}
	
		$t_custom_group_actions = \Flickerbox\Config::mantis_get( 'custom_group_actions' );
	
		foreach( $t_custom_group_actions as $t_custom_group_action ) {
			# use label if provided to get the localized text, otherwise fallback to action name.
			if( isset( $t_custom_group_action['label'] ) ) {
				$t_commands[$t_custom_group_action['action']] = \Flickerbox\Lang::get_defaulted( $t_custom_group_action['label'] );
			} else {
				$t_commands[$t_custom_group_action['action']] = \Flickerbox\Lang::get_defaulted( $t_custom_group_action['action'] );
			}
		}
	
		return $t_commands;
	}

}