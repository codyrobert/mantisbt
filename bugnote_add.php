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
 * Insert the bugnote into the database then redirect to the bug page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'bugnote_add' );

$f_bug_id		= \Core\GPC::get_int( 'bug_id' );
$f_private		= \Core\GPC::get_bool( 'private' );
$f_time_tracking	= \Core\GPC::get_string( 'time_tracking', '0:00' );
$f_bugnote_text	= trim( \Core\GPC::get_string( 'bugnote_text', '' ) );

$t_bug = \Core\Bug::get( $f_bug_id, true );
if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( \Core\Bug::is_readonly( $t_bug->id ) ) {
	\Core\Error::parameters( $t_bug->id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'add_bugnote_threshold' ), $t_bug->id );

if( $f_private ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'set_view_status_threshold' ), $t_bug->id );
}

# We always set the note time to BUGNOTE, and the API will overwrite it with TIME_TRACKING
# if $f_time_tracking is not 0 and the time tracking feature is enabled.
$t_bugnote_id = \Core\Bug\Note::add( $t_bug->id, $f_bugnote_text, $f_time_tracking, $f_private, BUGNOTE );
if( !$t_bugnote_id ) {
	\Core\Error::parameters( \Core\Lang::get( 'bugnote' ) );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

# Handle the reassign on feedback feature. Note that this feature generally
# won't work very well with custom workflows as it makes a lot of assumptions
# that may not be true. It assumes you don't have any statuses in the workflow
# between 'bug_submit_status' and 'bug_feedback_status'. It assumes you only
# have one feedback, assigned and submitted status.
if( \Core\Config::mantis_get( 'reassign_on_feedback' ) &&
	 $t_bug->status === \Core\Config::mantis_get( 'bug_feedback_status' ) &&
	 $t_bug->handler_id !== auth_get_current_user_id() &&
	 $t_bug->reporter_id === auth_get_current_user_id() ) {
	if( $t_bug->handler_id !== NO_USER ) {
		\Core\Bug::set_field( $t_bug->id, 'status', \Core\Config::mantis_get( 'bug_assigned_status' ) );
	} else {
		\Core\Bug::set_field( $t_bug->id, 'status', \Core\Config::mantis_get( 'bug_submit_status' ) );
	}
}

\Core\Form::security_purge( 'bugnote_add' );

\Core\Print_Util::successful_redirect_to_bug( $t_bug->id );
