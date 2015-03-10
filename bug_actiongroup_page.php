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
 * This page allows actions to be performed on an array of bugs
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
 * @uses bug_group_action_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'custom_field_api.php' );

\Core\Auth::ensure_user_authenticated();

$f_action = \Core\GPC::get_string( 'action', '' );
$f_bug_arr = \Core\GPC::get_int_array( 'bug_arr', array() );

# redirects to all_bug_page if nothing is selected
if( \Core\Utility::is_blank( $f_action ) || ( 0 == count( $f_bug_arr ) ) ) {
	\Core\Print_Util::header_redirect( 'view_all_bug_page.php' );
}

# run through the issues to see if they are all from one project
$t_project_id = ALL_PROJECTS;
$t_multiple_projects = false;
$t_projects = array();

\Core\Bug::cache_array_rows( $f_bug_arr );

foreach( $f_bug_arr as $t_bug_id ) {
	$t_bug = \Core\Bug::get( $t_bug_id );
	if( $t_project_id != $t_bug->project_id ) {
		if( ( $t_project_id != ALL_PROJECTS ) && !$t_multiple_projects ) {
			$t_multiple_projects = true;
		} else {
			$t_project_id = $t_bug->project_id;
			$t_projects[$t_project_id] = $t_project_id;
		}
	}
}
if( $t_multiple_projects ) {
	$t_project_id = ALL_PROJECTS;
	$t_projects[ALL_PROJECTS] = ALL_PROJECTS;
}
# override the project if necessary
if( $t_project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_project_id;
}

define( 'BUG_ACTIONGROUP_INC_ALLOW', true );

$t_finished = false;
$t_bugnote = false;

$t_external_action_prefix = 'EXT_';
if( strpos( $f_action, $t_external_action_prefix ) === 0 ) {
	$t_form_page = 'bug_actiongroup_ext_page.php';
	require_once( $t_form_page );
	exit;
}

$t_custom_group_actions = \Core\Config::mantis_get( 'custom_group_actions' );

foreach( $t_custom_group_actions as $t_custom_group_action ) {
	if( $f_action == $t_custom_group_action['action'] ) {
		require_once( $t_custom_group_action['form_page'] );
		exit;
	}
}

# Check if user selected to update a custom field.
$t_custom_fields_prefix = 'custom_field_';
if( strpos( $f_action, $t_custom_fields_prefix ) === 0 ) {
	$t_custom_field_id = (int)substr( $f_action, utf8_strlen( $t_custom_fields_prefix ) );
	$f_action = 'CUSTOM';
}

# Form name
$t_form_name = 'bug_actiongroup_' . $f_action;

switch( $f_action ) {
	# Use a simple confirmation page, if close or delete...
	case 'CLOSE' :
		$t_finished 			= true;
		$t_question_title 		= \Core\Lang::get( 'close_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'close_group_bugs_button' );
		$t_bugnote				= true;
		break;
	case 'DELETE' :
		$t_finished 			= true;
		$t_question_title		= \Core\Lang::get( 'delete_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'delete_group_bugs_button' );
		break;
	case 'SET_STICKY' :
		$t_finished 			= true;
		$t_question_title		= \Core\Lang::get( 'set_sticky_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'set_sticky_group_bugs_button' );
		break;
	# ...else we define the variables used in the form
	case 'MOVE' :
		$t_question_title 		= \Core\Lang::get( 'move_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'move_group_bugs_button' );
		$t_form					= 'project_id';
		break;
	case 'COPY' :
		$t_question_title 		= \Core\Lang::get( 'copy_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'copy_group_bugs_button' );
		$t_form					= 'project_id';
		break;
	case 'ASSIGN' :
		$t_question_title 		= \Core\Lang::get( 'assign_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'assign_group_bugs_button' );
		$t_form 				= 'assign';
		break;
	case 'RESOLVE' :
		$t_question_title 		= \Core\Lang::get( 'resolve_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'resolve_group_bugs_button' );
		$t_form 				= 'resolution';
		if( ALL_PROJECTS != $t_project_id ) {
			$t_question_title2 = \Core\Lang::get( 'fixed_in_version' );
			$t_form2 = 'fixed_in_version';
		}
		$t_bugnote				= true;
		break;
	case 'UP_PRIOR' :
		$t_question_title 		= \Core\Lang::get( 'priority_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'priority_group_bugs_button' );
		$t_form 				= 'priority';
		break;
	case 'UP_STATUS' :
		$t_question_title 		= \Core\Lang::get( 'status_bugs_conf_msg' );
		$t_button_title 		= \Core\Lang::get( 'status_group_bugs_button' );
		$t_form 				= 'status';
		$t_bugnote				= true;
		break;
	case 'UP_CATEGORY' :
		$t_question_title		= \Core\Lang::get( 'category_bugs_conf_msg' );
		$t_button_title			= \Core\Lang::get( 'category_group_bugs_button' );
		$t_form					= 'category';
		break;
	case 'VIEW_STATUS' :
		$t_question_title		= \Core\Lang::get( 'view_status_bugs_conf_msg' );
		$t_button_title			= \Core\Lang::get( 'view_status_group_bugs_button' );
		$t_form					= 'view_status';
		break;
	case 'UP_PRODUCT_VERSION':
		$t_question_title		= \Core\Lang::get( 'product_version_bugs_conf_msg' );
		$t_button_title			= \Core\Lang::get( 'product_version_group_bugs_button' );
		$t_form					= 'product_version';
		break;
	case 'UP_FIXED_IN_VERSION':
		$t_question_title		= \Core\Lang::get( 'fixed_in_version_bugs_conf_msg' );
		$t_button_title			= \Core\Lang::get( 'fixed_in_version_group_bugs_button' );
		$t_form					= 'fixed_in_version';
		break;
	case 'UP_TARGET_VERSION':
		$t_question_title		= \Core\Lang::get( 'target_version_bugs_conf_msg' );
		$t_button_title			= \Core\Lang::get( 'target_version_group_bugs_button' );
		$t_form					= 'target_version';
		break;
	case 'CUSTOM' :
		$t_custom_field_def = custom_field_get_definition( $t_custom_field_id );
		$t_question_title = sprintf( \Core\Lang::get( 'actiongroup_menu_update_field' ), \Core\Lang::get_defaulted( $t_custom_field_def['name'] ) );
		$t_button_title = $t_question_title;
		$t_form = 'custom_field_' . $t_custom_field_id;
		break;
	default:
		trigger_error( ERROR_GENERIC, ERROR );
}

\Core\Bug\Group::action_print_top();

if( $t_multiple_projects ) {
	echo '<p class="bold">' . \Core\Lang::get( 'multiple_projects' ) . '</p>';
}
?>

<br />

<div id="action-group-div" class="form-container">
	<form method="post" action="bug_actiongroup.php">
		<?php echo \Core\Form::security_field( $t_form_name ); ?>
		<input type="hidden" name="action" value="<?php echo \Core\String::attribute( $f_action ) ?>" />
<?php
	\Core\Bug\Group::action_print_hidden_fields( $f_bug_arr );

	if( $f_action === 'CUSTOM' ) {
		echo '<input type="hidden" name="custom_field_id" value="' . $t_custom_field_id . '" />';
	}
?>
		<table>
			<tbody>
<?php
	if( !$t_finished ) {
?>
				<tr class="row-1">
					<th class="category">
						<?php echo $t_question_title ?>
					</th>
					<td>
<?php
		if( $f_action === 'CUSTOM' ) {
			$t_custom_field_def = custom_field_get_definition( $t_custom_field_id );

			$t_bug_id = null;

			# if there is only one issue, use its current value as default, otherwise,
			# use the default value specified in custom field definition.
			if( count( $f_bug_arr ) == 1 ) {
				$t_bug_id = $f_bug_arr[0];
			}

			print_custom_field_input( $t_custom_field_def, $t_bug_id );
		} else {
			echo '<select name="' . $t_form . '">';

			switch( $f_action ) {
				case 'COPY':
				case 'MOVE':
					\Core\Print_Util::project_option_list( null, false );
					break;
				case 'ASSIGN':
					\Core\Print_Util::assign_to_option_list( 0, $t_project_id );
					break;
				case 'RESOLVE':
					\Core\Print_Util::enum_string_option_list( 'resolution', \Core\Config::mantis_get( 'bug_resolution_fixed_threshold' ) );
					break;
				case 'UP_PRIOR':
					\Core\Print_Util::enum_string_option_list( 'priority', \Core\Config::mantis_get( 'default_bug_priority' ) );
					break;
				case 'UP_STATUS':
					\Core\Print_Util::enum_string_option_list( 'status', \Core\Config::mantis_get( 'bug_submit_status' ) );
					break;
				case 'UP_CATEGORY':
					\Core\Print_Util::category_option_list();
					break;
				case 'VIEW_STATUS':
					\Core\Print_Util::enum_string_option_list( 'view_state', \Core\Config::mantis_get( 'default_bug_view_status' ) );
					break;
				case 'UP_TARGET_VERSION':
					\Core\Print_Util::version_option_list( '', $t_project_id, VERSION_FUTURE, true, true );
					break;
				case 'UP_PRODUCT_VERSION':
				case 'UP_FIXED_IN_VERSION':
					\Core\Print_Util::version_option_list( '', $t_project_id, VERSION_ALL, true, true );
					break;
			}

			echo '</select>';
		}
?>
					</td>
				</tr>
<?php
		if( isset( $t_question_title2 ) ) {
			switch( $f_action ) {
				case 'RESOLVE':
					$t_show_product_version = ( ON == \Core\Config::mantis_get( 'show_product_version' ) )
						|| ( ( AUTO == \Core\Config::mantis_get( 'show_product_version' ) )
									&& ( count( \Core\Version::get_all_rows( $t_project_id ) ) > 0 ) );
					if( $t_show_product_version ) {
?>
				<tr class="row-2">
					<th class="category">
						<?php echo $t_question_title2 ?>
					</th>
					<td>
						<select name="<?php echo $t_form2 ?>">
							<?php \Core\Print_Util::version_option_list( '', null, VERSION_ALL );?>
						</select>
					</td>
				</tr>
<?php
					}
					break;
			}
		}
	} else {
?>
				<tr class="row-1">
					<th class="category" colspan="2">
						<?php echo $t_question_title; ?>
					</th>
				</tr>
<?php
	}

	if( $t_bugnote ) {
?>
				<tr class="row-1">
					<th class="category">
						<?php echo \Core\Lang::get( 'add_bugnote_title' ); ?>
					</th>
					<td>
						<textarea name="bugnote_text" cols="80" rows="10"></textarea>
					</td>
				</tr>
<?php
		if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'private_bugnote_threshold' ), $t_project_id ) ) {
?>
				<tr>
					<th class="category">
						<?php echo \Core\Lang::get( 'view_status' ) ?>
					</th>
					<td>
<?php
			$t_default_bugnote_view_status = \Core\Config::mantis_get( 'default_bugnote_view_status' );
			if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'set_view_status_threshold' ), $t_project_id ) ) {
?>
						<input type="checkbox" name="private" <?php \Core\Helper::check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
<?php
				echo \Core\Lang::get( 'private' );
			} else {
				echo \Core\Helper::get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
			}
?>
					</td>
				</tr>
<?php
		}
	}
?>
			</tbody>

			<tfoot>
				<tr>
					<td class="center" colspan="2">
						<input type="submit" class="button" value="<?php echo $t_button_title ?>" />
					</td>
				</tr>
			</tfoot>
		</table>

	</form>
</div>

<br />

<?php
\Core\Bug\Group::action_print_bug_list( $f_bug_arr );
\Core\Bug\Group::action_print_bottom();
