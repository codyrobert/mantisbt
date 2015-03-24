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
 * This file turns monitoring on or off for a bug for the current user
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
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */



\Core\Form::security_validate( 'bug_monitor_add' );

$f_bug_id = \Core\GPC::get_int( 'bug_id' );
$t_bug = \Core\Bug::get( $f_bug_id, true );
$f_username = \Core\GPC::get_string( 'username', '' );

$t_logged_in_user_id = \Core\Auth::get_current_user_id();

if( \Core\Utility::is_blank( $f_username ) ) {
	$t_user_id = $t_logged_in_user_id;
} else {
	$t_user_id = \Core\User::get_id_by_name( $f_username );
	if( $t_user_id === false ) {
		$t_user_id = \Core\User::get_id_by_realname( $f_username );

		if( $t_user_id === false ) {
			\Core\Error::parameters( $f_username );
			trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, E_USER_ERROR );
		}
	}
}

if( \Core\User::is_anonymous( $t_user_id ) ) {
	trigger_error( ERROR_PROTECTED_ACCOUNT, E_USER_ERROR );
}

\Core\Bug::ensure_exists( $f_bug_id );

if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( $t_logged_in_user_id == $t_user_id ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'monitor_bug_threshold' ), $f_bug_id );
} else {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'monitor_add_others_bug_threshold' ), $f_bug_id );
}

\Core\Bug::monitor( $f_bug_id, $t_user_id );

\Core\Form::security_purge( 'bug_monitor_add' );

\Core\Print_Util::successful_redirect_to_bug( $f_bug_id );
