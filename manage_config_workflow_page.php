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
 * Workflow Configuration Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses workflow_api.php
 */



\Core\Auth::reauthenticate();

\Core\HTML::page_top( \Core\Lang::get( 'manage_workflow_config' ) );

\Core\HTML::print_manage_menu( 'adm_permissions_report.php' );
\Core\HTML::print_manage_config_menu( 'manage_config_workflow_page.php' );

$g_access = \Core\Current_User::get_access_level();
$t_project = \Core\Helper::get_current_project();
$g_can_change_workflow = ( $g_access >= \Core\Config::get_access( 'status_enum_workflow' ) );
$g_can_change_flags = $g_can_change_workflow;
$g_overrides = array();

/**
 * Set overrides
 * @param string $p_config Configuration value.
 * @return void
 */
function set_overrides( $p_config ) {
	global $g_overrides;
	if( !in_array( $p_config, $g_overrides ) ) {
		$g_overrides[] = $p_config;
	}
}

/**
 * Returns a string to define the background color attribute depending
 * on the level where it's overridden
 * @param integer $p_level_file    Config file's access level.
 * @param integer $p_level_global  All projects' access level.
 * @param integer $p_level_project Current project's access level.
 * @return string class name or '' if no override.
 */
function set_color_override( $p_level_file, $p_level_global, $p_level_project ) {
	if( $p_level_project != $p_level_global ) {
		$t_color = 'color-project';
	} else if( $p_level_global != $p_level_file ) {
		$t_color = 'color-global';
	} else {
		$t_color = '';
	}

	return $t_color;
}


/**
 * Get the value associated with the specific action and flag.
 * @param integer $p_from_status_id From status id.
 * @param integer $p_to_status_id   To status id.
 * @return string
 */
function show_flag( $p_from_status_id, $p_to_status_id ) {
	global $g_can_change_workflow,
		$g_file_workflow, $g_global_workflow, $g_project_workflow,
		$t_resolved_status, $t_reopen_status, $t_reopen_label;
	if( $p_from_status_id <> $p_to_status_id ) {
		$t_file = isset( $g_file_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;
		$t_global = isset( $g_global_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;
		$t_project = isset( $g_project_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;

		$t_color = set_color_override( $t_file, $t_global, $t_project );
		if( $g_can_change_workflow && $t_color != '' ) {
			set_overrides( 'status_enum_workflow' );
		}
		$t_value = '<td class="center ' . $t_color . '">';

		$t_flag = ( 1 == $t_project );

		if( $g_can_change_workflow ) {
			$t_flag_name = $p_from_status_id . ':' . $p_to_status_id;
			$t_set = $t_flag ? 'checked="checked"' : '';
			$t_value .= '<input type="checkbox" name="flag[]" value="' . $t_flag_name . '" ' . $t_set . ' />';
		} else {
			$t_value .= $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&#160;';
		}

		# Add 'reopened' label
		if( $p_from_status_id >= $t_resolved_status && $p_to_status_id == $t_reopen_status ) {
			$t_value .= '<br />(' . $t_reopen_label . ')';
		}
	} else {
		$t_value = '<td>&#160;';
	}

	$t_value .= '</td>';

	return $t_value;
}

/**
 * section header
 * @param string $p_section_name Section name.
 * @return void
 */
function section_begin( $p_section_name ) {
	$t_enum_statuses = \Core\Enum::getValues( \Core\Config::mantis_get( 'status_enum_string' ) );
	echo '<div class="form-container">'. "\n";
	echo "\t<table>\n";
	echo "\t\t<thead>\n";
	echo "\t\t" . '<tr>' . "\n\t\t\t" . '<td class="form-title-caps" colspan="' . ( count( $t_enum_statuses ) + 2 ) . '">'
		. $p_section_name . '</td>' . "\n\t\t" . '</tr>' . "\n";
	echo "\t\t" . '<tr class="row-category2">' . "\n";
	echo "\t\t\t" . '<th class="form-title width30" rowspan="2">' . \Core\Lang::get( 'current_status' ) . '</th>'. "\n";
	echo "\t\t\t" . '<th class="form-title" style="text-align:center" colspan="' . ( count( $t_enum_statuses ) + 1 ) . '">'
		. \Core\Lang::get( 'next_status' ) . '</th>';
	echo "\n\t\t" . '</tr>'. "\n";
	echo "\t\t" . '<tr class="row-category2">' . "\n";

	foreach( $t_enum_statuses as $t_status ) {
		echo "\t\t\t" . '<th class="form-title" style="text-align:center">&#160;'
			. \Core\String::no_break( \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $t_status ) )
			. '&#160;</th>' ."\n";
	}

	echo "\t\t\t" . '<th class="form-title" style="text-align:center">' . \Core\Lang::get( 'custom_field_default_value' ) . '</th>' . "\n";
	echo "\t\t" . '</tr>' . "\n";
	echo "\t\t</thead>\n";
	echo "\t\t<tbody>\n";
}

/**
 * Print row
 * @param integer $p_from_status From status.
 * @return void
 */
function capability_row( $p_from_status ) {
	global $g_file_workflow, $g_global_workflow, $g_project_workflow, $g_can_change_workflow;
	$t_enum_status = \Core\Enum::getAssocArrayIndexedByValues( \Core\Config::mantis_get( 'status_enum_string' ) );
	echo "\t\t" .'<tr><td>' . \Core\String::no_break( \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $p_from_status ) ) . '</td>' . "\n";
	foreach ( $t_enum_status as $t_to_status_id => $t_to_status_label ) {
		echo show_flag( $p_from_status, $t_to_status_id );
	}

	$t_file = isset( $g_file_workflow['default'][$p_from_status] ) ? $g_file_workflow['default'][$p_from_status] : 0 ;
	$t_global = isset( $g_global_workflow['default'][$p_from_status] ) ? $g_global_workflow['default'][$p_from_status] : 0 ;
	$t_project = isset( $g_project_workflow['default'][$p_from_status] ) ? $g_project_workflow['default'][$p_from_status] : 0;

	$t_color = set_color_override( $t_file, $t_global, $t_project );
	if( $g_can_change_workflow && $t_color != '' ) {
		set_overrides( 'status_enum_workflow' );
	}
	echo "\t\t\t" . '<td class="center ' . $t_color . '">';
	if( $g_can_change_workflow ) {
		echo '<select name="default_' . $p_from_status . '">';
		\Core\Print_Util::enum_string_option_list( 'status', $t_project );
		echo '</select>';
	} else {
		echo \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $t_project );
	}
	echo ' </td>' . "\n";
	echo "\t\t" . '</tr>' . "\n";
}

/**
 * section footer
 * @return void
 */
function section_end() {
	echo '</tbody></table></div><br />' . "\n";
}

/**
 * threshold section begin
 * @param string $p_section_name Section name.
 * @return void
 */
function threshold_begin( $p_section_name ) {
	echo '<div class="form-container">';
	echo '<table>';
	echo '<thead>';
	echo "\t" . '<tr><td class="form-title" colspan="3">' . $p_section_name . '</td></tr>' . "\n";
	echo "\t" . '<tr class="row-category2">';
	echo "\t\t" . '<th class="form-title width30">' . \Core\Lang::get( 'threshold' ) . '</th>' . "\n";
	echo "\t\t" . '<th class="form-title" >' . \Core\Lang::get( 'status_level' ) . '</th>' . "\n";
	echo "\t\t" . '<th class="form-title" >' . \Core\Lang::get( 'alter_level' ) . '</th></tr>' . "\n";
	echo "\n";
	echo '</thead>';
	echo '<tbody>';
}

/**
 * threshold section row
 * @param string $p_threshold Threshold.
 * @return void
 */
function threshold_row( $p_threshold ) {
	global $g_access, $g_can_change_flags;

	$t_file = \Core\Config::get_global( $p_threshold );
	$t_global = \Core\Config::mantis_get( $p_threshold, null, ALL_USERS, ALL_PROJECTS );
	$t_project = \Core\Config::mantis_get( $p_threshold );
	$t_can_change_threshold = ( $g_access >= \Core\Config::get_access( $p_threshold ) );

	$t_color = set_color_override( $t_file, $t_global, $t_project );
	if( $t_can_change_threshold && $t_color != '' ) {
		set_overrides( $p_threshold );
	}

	echo '<tr><td>' . \Core\Lang::get( 'desc_' . $p_threshold ) . '</td>' . "\n";
	if( $t_can_change_threshold ) {
		echo '<td class="center ' . $t_color . '"><select name="threshold_' . $p_threshold . '">';
		\Core\Print_Util::enum_string_option_list( 'status', $t_project );
		echo '</select> </td>' . "\n";
		echo '<td><select name="access_' . $p_threshold . '">';
		\Core\Print_Util::enum_string_option_list( 'access_levels', \Core\Config::get_access( $p_threshold ) );
		echo '</select> </td>' . "\n";
		$g_can_change_flags = true;
	} else {
		echo '<td' . $t_color . '>' . \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $t_project ) . '&#160;</td>' . "\n";
		echo '<td>' . \Core\Enum::getLabel( \Core\Lang::get( 'access_levels_enum_string' ), \Core\Config::get_access( $p_threshold ) ) . '&#160;</td>' . "\n";
	}

	echo '</tr>' . "\n";
}

/**
 * threshold section end
 * @return void
 */
function threshold_end() {
	echo '</tbody></table></div><br />' . "\n";
}

/**
 * access begin
 * @param string $p_section_name Section name.
 * @return void
 */
function access_begin( $p_section_name ) {
	echo '<div class="form-container">';
	echo '<table>';
	echo '<thead>';
	echo "\t\t" . '<tr><td class="form-title" colspan="2">' . $p_section_name . '</td></tr>' . "\n";
	echo "\t\t" . '<tr class="row-category2"><th class="form-title" colspan="2">' . \Core\Lang::get( 'access_change' ) . '</th></tr>' . "\n";
	echo '</thead>';
	echo '<tbody>';
}

/**
 * access row
 * @return void
 */
function access_row() {
	global $g_access, $g_can_change_flags;

	$t_enum_status = \Core\Enum::getAssocArrayIndexedByValues( \Core\Config::mantis_get( 'status_enum_string' ) );

	$t_file_new = \Core\Config::get_global( 'report_bug_threshold' );
	$t_global_new = \Core\Config::mantis_get( 'report_bug_threshold', null, ALL_USERS, ALL_PROJECTS );
	$t_project_new = \Core\Config::mantis_get( 'report_bug_threshold' );

	$t_file_set = \Core\Config::get_global( 'set_status_threshold' );
	$t_global_set = \Core\Config::mantis_get( 'set_status_threshold', null, ALL_USERS, ALL_PROJECTS );
	$t_project_set = \Core\Config::mantis_get( 'set_status_threshold' );

	$t_submit_status = \Core\Config::mantis_get( 'bug_submit_status' );

	# Print the table rows
	foreach( $t_enum_status as $t_status => $t_status_label ) {
		echo "\t\t" . '<tr><td class="width30">'
			. \Core\String::no_break( \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $t_status ) ) . '</td>' . "\n";

		if( $t_status == $t_submit_status ) {
			# 'NEW' status
			$t_level_project = $t_project_new;

			$t_can_change = ( $g_access >= \Core\Config::get_access( 'report_bug_threshold' ) );
			$t_color = set_color_override( $t_file_new, $t_global_new, $t_project_new );
			if( $t_can_change  && $t_color != '' ) {
				set_overrides( 'report_bug_threshold' );
			}
		} else {
			# Other statuses

			# File level: fallback if set_status_threshold is not defined
			if( isset( $t_file_set[$t_status] ) ) {
				$t_level_file = $t_file_set[$t_status];
			} else {
				$t_level_file = \Core\Config::get_global( 'update_bug_status_threshold' );
			}

			$t_level_global  = isset( $t_global_set[$t_status] ) ? $t_global_set[$t_status] : $t_level_file;
			$t_level_project = isset( $t_project_set[$t_status] ) ? $t_project_set[$t_status] : $t_level_global;

			$t_can_change = ( $g_access >= \Core\Config::get_access( 'set_status_threshold' ) );
			$t_color = set_color_override( $t_level_file, $t_level_global, $t_level_project );
			if( $t_can_change  && $t_color != '' ) {
				set_overrides( 'set_status_threshold' );
			}
		}

		if( $t_can_change ) {
			echo '<td class="center ' . $t_color . '"><select name="access_change_' . $t_status . '">' . "\n";
			\Core\Print_Util::enum_string_option_list( 'access_levels', $t_level_project );
			echo '</select> </td>' . "\n";
			$g_can_change_flags = true;
		} else {
			echo '<td class="center ' . $t_color . '">'
				. \Core\Enum::getLabel( \Core\Lang::get( 'access_levels_enum_string' ), $t_level_project )
				. '</td>' . "\n";
		}

		echo '</tr>' . "\n";
	}
} # end function access_row

/**
 * access section end
 * @return void
 */
function access_end() {
	echo '</tbody></table></div><br />' . "\n";
}

echo '<br /><br />';

# count arcs in and out of each status
$t_enum_status = \Core\Config::mantis_get( 'status_enum_string' );
$t_status_arr  = \Core\Enum::getAssocArrayIndexedByValues( $t_enum_status );

$t_extra_enum_status = '0:non-existent,' . $t_enum_status;
$t_lang_enum_status = '0:' . \Core\Lang::get( 'non_existent' ) . ',' . \Core\Lang::get( 'status_enum_string' );
$t_all_status = explode( ',', $t_extra_enum_status );

# gather all versions of the workflow
$g_file_workflow = \Core\Workflow::parse( \Core\Config::get_global( 'status_enum_workflow' ) );
$g_global_workflow = \Core\Workflow::parse( \Core\Config::mantis_get( 'status_enum_workflow', null, ALL_USERS, ALL_PROJECTS ) );
$g_project_workflow = \Core\Workflow::parse( \Core\Config::mantis_get( 'status_enum_workflow', null, ALL_USERS, $t_project ) );

# validate the project workflow
$t_validation_result = '';
foreach( $t_status_arr as $t_status => $t_label ) {
	if( isset( $g_project_workflow['exit'][$t_status][$t_status] ) ) {
		$t_validation_result .= '<tr><td>'
						. \Core\Enum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FFED4F">' . \Core\Lang::get( 'superfluous' ) . '</td></tr>';
	}
}

# check for entry == 0 without exit == 0, unreachable state
foreach( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $g_project_workflow['entry'][$t_status] ) ) && ( 0 < count( $g_project_workflow['exit'][$t_status] ) ) ) {
		$t_validation_result .= '<tr><td>'
						. \Core\Enum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . \Core\Lang::get( 'unreachable' ) . '</td></tr>';
	}
}

# check for exit == 0 without entry == 0, unleaveable state
foreach( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $g_project_workflow['exit'][$t_status] ) ) && ( 0 < count( $g_project_workflow['entry'][$t_status] ) ) ) {
		$t_validation_result .= '<tr><td>'
						. \Core\Enum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . \Core\Lang::get( 'no_exit' ) . '</td></tr>';
	}
}

# check for exit == 0 and entry == 0, isolated state
foreach ( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $g_project_workflow['exit'][$t_status] ) ) && ( 0 == count( $g_project_workflow['entry'][$t_status] ) ) ) {
		$t_validation_result .= '<tr><td>'
						. \Core\Enum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . \Core\Lang::get( 'unreachable' ) . '<br />' . \Core\Lang::get( 'no_exit' ) . '</td></tr>';
	}
}

echo '<form id="workflow_config_action" method="post" action="manage_config_workflow_set.php">' . "\n";
echo '<fieldset>';
echo \Core\Form::security_field( 'manage_config_workflow_set' );
echo '</fieldset>';

if( ALL_PROJECTS == $t_project ) {
	$t_project_title = \Core\Lang::get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( \Core\Lang::get( 'config_project' ), \Core\String::display( \Core\Project::get_name( $t_project ) ) );
}
echo '<p class="bold">' . $t_project_title . '</p>' . "\n";
echo '<p>' . \Core\Lang::get( 'colour_coding' ) . '<br />';
if( ALL_PROJECTS <> $t_project ) {
	echo '<span class="color-project">' . \Core\Lang::get( 'colour_project' ) .'</span><br />';
}
echo '<span class="color-global">' . \Core\Lang::get( 'colour_global' ) . '</span></p>';

# show the settings used to derive the table
threshold_begin( \Core\Lang::get( 'workflow_thresholds' ) );
if( !is_array( \Core\Config::mantis_get( 'bug_submit_status' ) ) ) {
	threshold_row( 'bug_submit_status' );
}
threshold_row( 'bug_resolved_status_threshold' );
threshold_row( 'bug_reopen_status' );
threshold_end();
echo '<br />';

if( '' <> $t_validation_result ) {
	echo '<table class="width100">';
	echo '<tr><td class="form-title" colspan="3">' . \Core\Lang::get( 'validation' ) . '</td></tr>' . "\n";
	echo '<tr><td class="form-title width30">' . \Core\Lang::get( 'status' ) . '</td>';
	echo '<td class="form-title" >' . \Core\Lang::get( 'comment' ) . '</td></tr>';
	echo "\n";
	echo $t_validation_result;
	echo '</table><br /><br />';
}

# Initialization for 'reopened' label handling
$t_resolved_status = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
$t_reopen_status = \Core\Config::mantis_get( 'bug_reopen_status' );
$t_reopen_label = \Core\Enum::getLabel( \Core\Lang::get( 'resolution_enum_string' ), \Core\Config::mantis_get( 'bug_reopen_resolution' ) );

# display the graph as a matrix
section_begin( \Core\Lang::get( 'workflow' ) );
foreach ( $t_status_arr as $t_from_status => $t_from_label ) {
	capability_row( $t_from_status );
}
section_end();

if( $g_can_change_workflow ) {
	echo '<p>' . \Core\Lang::get( 'workflow_change_access_label' );
	echo '<select name="workflow_access">';
	\Core\Print_Util::enum_string_option_list( 'access_levels', \Core\Config::get_access( 'status_enum_workflow' ) );
	echo '</select> </p><br />';
}

# display the access levels required to move an issue
access_begin( \Core\Lang::get( 'access_levels' ) );
access_row();
access_end();

if( $g_access >= \Core\Config::get_access( 'set_status_threshold' ) ) {
	echo '<p>' . \Core\Lang::get( 'access_change_access_label' );
	echo '<select name="status_access">';
	\Core\Print_Util::enum_string_option_list( 'access_levels', \Core\Config::get_access( 'set_status_threshold' ) );
	echo '</select> </p><br />';
}

if( $g_can_change_flags ) {
	echo '<input type="submit" class="button" value="' . \Core\Lang::get( 'change_configuration' ) . '" />' . "\n";
	echo '</form>' . "\n";

	if( 0 < count( $g_overrides ) ) {
		echo '<div class="right"><form id="mail_config_action" method="post" action="manage_config_revert.php">' ."\n";
		echo '<fieldset>' . "\n";
		echo \Core\Form::security_field( 'manage_config_revert' );
		echo '<input name="revert" type="hidden" value="' . implode( ',', $g_overrides ) . '" />';
		echo '<input name="project" type="hidden" value="' . $t_project . '" />';
		echo '<input name="return" type="hidden" value="' . \Core\String::attribute( \Core\Form::action_self() ) .'" />';
		echo '<input type="submit" class="button" value=';
		if( ALL_PROJECTS == $t_project ) {
			echo \Core\Lang::get( 'revert_to_system' );
		} else {
			echo \Core\Lang::get( 'revert_to_all_project' );
		}
		echo '" />' . "\n";
		echo '</fieldset>' . "\n";
		echo '</form></div>' . "\n";
	}

} else {
	echo '</form>' . "\n";
}

\Core\HTML::page_bottom();
