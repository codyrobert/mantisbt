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
 * Display advanced Bug update page
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
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;




\Core\HTML::require_css( 'status_config.php' );

$f_bug_id = \Core\GPC::get_int( 'bug_id' );
$f_reporter_edit = \Core\GPC::get_bool( 'reporter_edit' );

$t_bug = \Core\Bug::get( $f_bug_id, true );

if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( \Core\Bug::is_readonly( $f_bug_id ) ) {
	\Core\Error::parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'update_bug_threshold' ), $f_bug_id );

$t_fields = \Core\Config::mantis_get( 'bug_update_page_fields' );
$t_fields = \Core\Columns::filter_disabled( $t_fields );

$t_bug_id = $f_bug_id;

$t_action_button_position = \Core\Config::mantis_get( 'action_button_position' );

$t_top_buttons_enabled = $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH;
$t_bottom_buttons_enabled = $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH;

$t_show_id = in_array( 'id', $t_fields );
$t_show_project = in_array( 'project', $t_fields );
$t_show_category = in_array( 'category_id', $t_fields );
$t_show_view_state = in_array( 'view_state', $t_fields );
$t_view_state = $t_show_view_state ? \Core\String::display_line( \Core\Helper::get_enum_element( 'view_state', $t_bug->view_state ) ) : '';
$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_show_status = in_array( 'status', $t_fields );
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_show_projection = in_array( 'projection', $t_fields ) && \Core\Config::mantis_get( 'enable_projection' ) == ON;
$t_show_eta = in_array( 'eta', $t_fields ) && \Core\Config::mantis_get( 'enable_eta' ) == ON;
$t_show_profiles = \Core\Config::mantis_get( 'enable_profiles' ) == ON;
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_show_versions = \Core\Version::should_show_product_version( $t_bug->project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && ( \Core\Config::mantis_get( 'enable_product_build' ) == ON );
$t_product_build_attribute = $t_show_product_build ? \Core\String::attribute( $t_bug->build ) : '';
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && \Core\Access::has_bug_level( \Core\Config::mantis_get( 'roadmap_update_threshold' ), $t_bug_id );
$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
$t_show_due_date = in_array( 'due_date', $t_fields ) && \Core\Access::has_bug_level( \Core\Config::mantis_get( 'due_date_view_threshold' ), $t_bug_id );
$t_show_summary = in_array( 'summary', $t_fields );
$t_summary_attribute = $t_show_summary ? \Core\String::attribute( $t_bug->summary ) : '';
$t_show_description = in_array( 'description', $t_fields );
$t_description_textarea = $t_show_description ? \Core\String::textarea( $t_bug->description ) : '';
$t_show_additional_information = in_array( 'additional_info', $t_fields );
$t_additional_information_textarea = $t_show_additional_information ? \Core\String::textarea( $t_bug->additional_information ) : '';
$t_show_steps_to_reproduce = in_array( 'steps_to_reproduce', $t_fields );
$t_steps_to_reproduce_textarea = $t_show_steps_to_reproduce ? \Core\String::textarea( $t_bug->steps_to_reproduce ) : '';
if( NO_USER == $t_bug->handler_id ) {
	$t_handler_name =  '';
} else {
	$t_handler_name = \Core\String::display_line( \Core\User::get_name( $t_bug->handler_id ) );
}

$t_can_change_view_state = $t_show_view_state && \Core\Access::has_project_level( \Core\Config::mantis_get( 'change_view_status_threshold' ) );

if( $t_show_product_version ) {
	$t_product_version_released_mask = VERSION_RELEASED;

	if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
		$t_product_version_released_mask = VERSION_ALL;
	}
}

$t_formatted_bug_id = $t_show_id ? \Core\Bug::format_id( $f_bug_id ) : '';
$t_project_name = $t_show_project ? \Core\String::display_line( \Core\Project::get_name( $t_bug->project_id ) ) : '';

if( $t_show_due_date ) {
	\Core\HTML::require_js( 'jscalendar/calendar.js' );
	\Core\HTML::require_js( 'jscalendar/lang/calendar-en.js' );
	\Core\HTML::require_js( 'jscalendar/calendar-setup.js' );
	\Core\HTML::require_css( 'calendar-blue.css' );
}

\Core\HTML::page_top( \Core\Bug::format_summary( $f_bug_id, SUMMARY_CAPTION ) );

\Core\Print_Util::recently_visited();

?>
<br />
<div id="bug-update" class="form-container">
	<form id="update_bug_form" method="post" action="bug_update.php">
		<?php echo \Core\Form::security_field( 'bug_update' ); ?>
		<table>
			<thead>
				<tr>
					<td class="form-title" colspan="3">
						<input type="hidden" name="bug_id" value="<?php echo $t_bug_id ?>" />
						<input type="hidden" name="last_updated" value="<?php echo $t_bug->last_updated ?>" />
						<?php echo \Core\Lang::get( 'updating_bug_advanced_title' ); ?>
					</td>
					<td class="right" colspan="3">
						<?php \Core\Print_Util::bracket_link( \Core\String::get_bug_view_url( $t_bug_id ), \Core\Lang::get( 'back_to_bug_link' ) );
						?>
					</td>
				</tr>

<?php
# Submit Button
if( $t_top_buttons_enabled ) {
?>
				<tr>
					<td class="center" colspan="6">
						<input <?php \Core\Helper::get_tab_index(); ?>
							type="submit" class="button"
							value="<?php echo \Core\Lang::get( 'update_information_button' ); ?>" />
					</td>
				</tr>
			</thead>

<?php
}
?>
			<tbody>
<?php
\Core\Event::signal( 'EVENT_UPDATE_BUG_FORM_TOP', array( $t_bug_id, true ) );

if( $t_show_id || $t_show_project || $t_show_category || $t_show_view_state || $t_show_date_submitted | $t_show_last_updated ) {
	#
	# Titles for Bug Id, Project Name, Category, View State, Date Submitted, Last Updated
	#

	echo '<tr>';
	echo '<td width="15%" class="category">', $t_show_id ? \Core\Lang::get( 'id' ) : '', '</td>';
	echo '<td width="20%" class="category">', $t_show_project ? \Core\Lang::get( 'email_project' ) : '', '</td>';
	echo '<td width="15%" class="category">', $t_show_category ? '<label for="category_id">' . \Core\Lang::get( 'category' ) . '</label>' : '', '</td>';
	echo '<td width="20%" class="category">', $t_show_view_state ? '<label for="view_state">' . \Core\Lang::get( 'view_status' ) . '</label>' : '', '</td>';
	echo '<td width="15%" class="category">', $t_show_date_submitted ? \Core\Lang::get( 'date_submitted' ) : '', '</td>';
	echo '<td width="15%" class="category">', $t_show_last_updated ? \Core\Lang::get( 'last_update' ) : '', '</td>';
	echo '</tr>';

	#
	# Values for Bug Id, Project Name, Category, View State, Date Submitted, Last Updated
	#

	echo '<tr>';

	# Bug ID
	echo '<td>', $t_formatted_bug_id, '</td>';

	# Project Name
	echo '<td>', $t_project_name, '</td>';

	# Category
	echo '<td>';

	if( $t_show_category ) {
		echo '<select ' . \Core\Helper::get_tab_index() . ' id="category_id" name="category_id">';
		\Core\Print_Util::category_option_list( $t_bug->category_id, $t_bug->project_id );
		echo '</select>';
	}

	echo '</td>';

	# View State
	echo '<td>';

	if( $t_can_change_view_state ) {
		echo '<select ' . \Core\Helper::get_tab_index() . ' id="view_state" name="view_state">';
		\Core\Print_Util::enum_string_option_list( 'view_state', (int)$t_bug->view_state );
		echo '</select>';
	} else if( $t_show_view_state ) {
		echo $t_view_state;
	}

	echo '</td>';

	# Date Submitted
	echo '<td>', $t_show_date_submitted ? date( \Core\Config::mantis_get( 'normal_date_format' ), $t_bug->date_submitted ) : '', '</td>';

	# Date Updated
	echo '<td>', $t_show_last_updated ? date( \Core\Config::mantis_get( 'normal_date_format' ), $t_bug->last_updated ) : '', '</td>';

	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

#
# Reporter
#

if( $t_show_reporter ) {
	echo '<tr>';

	$t_spacer = 4;

	if( $t_show_reporter ) {
		# Reporter
		echo '<th class="category"><label for="reporter_id">' . \Core\Lang::get( 'reporter' ) . '</label></th>';
		echo '<td>';

		# Do not allow the bug's reporter to edit the Reporter field
		# when limit_reporters is ON
		if( ON == \Core\Config::mantis_get( 'limit_reporters' )
		&&  !\Core\Access::has_project_level( \Core\Config::mantis_get( 'report_bug_threshold', null, null, $t_bug->project_id ) + 1, $t_bug->project_id )
		) {
			echo \Core\String::attribute( \Core\User::get_name( $t_bug->reporter_id ) );
		} else {
			if ( $f_reporter_edit ) {
				echo '<select ' . \Core\Helper::get_tab_index() . ' id="reporter_id" name="reporter_id">';
				\Core\Print_Util::reporter_option_list( $t_bug->reporter_id, $t_bug->project_id );
				echo '</select>';
			} else {
				echo \Core\String::attribute( \Core\User::get_name( $t_bug->reporter_id ) );
				echo ' [<a href="#reporter_edit" class="click-url" url="' . \Core\String::get_bug_update_url( $f_bug_id ) . '&amp;reporter_edit=true">' . \Core\Lang::get( 'edit_link' ) . '</a>]';
			}
		}
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Assigned To, Due Date
#

if( $t_show_handler || $t_show_due_date ) {
	echo '<tr>';

	$t_spacer = 2;

	# Assigned To
	echo '<th class="category"><label for="handler_id">' . \Core\Lang::get( 'assigned_to' ) . '</label></th>';
	echo '<td>';

	if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'update_bug_assign_threshold', \Core\Config::mantis_get( 'update_bug_threshold' ) ) ) ) {
		echo '<select ' . \Core\Helper::get_tab_index() . ' id="handler_id" name="handler_id">';
		echo '<option value="0"></option>';
		\Core\Print_Util::assign_to_option_list( $t_bug->handler_id, $t_bug->project_id );
		echo '</select>';
	} else {
		echo $t_handler_name;
	}

	echo '</td>';

	if( $t_show_due_date ) {
		# Due Date
		echo '<th class="category"><label for="due_date">' . \Core\Lang::get( 'due_date' ) . '</label></th>';

		if( \Core\Bug::is_overdue( $t_bug_id ) ) {
			echo '<td class="overdue">';
		} else {
			echo '<td>';
		}

		if( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'due_date_update_threshold' ), $t_bug_id ) ) {
			$t_date_to_display = '';

			if( !\Core\Date::is_null( $t_bug->due_date ) ) {
				$t_date_to_display = date( \Core\Config::mantis_get( 'calendar_date_format' ), $t_bug->due_date );
			}
			echo '<input ' . \Core\Helper::get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetime" size="20" maxlength="16" value="' . $t_date_to_display . '" />';
		} else {
			if( !\Core\Date::is_null( $t_bug->due_date ) ) {
				echo date( \Core\Config::mantis_get( 'short_date_format' ), $t_bug->due_date );
			}
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Priority, Severity, Reproducibility
#

if( $t_show_priority || $t_show_severity || $t_show_reproducibility ) {
	echo '<tr>';

	$t_spacer = 0;

	if( $t_show_priority ) {
		# Priority
		echo '<th class="category"><label for="priority">' . \Core\Lang::get( 'priority' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="priority" name="priority">';
		\Core\Print_Util::enum_string_option_list( 'priority', $t_bug->priority );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_severity ) {
		# Severity
		echo '<th class="category"><label for="severity">' . \Core\Lang::get( 'severity' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="severity" name="severity">';
		\Core\Print_Util::enum_string_option_list( 'severity', $t_bug->severity );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_reproducibility ) {
		# Reproducibility
		echo '<th class="category"><label for="reproducibility">' . \Core\Lang::get( 'reproducibility' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="reproducibility" name="reproducibility">';
		\Core\Print_Util::enum_string_option_list( 'reproducibility', $t_bug->reproducibility );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Status, Resolution
#

if( $t_show_status || $t_show_resolution ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_show_status ) {
		# Status
		echo '<th class="category"><label for="status">' . \Core\Lang::get( 'status' ) . '</label></th>';

		# choose color based on status
		$t_status_label = \Core\HTML::get_status_css_class( $t_bug->status );

		echo '<td class="' . $t_status_label .  '">';
		\Core\Print_Util::status_option_list( 'status', $t_bug->status,
			\Core\Access::can_close_bug( $t_bug ),
			$t_bug->project_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_resolution ) {
		# Resolution
		echo '<th class="category"><label for="resolution">' . \Core\Lang::get( 'resolution' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="resolution" name="resolution">';
		\Core\Print_Util::enum_string_option_list( 'resolution', $t_bug->resolution );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Projection, ETA
#

if( $t_show_projection || $t_show_eta ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_show_projection ) {
		# Projection
		echo '<th class="category"><label for="projection">' . \Core\Lang::get( 'projection' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="projection" name="projection">';
		\Core\Print_Util::enum_string_option_list( 'projection', $t_bug->projection );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# ETA
	if( $t_show_eta ) {
		echo '<th class="category"><label for="eta">' . \Core\Lang::get( 'eta' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="eta" name="eta">';
		\Core\Print_Util::enum_string_option_list( 'eta', (int)$t_bug->eta );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Platform, OS, OS Version
#

if( $t_show_platform || $t_show_os || $t_show_os_version ) {
	echo '<tr>';

	$t_spacer = 0;

	if( $t_show_platform ) {
		# Platform
		echo '<th class="category"><label for="platform">' . \Core\Lang::get( 'platform' ) . '</label></th>';
		echo '<td>';

		if( \Core\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . \Core\Helper::get_tab_index() . ' id="platform" name="platform"><option value=""></option>';
			\Core\Print_Util::platform_option_list( $t_bug->platform );
			echo '</select>';
		} else {
			echo '<input type="text" id="platform" name="platform" class="autocomplete" size="16" maxlength="32" tabindex="' . \Core\Helper::get_tab_index_value() . '" value="' . \Core\String::attribute( $t_bug->platform ) . '" />';
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_os ) {
		# Operating System
		echo '<th class="category"><label for="os">' . \Core\Lang::get( 'os' ) . '</label></th>';
		echo '<td>';

		if( \Core\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . \Core\Helper::get_tab_index() . ' id="os" name="os"><option value=""></option>';
			\Core\Print_Util::os_option_list( $t_bug->os );
			echo '</select>';
		} else {
			echo '<input type="text" id="os" name="os" class="autocomplete" size="16" maxlength="32" tabindex="' . \Core\Helper::get_tab_index_value() . '" value="' . \Core\String::attribute( $t_bug->os ) . '" />';
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_os_version ) {
		# OS Version
		echo '<th class="category"><label for="os_build">' . \Core\Lang::get( 'os_version' ) . '</label></th>';
		echo '<td>';

		if( \Core\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . \Core\Helper::get_tab_index() . ' id="os_build" name="os_build"><option value=""></option>';
			\Core\Print_Util::os_build_option_list( $t_bug->os_build );
			echo '</select>';
		} else {
			echo '<input type="text" id="os_build" name="os_build" class="autocomplete" size="16" maxlength="16" tabindex="' . \Core\Helper::get_tab_index_value() . '" value="' . \Core\String::attribute( $t_bug->os_build ) . '" />';
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Product Version, Product Build
#

if( $t_show_product_version || $t_show_product_build ) {
	echo '<tr>';

	$t_spacer = 2;

	# Product Version  or Product Build, if version is suppressed
	if( $t_show_product_version ) {
		echo '<th class="category"><label for="version">' . \Core\Lang::get( 'product_version' ) . '</label></th>';
		echo '<td>', '<select ', \Core\Helper::get_tab_index(), ' id="version" name="version">';
		\Core\Print_Util::version_option_list( $t_bug->version, $t_bug->project_id, $t_product_version_released_mask );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_product_build ) {
		echo '<th class="category"><label for="build">' . \Core\Lang::get( 'product_build' ) . '</label></th>';
		echo '<td>';
		echo '<input type="text" id="build" name="build" size="16" maxlength="32" ' . \Core\Helper::get_tab_index() . ' value="' . $t_product_build_attribute . '" />';
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Target Version, Fixed in Version
#

if( $t_show_target_version || $t_show_fixed_in_version ) {
	echo '<tr>';

	$t_spacer = 2;

	# Target Version
	if( $t_show_target_version ) {
		echo '<th class="category"><label for="target_version">' . \Core\Lang::get( 'target_version' ) . '</label></th>';
		echo '<td><select ' . \Core\Helper::get_tab_index() . ' id="target_version" name="target_version">';
		\Core\Print_Util::version_option_list( $t_bug->target_version, $t_bug->project_id, VERSION_FUTURE );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# Fixed in Version
	if( $t_show_fixed_in_version ) {
		echo '<th class="category"><label for="fixed_in_version">' . \Core\Lang::get( 'fixed_in_version' ) . '</label></th>';
		echo '<td>';
		echo '<select ' . \Core\Helper::get_tab_index() . ' id="fixed_in_version" name="fixed_in_version">';
		\Core\Print_Util::version_option_list( $t_bug->fixed_in_version, $t_bug->project_id, VERSION_ALL );
		echo '</select>';
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

\Core\Event::signal( 'EVENT_UPDATE_BUG_FORM', array( $t_bug_id, true ) );

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Summary
if( $t_show_summary ) {
	echo '<tr>';
	echo '<th class="category"><label for="summary">' . \Core\Lang::get( 'summary' ) . '</label></th>';
	echo '<td colspan="5">', '<input ', \Core\Helper::get_tab_index(), ' type="text" id="summary" name="summary" size="105" maxlength="128" value="', $t_summary_attribute, '" />';
	echo '</td></tr>';
}

# Description
if( $t_show_description ) {
	echo '<tr>';
	echo '<th class="category"><label for="description">' . \Core\Lang::get( 'description' ) . '</label></th>';
	echo '<td colspan="5">';
	echo '<textarea ', \Core\Helper::get_tab_index(), ' cols="80" rows="10" id="description" name="description">', $t_description_textarea, '</textarea>';
	echo '</td></tr>';
}

# Steps to Reproduce
if( $t_show_steps_to_reproduce ) {
	echo '<tr>';
	echo '<th class="category"><label for="steps_to_reproduce">' . \Core\Lang::get( 'steps_to_reproduce' ) . '</label></th>';
	echo '<td colspan="5">';
	echo '<textarea ', \Core\Helper::get_tab_index(), ' cols="80" rows="10" id="steps_to_reproduce" name="steps_to_reproduce">', $t_steps_to_reproduce_textarea, '</textarea>';
	echo '</td></tr>';
}

# Additional Information
if( $t_show_additional_information ) {
	echo '<tr>';
	echo '<th class="category"><label for="additional_information">' . \Core\Lang::get( 'additional_information' ) . '</label></th>';
	echo '<td colspan="5">';
	echo '<textarea ', \Core\Helper::get_tab_index(), ' cols="80" rows="10" id="additional_information" name="additional_information">', $t_additional_information_textarea, '</textarea>';
	echo '</td></tr>';
}

echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Custom Fields
$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

foreach ( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	if( ( $t_def['display_update'] || $t_def['require_update'] ) && custom_field_has_write_access( $t_id, $t_bug_id ) ) {
		$t_custom_fields_found = true;

		echo '<tr>';
		echo '<td class="category">';
		if( $t_def['require_update'] ) {
			echo '<span class="required">*</span>';
		}
		if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) {
			echo '<label for="custom_field_' . \Core\String::attribute( $t_def['id'] ) . '">' . \Core\String::display( \Core\Lang::get_defaulted( $t_def['name'] ) ) . '</label>';
		} else {
			echo \Core\String::display( \Core\Lang::get_defaulted( $t_def['name'] ) );
		}
		echo '</td><td colspan="5">';
		print_custom_field_input( $t_def, $t_bug_id );
		echo '</td></tr>';
	}
} # foreach( $t_related_custom_field_ids as $t_id )

if( $t_custom_fields_found ) {
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

# Bugnote Text Box
echo '<tr>';
echo '<th class="category"><label for="bugnote_text">' . \Core\Lang::get( 'add_bugnote_title' ) . '</label></th>';
echo '<td colspan="5"><textarea ', \Core\Helper::get_tab_index(), ' id="bugnote_text" name="bugnote_text" cols="80" rows="10"></textarea></td></tr>';

# Bugnote Private Checkbox (if permitted)
if( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'private_bugnote_threshold' ), $t_bug_id ) ) {
	echo '<tr>';
	echo '<th class="category">' . \Core\Lang::get( 'private' ) . '</th>';
	echo '<td colspan="5">';

	$t_default_bugnote_view_status = \Core\Config::mantis_get( 'default_bugnote_view_status' );
	if( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'set_view_status_threshold' ), $t_bug_id ) ) {
		echo '<input ', \Core\Helper::get_tab_index(), ' type="checkbox" id="private" name="private" ', \Core\Helper::check_checked( \Core\Config::mantis_get( 'default_bugnote_view_status' ), VS_PRIVATE ), ' />';
		echo \Core\Lang::get( 'private' );
	} else {
		echo \Core\Helper::get_enum_element( 'view_state', $t_default_bugnote_view_status );
	}

	echo '</td></tr>';
}

# Time Tracking (if permitted)
if( \Core\Config::mantis_get( 'time_tracking_enabled' ) ) {
	if( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) {
		echo '<tr>';
		echo '<th class="category"><label for="time_tracking">' . \Core\Lang::get( 'time_tracking' ) . '</label></th>';
		echo '<td colspan="5"><input type="text" id="time_tracking" name="time_tracking" size="5" placeholder="hh:mm" /></td></tr>';
	}
}

\Core\Event::signal( 'EVENT_BUGNOTE_ADD_FORM', array( $t_bug_id ) );

# Submit Button
if( $t_bottom_buttons_enabled ) {
?>
			<tfoot>
				<tr>
					<td class="center" colspan="6">
						<input <?php \Core\Helper::get_tab_index(); ?>
							type="submit" class="button"
							value="<?php echo \Core\Lang::get( 'update_information_button' ); ?>" />
					</td>
				</tr>
			</tfoot>
<?php
}
?>

		</table>
	</form>
</div>

<?php
define( 'BUGNOTE_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
\Core\HTML::page_bottom();

\Core\Last_Visited::issue( $t_bug_id );
