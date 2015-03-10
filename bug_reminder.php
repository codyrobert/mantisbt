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
 * This page allows an authorized user to send a reminder by email to another user
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
 * @uses email_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'bug_reminder' );

$f_bug_id		= \Core\GPC::get_int( 'bug_id' );
$f_to			= \Core\GPC::get_int_array( 'to' );
$f_body			= \Core\GPC::get_string( 'body' );

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

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'bug_reminder_threshold' ), $f_bug_id );

# Automatically add recipients to monitor list if they are above the monitor
# threshold, option is enabled, and not reporter or handler.
$t_reminder_recipients_monitor_bug = \Core\Config::mantis_get( 'reminder_recipients_monitor_bug' );
$t_monitor_bug_threshold = \Core\Config::mantis_get( 'monitor_bug_threshold' );
$t_handler = \Core\Bug::get_field( $f_bug_id, 'handler_id' );
$t_reporter = \Core\Bug::get_field( $f_bug_id, 'reporter_id' );
foreach ( $f_to as $t_recipient ) {
	if( ON == $t_reminder_recipients_monitor_bug
		&& \Core\Access::has_bug_level( $t_monitor_bug_threshold, $f_bug_id )
		&& $t_recipient != $t_handler
		&& $t_recipient != $t_reporter
	) {
		\Core\Bug::monitor( $f_bug_id, $t_recipient );
	}
}

$t_result = \Core\Email::bug_reminder( $f_to, $f_bug_id, $f_body );

# Add reminder as bugnote if store reminders option is ON.
if( ON == \Core\Config::mantis_get( 'store_reminders' ) ) {
	# Build list of recipients, truncated to note_attr fields's length
	$t_attr = '|';
	$t_length = 0;
	foreach( $t_result as $t_id ) {
		$t_recipient = $t_id . '|';
		$t_length += strlen( $t_recipient );
		if( $t_length > 250 ) {
			# Remove trailing delimiter to indicate truncation
			$t_attr = rtrim( $t_attr, '|' );
			break;
		}
		$t_attr .= $t_recipient;
	}
	\Core\Bug\Note::add( $f_bug_id, $f_body, 0, \Core\Config::mantis_get( 'default_reminder_view_status' ) == VS_PRIVATE, REMINDER, $t_attr, null, false );
}

\Core\Form::security_purge( 'bug_reminder' );

\Core\HTML::page_top( null, \Core\String::get_bug_view_url( $f_bug_id ) );

$t_redirect = \Core\String::get_bug_view_url( $f_bug_id );
\Core\HTML::operation_successful( $t_redirect );

\Core\HTML::page_bottom();
