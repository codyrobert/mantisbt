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
 * Update bug data then redirect to the appropriate viewing page
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
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 */

require_once( 'core.php' );
require_api( 'custom_field_api.php' );

\Core\Form::security_validate( 'bug_update' );

$f_bug_id = \Core\GPC::get_int( 'bug_id' );
$t_existing_bug = \Core\Bug::get( $f_bug_id, true );
$f_update_type = \Core\GPC::get_string( 'action_type', BUG_UPDATE_TYPE_NORMAL );

if( \Core\Helper::get_current_project() !== $t_existing_bug->project_id ) {
	$g_project_override = $t_existing_bug->project_id;
}

# Ensure that the user has permission to update bugs. This check also factors
# in whether the user has permission to view private bugs. The
# $g_limit_reporters option is also taken into consideration.
\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'update_bug_threshold' ), $f_bug_id );

# Check if the bug is in a read-only state and whether the current user has
# permission to update read-only bugs.
if( \Core\Bug::is_readonly( $f_bug_id ) ) {
	\Core\Error::parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

$t_updated_bug = clone $t_existing_bug;

$t_updated_bug->additional_information = \Core\GPC::get_string( 'additional_information', $t_existing_bug->additional_information );
$t_updated_bug->build = \Core\GPC::get_string( 'build', $t_existing_bug->build );
$t_updated_bug->category_id = \Core\GPC::get_int( 'category_id', $t_existing_bug->category_id );
$t_updated_bug->description = \Core\GPC::get_string( 'description', $t_existing_bug->description );
$t_due_date = \Core\GPC::get_string( 'due_date', null );
if( $t_due_date !== null ) {
	if( \Core\Utility::is_blank( $t_due_date ) ) {
		$t_updated_bug->due_date = 1;
	} else {
		$t_updated_bug->due_date = strtotime( $t_due_date );
	}
}
$t_updated_bug->duplicate_id = \Core\GPC::get_int( 'duplicate_id', 0 );
$t_updated_bug->eta = \Core\GPC::get_int( 'eta', $t_existing_bug->eta );
$t_updated_bug->fixed_in_version = \Core\GPC::get_string( 'fixed_in_version', $t_existing_bug->fixed_in_version );
$t_updated_bug->handler_id = \Core\GPC::get_int( 'handler_id', $t_existing_bug->handler_id );
$t_updated_bug->last_updated = \Core\GPC::get_string( 'last_updated' );
$t_updated_bug->os = \Core\GPC::get_string( 'os', $t_existing_bug->os );
$t_updated_bug->os_build = \Core\GPC::get_string( 'os_build', $t_existing_bug->os_build );
$t_updated_bug->platform = \Core\GPC::get_string( 'platform', $t_existing_bug->platform );
$t_updated_bug->priority = \Core\GPC::get_int( 'priority', $t_existing_bug->priority );
$t_updated_bug->projection = \Core\GPC::get_int( 'projection', $t_existing_bug->projection );
$t_updated_bug->reporter_id = \Core\GPC::get_int( 'reporter_id', $t_existing_bug->reporter_id );
$t_updated_bug->reproducibility = \Core\GPC::get_int( 'reproducibility', $t_existing_bug->reproducibility );
$t_updated_bug->resolution = \Core\GPC::get_int( 'resolution', $t_existing_bug->resolution );
$t_updated_bug->severity = \Core\GPC::get_int( 'severity', $t_existing_bug->severity );
$t_updated_bug->status = \Core\GPC::get_int( 'status', $t_existing_bug->status );
$t_updated_bug->steps_to_reproduce = \Core\GPC::get_string( 'steps_to_reproduce', $t_existing_bug->steps_to_reproduce );
$t_updated_bug->summary = \Core\GPC::get_string( 'summary', $t_existing_bug->summary );
$t_updated_bug->target_version = \Core\GPC::get_string( 'target_version', $t_existing_bug->target_version );
$t_updated_bug->version = \Core\GPC::get_string( 'version', $t_existing_bug->version );
$t_updated_bug->view_state = \Core\GPC::get_int( 'view_state', $t_existing_bug->view_state );

$t_bug_note = new BugNoteData();
$t_bug_note->note = \Core\GPC::get_string( 'bugnote_text', '' );
$t_bug_note->view_state = \Core\GPC::get_bool( 'private', \Core\Config::mantis_get( 'default_bugnote_view_status' ) == VS_PRIVATE ) ? VS_PRIVATE : VS_PUBLIC;
$t_bug_note->time_tracking = \Core\GPC::get_string( 'time_tracking', '0:00' );

if( $t_existing_bug->last_updated !== $t_updated_bug->last_updated ) {
	trigger_error( ERROR_BUG_CONFLICTING_EDIT, ERROR );
}

# Determine whether the new status will reopen, resolve or close the issue.
# Note that multiple resolved or closed states can exist and thus we need to
# look at a range of statuses when performing this check.
$t_resolved_status = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
$t_closed_status = \Core\Config::mantis_get( 'bug_closed_status_threshold' );
$t_reopen_resolution = \Core\Config::mantis_get( 'bug_reopen_resolution' );
$t_resolve_issue = false;
$t_close_issue = false;
$t_reopen_issue = false;
if( $t_existing_bug->status < $t_resolved_status &&
	 $t_updated_bug->status >= $t_resolved_status &&
	 $t_updated_bug->status < $t_closed_status ) {
	$t_resolve_issue = true;
} else if( $t_existing_bug->status < $t_closed_status &&
			$t_updated_bug->status >= $t_closed_status ) {
	$t_close_issue = true;
} else if( $t_existing_bug->status >= $t_resolved_status &&
			$t_updated_bug->status <= \Core\Config::mantis_get( 'bug_reopen_status' ) ) {
	$t_reopen_issue = true;
}

# If resolving or closing, ensure that all dependant issues have been resolved.
if( ( $t_resolve_issue || $t_close_issue ) &&
	 !\Core\Relationship::can_resolve_bug( $f_bug_id ) ) {
	trigger_error( ERROR_BUG_RESOLVE_DEPENDANTS_BLOCKING, ERROR );
}

# Validate any change to the status of the issue.
if( $t_existing_bug->status !== $t_updated_bug->status ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'update_bug_status_threshold' ), $f_bug_id );
	if( !\Core\Bug::check_workflow( $t_existing_bug->status, $t_updated_bug->status ) ) {
		\Core\Error::parameters( \Core\Lang::get( 'status' ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
	}
	if( !\Core\Access::has_bug_level( \Core\Access::get_status_threshold( $t_updated_bug->status, $t_updated_bug->project_id ), $f_bug_id ) ) {
		# The reporter may be allowed to close or reopen the issue regardless.
		$t_can_bypass_status_access_thresholds = false;
		if( $t_close_issue &&
		     $t_existing_bug->status >= $t_resolved_status &&
		     $t_existing_bug->reporter_id === \Core\Auth::get_current_user_id() &&
		     \Core\Config::mantis_get( 'allow_reporter_close' ) ) {
			$t_can_bypass_status_access_thresholds = true;
		} else if( $t_reopen_issue &&
		            $t_existing_bug->status < $t_closed_status &&
		            $t_existing_bug->reporter_id === \Core\Auth::get_current_user_id() &&
		            \Core\Config::mantis_get( 'allow_reporter_reopen' ) ) {
			$t_can_bypass_status_access_thresholds = true;
		}
		if( !$t_can_bypass_status_access_thresholds ) {
			trigger_error( ERROR_ACCESS_DENIED, ERROR );
		}
	}
	if( $t_reopen_issue ) {
		# for everyone allowed to reopen an issue, set the reopen resolution
		$t_updated_bug->resolution = $t_reopen_resolution;
	}
}

# Validate any change to the handler of an issue.
$t_issue_is_sponsored = \Core\Sponsorship::get_amount( \Core\Sponsorship::get_all_ids( $f_bug_id ) ) > 0;
if( $t_existing_bug->handler_id !== $t_updated_bug->handler_id ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'update_bug_assign_threshold' ), $f_bug_id );
	if( $t_issue_is_sponsored && !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'handle_sponsored_bugs_threshold' ), $f_bug_id ) ) {
		trigger_error( ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW, ERROR );
	}
	if( $t_updated_bug->handler_id !== NO_USER ) {
		if( !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'handle_bug_threshold' ), $f_bug_id, $t_updated_bug->handler_id ) ) {
			trigger_error( ERROR_HANDLER_ACCESS_TOO_LOW, ERROR );
		}
		if( $t_issue_is_sponsored && !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'assign_sponsored_bugs_threshold' ), $f_bug_id ) ) {
			trigger_error( ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW, ERROR );
		}
	}
}

# Check whether the category has been undefined when it's compulsory.
if( $t_existing_bug->category_id !== $t_updated_bug->category_id ) {
	if( $t_updated_bug->category_id === 0 &&
	     !\Core\Config::mantis_get( 'allow_no_category' ) ) {
		\Core\Error::parameters( \Core\Lang::get( 'category' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}
}

# Don't allow changing the Resolution in the following cases:
# - new status < RESOLVED and resolution denoting completion (>= fixed_threshold)
# - new status >= RESOLVED and resolution < fixed_threshold
# - resolution = REOPENED and current status < RESOLVED and new status >= RESOLVED
# Refer to #15653 for further details (particularly note 37180)
$t_resolution_fixed_threshold = \Core\Config::mantis_get( 'bug_resolution_fixed_threshold' );
if( $t_existing_bug->resolution != $t_updated_bug->resolution && (
	   (  $t_updated_bug->resolution >= $t_resolution_fixed_threshold
	   && $t_updated_bug->resolution != $t_reopen_resolution
	   && $t_updated_bug->status < $t_resolved_status
	   )
	|| (  $t_updated_bug->resolution == $t_reopen_resolution
	   && (  $t_existing_bug->status < $t_resolved_status
	      || $t_updated_bug->status >= $t_resolved_status
	   ) )
	|| (  $t_updated_bug->resolution < $t_resolution_fixed_threshold
	   && $t_updated_bug->status >= $t_resolved_status
	   )
) ) {
	\Core\Error::parameters(
		\Core\Helper::get_enum_element( 'resolution', $t_updated_bug->resolution ),
		\Core\Helper::get_enum_element( 'status', $t_updated_bug->status )
	);
	trigger_error( ERROR_INVALID_RESOLUTION, ERROR );
}

# Ensure that the user has permission to change the target version of the issue.
if( $t_existing_bug->target_version !== $t_updated_bug->target_version ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'roadmap_update_threshold' ), $f_bug_id );
}

# Ensure that the user has permission to change the view status of the issue.
if( $t_existing_bug->view_state !== $t_updated_bug->view_state ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'change_view_status_threshold' ), $f_bug_id );
}

# Determine the custom field "require check" to use for validating
# whether fields can be undefined during this bug update.
if( $t_close_issue ) {
	$t_cf_require_check = 'require_closed';
} else if( $t_resolve_issue ) {
	$t_cf_require_check = 'require_resolved';
} else {
	$t_cf_require_check = 'require_update';
}

$t_related_custom_field_ids = custom_field_get_linked_ids( $t_existing_bug->project_id );
$t_custom_fields_to_set = array();
foreach ( $t_related_custom_field_ids as $t_cf_id ) {
	$t_cf_def = custom_field_get_definition( $t_cf_id );

	if( !\Core\GPC::isset_custom_field( $t_cf_id, $t_cf_def['type'] ) ) {
		if( $t_cf_def[$t_cf_require_check] && $f_update_type == BUG_UPDATE_TYPE_NORMAL ) {
			# A value for the custom field was expected however
			# no value was given by the user.
			\Core\Error::parameters( \Core\Lang::get_defaulted( custom_field_get_field( $t_cf_id, 'name' ) ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		} else {
			# The custom field isn't compulsory and the user did
			# not supply a value. Therefore we can just ignore this
			# custom field completely (ie. don't attempt to update
			# the field).
			continue;
		}
	}

	if( !custom_field_has_write_access( $t_cf_id, $f_bug_id ) ) {
		trigger_error( ERROR_ACCESS_DENIED, ERROR );
	}

	$t_new_custom_field_value = \Core\GPC::get_custom_field( 'custom_field_' . $t_cf_id, $t_cf_def['type'], null );
	$t_old_custom_field_value = custom_field_get_value( $t_cf_id, $f_bug_id );

	# Validate the value of the field against current validation rules.
	# This may cause an error if validation rules have recently been
	# modified such that old values that were once OK are now considered
	# invalid.
	if( !custom_field_validate( $t_cf_id, $t_new_custom_field_value ) ) {
		\Core\Error::parameters( \Core\Lang::get_defaulted( custom_field_get_field( $t_cf_id, 'name' ) ) );
		trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
	}

	# Remember the new custom field values so we can set them when updating
	# the bug (done after all data passed to this update page has been
	# validated).
	$t_custom_fields_to_set[] = array( 'id' => $t_cf_id, 'value' => $t_new_custom_field_value );
}

# Perform validation of the duplicate ID of the bug.
if( $t_updated_bug->duplicate_id !== 0 ) {
	if( $t_updated_bug->duplicate_id === $f_bug_id ) {
		trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
	}
	\Core\Bug::ensure_exists( $t_updated_bug->duplicate_id );
	if( !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'update_bug_threshold' ), $t_updated_bug->duplicate_id ) ) {
		trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
	}
	if( \Core\Relationship::exists( $f_bug_id, $t_updated_bug->duplicate_id ) ) {
		trigger_error( ERROR_RELATIONSHIP_ALREADY_EXISTS, ERROR );
	}
}

# Validate the new bug note (if any is provided).
if( $t_bug_note->note ||
	 ( \Core\Config::mantis_get( 'time_tracking_enabled' ) &&
	   \Core\Helper::duration_to_minutes( $t_bug_note->time_tracking ) > 0 ) ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'add_bugnote_threshold' ), $f_bug_id );
	if( !$t_bug_note->note &&
	     !\Core\Config::mantis_get( 'time_tracking_without_note' ) ) {
		\Core\Error::parameters( \Core\Lang::get( 'bugnote' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}
	if( $t_bug_note->view_state !== \Core\Config::mantis_get( 'default_bugnote_view_status' ) ) {
		\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'set_view_status_threshold' ), $f_bug_id );
	}
}

# Handle the reassign on feedback feature. Note that this feature generally
# won't work very well with custom workflows as it makes a lot of assumptions
# that may not be true. It assumes you don't have any statuses in the workflow
# between 'bug_submit_status' and 'bug_feedback_status'. It assumes you only
# have one feedback, assigned and submitted status.
if( $t_bug_note->note &&
	 \Core\Config::mantis_get( 'reassign_on_feedback' ) &&
	 $t_existing_bug->status === \Core\Config::mantis_get( 'bug_feedback_status' ) &&
	 $t_updated_bug->status !== $t_existing_bug->status &&
	 $t_updated_bug->handler_id !== \Core\Auth::get_current_user_id() &&
	 $t_updated_bug->reporter_id === \Core\Auth::get_current_user_id() ) {
	if( $t_updated_bug->handler_id !== NO_USER ) {
		$t_updated_bug->status = \Core\Config::mantis_get( 'bug_assigned_status' );
	} else {
		$t_updated_bug->status = \Core\Config::mantis_get( 'bug_submit_status' );
	}
}

# Handle automatic assignment of issues.
if( $t_existing_bug->handler_id === NO_USER &&
	 $t_updated_bug->handler_id !== NO_USER &&
	 $t_updated_bug->status < \Core\Config::mantis_get( 'bug_assigned_status' ) &&
	 \Core\Config::mantis_get( 'auto_set_status_to_assigned' ) ) {
	$t_updated_bug->status = \Core\Config::mantis_get( 'bug_assigned_status' );
}

# Allow a custom function to validate the proposed bug updates. Note that
# custom functions are being deprecated in MantisBT. You should migrate to
# the new plugin system instead.
\Core\Helper::call_custom_function( 'issue_update_validate', array( $f_bug_id, $t_updated_bug, $t_bug_note->note ) );

# Allow plugins to validate/modify the update prior to it being committed.
$t_updated_bug = \Core\Event::signal( 'EVENT_UPDATE_BUG_DATA', $t_updated_bug, $t_existing_bug );

# Commit the bug updates to the database.
$t_text_field_update_required = ( $t_existing_bug->description !== $t_updated_bug->description ) ||
								( $t_existing_bug->additional_information !== $t_updated_bug->additional_information ) ||
								( $t_existing_bug->steps_to_reproduce !== $t_updated_bug->steps_to_reproduce );
$t_updated_bug->update( $t_text_field_update_required, true );

# Update custom field values.
foreach ( $t_custom_fields_to_set as $t_custom_field_to_set ) {
	custom_field_set_value( $t_custom_field_to_set['id'], $f_bug_id, $t_custom_field_to_set['value'] );
}

# Add a bug note if there is one.
if( $t_bug_note->note || \Core\Helper::duration_to_minutes( $t_bug_note->time_tracking ) > 0 ) {
	\Core\Bug\Note::add( $f_bug_id, $t_bug_note->note, $t_bug_note->time_tracking, $t_bug_note->view_state == VS_PRIVATE, 0, '', null, false );
}

# Add a duplicate relationship if requested.
if( $t_updated_bug->duplicate_id !== 0 ) {
	\Core\Relationship::add( $f_bug_id, $t_updated_bug->duplicate_id, BUG_DUPLICATE );
	\Core\History::log_event_special( $f_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $t_updated_bug->duplicate_id );
	\Core\History::log_event_special( $t_updated_bug->duplicate_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $f_bug_id );
	if( \Core\User::exists( $t_existing_bug->reporter_id ) ) {
		\Core\Bug::monitor( $f_bug_id, $t_existing_bug->reporter_id );
	}
	if( \Core\User::exists( $t_existing_bug->handler_id ) ) {
		\Core\Bug::monitor( $f_bug_id, $t_existing_bug->handler_id );
	}
	\Core\Bug::monitor_copy( $f_bug_id, $t_updated_bug->duplicate_id );
}

\Core\Event::signal( 'EVENT_UPDATE_BUG', array( $t_existing_bug, $t_updated_bug ) );

# Allow a custom function to respond to the modifications made to the bug. Note
# that custom functions are being deprecated in MantisBT. You should migrate to
# the new plugin system instead.
\Core\Helper::call_custom_function( 'issue_update_notify', array( $f_bug_id ) );

# Send a notification of changes via email.
if( $t_resolve_issue ) {
	\Core\Email::generic( $f_bug_id, 'resolved', 'The following issue has been RESOLVED.' );
	\Core\Email::relationship_child_resolved( $f_bug_id );
} else if( $t_close_issue ) {
	\Core\Email::generic( $f_bug_id, 'closed', 'The following issue has been CLOSED' );
	\Core\Email::relationship_child_closed( $f_bug_id );
} else if( $t_reopen_issue ) {
	\Core\Email::generic( $f_bug_id, 'reopened', 'email_notification_title_for_action_bug_reopened' );
} else if( $t_existing_bug->handler_id === NO_USER &&
			$t_updated_bug->handler_id !== NO_USER ) {
	\Core\Email::generic( $f_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
} else if( $t_existing_bug->status !== $t_updated_bug->status ) {
	$t_new_status_label = \Core\Enum::getLabel( \Core\Config::mantis_get( 'status_enum_string' ), $t_updated_bug->status );
	$t_new_status_label = str_replace( ' ', '_', $t_new_status_label );
	\Core\Email::generic( $f_bug_id, $t_new_status_label, 'email_notification_title_for_status_bug_' . $t_new_status_label );
} else {
	\Core\Email::generic( $f_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
}

\Core\Form::security_purge( 'bug_update' );

\Core\Print_Util::successful_redirect_to_bug( $f_bug_id );
