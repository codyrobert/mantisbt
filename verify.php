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
 * Verify Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

# don't auto-login when trying to verify new user
$g_login_anonymous = false;

require_once( 'core.php' );

# check if at least one way to get here is enabled
if( OFF == \Core\Config::mantis_get( 'allow_signup' ) &&
	OFF == \Core\Config::mantis_get( 'lost_password_feature' ) &&
	OFF == \Core\Config::mantis_get( 'send_reset_password' ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
}

$f_user_id = \Core\GPC::get_string( 'id' );
$f_confirm_hash = \Core\GPC::get_string( 'confirm_hash' );

# force logout on the current user if already authenticated
if( \Core\Auth::is_user_authenticated() ) {
	\Core\Auth::logout();

	# reload the page after logout
	\Core\Print_Util::header_redirect( 'verify.php?id=' . $f_user_id . '&confirm_hash=' . $f_confirm_hash );
}

$t_calculated_confirm_hash = auth_generate_confirm_hash( $f_user_id );

if( $f_confirm_hash != $t_calculated_confirm_hash ) {
	trigger_error( ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID, ERROR );
}

# set a temporary cookie so the login information is passed between pages.
auth_set_cookies( $f_user_id, false );

\Core\User::reset_failed_login_count_to_zero( $f_user_id );
\Core\User::reset_lost_password_in_progress_count_to_zero( $f_user_id );

# fake login so the user can set their password
\Core\Auth::attempt_script_login( \Core\User::get_field( $f_user_id, 'username' ) );

\Core\User::increment_login_count( $f_user_id );


define( 'ACCOUNT_VERIFICATION_INC', true );
include ( dirname( __FILE__ ) . '/account_page.php' );
