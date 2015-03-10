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
 * Reset a Users Password
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_user_reset' );

auth_reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) );

$f_user_id = \Core\GPC::get_int( 'user_id' );

\Core\User::ensure_exists( $f_user_id );

$t_user = \Core\User::get_row( $f_user_id );

# Ensure that the account to be reset is of equal or lower access to the
# current user.
\Core\Access::ensure_global_level( $t_user['access_level'] );

# If the password can be changed, we reset it, otherwise we unlock
# the account (i.e. reset failed login count)
$t_reset = \Core\Helper::call_custom_function( 'auth_can_change_password', array() );
if( $t_reset ) {
	$t_result = \Core\User::reset_password( $f_user_id );
} else {
	$t_result = \Core\User::reset_failed_login_count_to_zero( $f_user_id );
}

$t_redirect_url = 'manage_user_page.php';

\Core\Form::security_purge( 'manage_user_reset' );

\Core\HTML::page_top( null, $t_result ? $t_redirect_url : null );

echo '<div class="success-msg">';

if( $t_reset ) {
	if( false == $t_result ) {
		# PROTECTED
		echo \Core\Lang::get( 'account_reset_protected_msg' );
	} else {
		# SUCCESSFUL RESET
		if( ( ON == \Core\Config::mantis_get( 'send_reset_password' ) ) && ( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
			# send the new random password via email
			echo \Core\Lang::get( 'account_reset_msg' );
		} else {
			# email notification disabled, then set the password to blank
			echo \Core\Lang::get( 'account_reset_msg2' );
		}
	}
} else {
	# UNLOCK
	echo \Core\Lang::get( 'account_unlock_msg' );
}

echo '<br />';
\Core\Print_Util::bracket_link( $t_redirect_url, \Core\Lang::get( 'proceed' ) );
echo '</div>';
\Core\HTML::page_bottom();
