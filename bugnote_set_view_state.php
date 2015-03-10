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
 * Set an existing bugnote private or public.
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
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'bugnote_set_view_state' );

$f_bugnote_id	= \Core\GPC::get_int( 'bugnote_id' );
$f_private		= \Core\GPC::get_bool( 'private' );

$t_bug_id = \Core\Bug\Note::get_field( $f_bugnote_id, 'bug_id' );

$t_bug = \Core\Bug::get( $t_bug_id, true );
if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# Check if the bug is readonly
if( \Core\Bug::is_readonly( $t_bug_id ) ) {
	\Core\Error::parameters( $t_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

# Check if the current user is allowed to change the view state of this bugnote
$t_user_id = \Core\Bug\Note::get_field( $f_bugnote_id, 'reporter_id' );
if( $t_user_id == auth_get_current_user_id() ) {
	\Core\Access::ensure_bugnote_level( \Core\Config::mantis_get( 'bugnote_user_change_view_state_threshold' ), $f_bugnote_id );
} else {
	\Core\Access::ensure_bugnote_level( \Core\Config::mantis_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	\Core\Access::ensure_bugnote_level( \Core\Config::mantis_get( 'change_view_status_threshold' ), $f_bugnote_id );
}

\Core\Bug\Note::set_view_state( $f_bugnote_id, $f_private );

\Core\Form::security_purge( 'bugnote_set_view_state' );

\Core\Print_Util::successful_redirect( \Core\String::get_bug_view_url( $t_bug_id ) . '#bugnotes' );
