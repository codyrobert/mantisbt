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
 * Handling of Bug Status change
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once( 'core.php' );
require_api( 'custom_field_api.php' );

$f_bug_id = \Flickerbox\GPC::get_int( 'id' );
$t_bug = \Flickerbox\Bug::get( $f_bug_id );

$t_file = __FILE__;
$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$t_show_page_header = false;
$t_force_readonly = true;
$t_fields_config_option = 'bug_change_status_page_fields';

if( $t_bug->project_id != \Flickerbox\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

$f_new_status = \Flickerbox\GPC::get_int( 'new_status' );
$f_reopen_flag = \Flickerbox\GPC::get_int( 'reopen_flag', OFF );

$t_reopen = \Flickerbox\Config::mantis_get( 'bug_reopen_status', null, null, $t_bug->project_id );
$t_resolved = \Flickerbox\Config::mantis_get( 'bug_resolved_status_threshold', null, null, $t_bug->project_id );
$t_closed = \Flickerbox\Config::mantis_get( 'bug_closed_status_threshold', null, null, $t_bug->project_id );
$t_current_user_id = \Flickerbox\Auth::get_current_user_id();

# Ensure user has proper access level before proceeding
if( $f_new_status == $t_reopen && $f_reopen_flag ) {
	\Flickerbox\Access::ensure_can_reopen_bug( $t_bug, $t_current_user_id );
} else if( $f_new_status == $t_closed ) {
	\Flickerbox\Access::ensure_can_close_bug( $t_bug, $t_current_user_id );
} else if( \Flickerbox\Bug::is_readonly( $f_bug_id )
	|| !\Flickerbox\Access::has_bug_level( \Flickerbox\Access::get_status_threshold( $f_new_status, $t_bug->project_id ), $f_bug_id, $t_current_user_id ) ) {
	\Flickerbox\Access::denied();
}

$t_can_update_due_date = \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'due_date_update_threshold' ), $f_bug_id );
if( $t_can_update_due_date ) {
	\Flickerbox\HTML::require_js( 'jscalendar/calendar.js' );
	\Flickerbox\HTML::require_js( 'jscalendar/lang/calendar-en.js' );
	\Flickerbox\HTML::require_js( 'jscalendar/calendar-setup.js' );
	\Flickerbox\HTML::require_css( 'calendar-blue.css' );
}

# get new issue handler if set, otherwise default to original handler
$f_handler_id = \Flickerbox\GPC::get_int( 'handler_id', $t_bug->handler_id );

if( \Flickerbox\Config::mantis_get( 'bug_assigned_status' ) == $f_new_status ) {
	$t_bug_sponsored = \Flickerbox\Sponsorship::get_amount( \Flickerbox\Sponsorship::get_all_ids( $f_bug_id ) ) > 0;
	if( $t_bug_sponsored ) {
		if( !\Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'assign_sponsored_bugs_threshold' ), $f_bug_id ) ) {
			trigger_error( ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW, ERROR );
		}
	}

	if( $f_handler_id != NO_USER ) {
		if( !\Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'handle_bug_threshold' ), $f_bug_id, $f_handler_id ) ) {
			trigger_error( ERROR_HANDLER_ACCESS_TOO_LOW, ERROR );
		}

		if( $t_bug_sponsored ) {
			if( !\Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'handle_sponsored_bugs_threshold' ), $f_bug_id, $f_handler_id ) ) {
				trigger_error( ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW, ERROR );
			}
		}
	}
}

$t_status_label = str_replace( ' ', '_', \Flickerbox\MantisEnum::getLabel( \Flickerbox\Config::mantis_get( 'status_enum_string' ), $f_new_status ) );

\Flickerbox\HTML::page_top( \Flickerbox\Bug::format_summary( $f_bug_id, SUMMARY_CAPTION ) );

\Flickerbox\Print_Util::recently_visited();
?>

<br />
<div id="bug-change-status-div" class="form-container">

<form id="bug-change-status-form" name="bug_change_status_form" method="post" action="bug_update.php">

	<?php echo \Flickerbox\Form::security_field( 'bug_update' ) ?>
	<table>
		<thead>
			<!-- Title -->
			<tr>
				<td class="form-title" colspan="2">
					<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
					<input type="hidden" name="status" value="<?php echo $f_new_status ?>" />
					<input type="hidden" name="last_updated" value="<?php echo $t_bug->last_updated ?>" />
					<?php echo \Flickerbox\Lang::get( $t_status_label . '_bug_title' ) ?>
				</td>
			</tr>
<?php
	if( $f_new_status >= $t_resolved ) {
		if( \Flickerbox\Relationship::can_resolve_bug( $f_bug_id ) == false ) {
			echo '<tr><td colspan="2">' . \Flickerbox\Lang::get( 'relationship_warning_blocking_bugs_not_resolved_2' ) . '</td></tr>';
		}
	}
?>
		</thead>
		<tbody>
<?php
$t_current_resolution = $t_bug->resolution;
$t_bug_is_open = $t_current_resolution < $t_resolved;
if( ( $f_new_status >= $t_resolved ) && ( ( $f_new_status < $t_closed ) || ( $t_bug_is_open ) ) ) { ?>
<!-- Resolution -->
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'resolution' ) ?>
				</th>
				<td>
					<select name="resolution">
			<?php
				$t_resolution = $t_bug_is_open ? \Flickerbox\Config::mantis_get( 'bug_resolution_fixed_threshold' ) : $t_current_resolution;

				$t_relationships = \Flickerbox\Relationship::get_all_src( $f_bug_id );
				foreach( $t_relationships as $t_relationship ) {
					if( $t_relationship->type == BUG_DUPLICATE ) {
						$t_resolution = \Flickerbox\Config::mantis_get( 'bug_duplicate_resolution' );
						break;
					}
				}

				\Flickerbox\Print_Util::enum_string_option_list( 'resolution', $t_resolution );
			?>
					</select>
				</td>
			</tr>
<?php } ?>

<?php
if( $f_new_status >= $t_resolved
	&& $f_new_status < $t_closed
	&& $t_resolution != \Flickerbox\Config::mantis_get( 'bug_duplicate_resolution' ) ) { ?>
<!-- Duplicate ID -->
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'duplicate_id' ) ?>
				</th>
				<td>
					<input type="text" name="duplicate_id" maxlength="10" />
				</td>
			</tr>
<?php } ?>

<?php
if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_assign_threshold', \Flickerbox\Config::mantis_get( 'update_bug_threshold' ) ), $f_bug_id ) ) {
	$t_suggested_handler_id = $t_bug->handler_id;

	if( $t_suggested_handler_id == NO_USER && \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'handle_bug_threshold' ), $f_bug_id ) ) {
		$t_suggested_handler_id = $t_current_user_id;
	}
?>
<!-- Assigned To -->
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'assigned_to' ) ?>
				</th>
				<td>
					<select name="handler_id">
						<option value="0"></option>
						<?php \Flickerbox\Print_Util::assign_to_option_list( $t_suggested_handler_id, $t_bug->project_id ) ?>
					</select>
				</td>
			</tr>
<?php } ?>

<?php if( $t_can_update_due_date ) {
	$t_date_to_display = '';
	if( !\Flickerbox\Date::is_null( $t_bug->due_date ) ) {
		$t_date_to_display = date( \Flickerbox\Config::mantis_get( 'calendar_date_format' ), $t_bug->due_date );
	}
?>
<!-- Due date -->
			<tr>
				<th class="category">
					<?php \Flickerbox\Print_Util::documentation_link( 'due_date' ) ?>
				</th>
				<td>
					<?php echo '<input ' . \Flickerbox\Helper::get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetime" size="20" maxlength="16" value="' . $t_date_to_display . '" />' ?>
				</td>
			</tr>
<?php } ?>

<!-- Custom Fields -->
<?php
/** @todo thraxisp - I undid part of the change for #5068 for #5527
 * We really need to say what fields are shown in which statusses. For now,
 * this page will show required custom fields in update mode, or
 *  display or required fields on resolve or close
 */
$t_custom_status_label = 'update'; # Don't show custom fields by default
if( ( $f_new_status == $t_resolved ) && ( $f_new_status < $t_closed ) ) {
	$t_custom_status_label = 'resolved';
}
if( $t_closed == $f_new_status ) {
	$t_custom_status_label = 'closed';
}

$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	$t_display = $t_def['display_' . $t_custom_status_label];
	$t_require = $t_def['require_' . $t_custom_status_label];

	if( ( 'update' == $t_custom_status_label ) && ( !$t_require ) ) {
		continue;
	}
	if( in_array( $t_custom_status_label, array( 'resolved', 'closed' ) ) && !( $t_display || $t_require ) ) {
		continue;
	}
	if( custom_field_has_write_access( $t_id, $f_bug_id ) ) {
?>
			<tr>
				<th class="category">
					<?php if( $t_require ) { ?>
						<span class="required">*</span>
					<?php }
					echo \Flickerbox\Lang::get_defaulted( $t_def['name'] )
					?>
				</th>
				<td>
					<?php
						print_custom_field_input( $t_def, $f_bug_id );
					?>
				</td>
			</tr>
<?php
	} else if( custom_field_has_read_access( $t_id, $f_bug_id ) ) {
		#  custom_field_has_write_access( $t_id, $f_bug_id ) )
?>
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get_defaulted( $t_def['name'] ) ?>
				</th>
				<td>
					<?php print_custom_field_value( $t_def, $t_id, $f_bug_id );			?>
				</td>
			</tr>
<?php
	} # custom_field_has_read_access( $t_id, $f_bug_id ) )
} # foreach( $t_related_custom_field_ids as $t_id )
?>

<?php
if( ( $f_new_status >= $t_resolved ) ) {
	if( \Flickerbox\Version::should_show_product_version( $t_bug->project_id )
		&& !\Flickerbox\Bug::is_readonly( $f_bug_id )
		&& \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'update_bug_threshold' ), $f_bug_id )
	) {
?>
			<!-- Fixed in Version -->
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'fixed_in_version' ) ?>
				</th>
				<td>
					<select name="fixed_in_version">
						<?php \Flickerbox\Print_Util::version_option_list( $t_bug->fixed_in_version, $t_bug->project_id, VERSION_ALL ) ?>
					</select>
				</td>
			</tr>
<?php
	}
}
?>
<?php \Flickerbox\Event::signal( 'EVENT_UPDATE_BUG_STATUS_FORM', array( $f_bug_id ) ); ?>
<?php if( ON == $f_reopen_flag ) { ?>
<!-- Bug was re-opened -->
<?php
	printf( '	<input type="hidden" name="resolution" value="%s" />' . "\n", \Flickerbox\Config::mantis_get( 'bug_reopen_resolution' ) );
}
?>
			<!-- Bugnote -->
			<tr id="bug-change-status-note">
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'add_bugnote_title' ) ?>
				</th>
				<td>
					<textarea name="bugnote_text" cols="80" rows="10"></textarea>
				</td>
			</tr>
<?php if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'view_status' ) ?>
				</th>
				<td>
<?php
		$t_default_bugnote_view_status = \Flickerbox\Config::mantis_get( 'default_bugnote_view_status' );
		if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
			<input type="checkbox" name="private" <?php \Flickerbox\Helper::check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
<?php
			echo \Flickerbox\Lang::get( 'private' );
		} else {
			echo \Flickerbox\Helper::get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
		}
?>
				</td>
			</tr>
<?php } ?>

<?php if( \Flickerbox\Config::mantis_get( 'time_tracking_enabled' ) ) { ?>
<?php 	if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
<?php 		if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'time_tracking_edit_threshold' ), $f_bug_id ) ) { ?>
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'time_tracking' ) ?>
				</th>
				<td>
					<input type="text" name="time_tracking" size="5" placeholder="hh:mm" />
				</td>
			</tr>
<?php 		} ?>
<?php 	} ?>
<?php } ?>

<?php \Flickerbox\Event::signal( 'EVENT_BUGNOTE_ADD_FORM', array( $f_bug_id ) ); ?>

			<!-- Submit Button -->
			<tr>
				<td class="center" colspan="2">
					<input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( $t_status_label . '_bug_button' ) ?>" />
				</td>
			</tr>
		</tbody>
	</table>
</form>

</div>
<br />
<?php
define( 'BUG_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/bug_view_inc.php' );
