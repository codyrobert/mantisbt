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
 * Create a User
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
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_user_create' );

\Core\Auth::reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) );

$f_username        = \Core\GPC::get_string( 'username' );
$f_realname        = \Core\GPC::get_string( 'realname', '' );
$f_password        = \Core\GPC::get_string( 'password', '' );
$f_password_verify = \Core\GPC::get_string( 'password_verify', '' );
$f_email           = \Core\GPC::get_string( 'email', '' );
$f_access_level    = \Core\GPC::get_string( 'access_level' );
$f_protected       = \Core\GPC::get_bool( 'protected' );
$f_enabled         = \Core\GPC::get_bool( 'enabled' );

# check for empty username
$f_username = trim( $f_username );
if( \Core\Utility::is_blank( $f_username ) ) {
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

# Check the name for validity here so we do it before promting to use a
#  blank password (don't want to prompt the user if the process will fail
#  anyway)
# strip extra space from real name
$t_realname = \Core\String::normalize( $f_realname );
\Core\User::ensure_name_valid( $f_username );
\Core\User::ensure_realname_unique( $f_username, $f_realname );

if( $f_password != $f_password_verify ) {
	trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
}

\Core\Email::ensure_not_disposable( $f_email );

if( ( ON == \Core\Config::mantis_get( 'send_reset_password' ) ) && ( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
	# Check code will be sent to the user directly via email. Dummy password set to random
	# Create random password
	$f_password	= auth_generate_random_password();
} else {
	# Password won't to be sent by email. It entered by the admin
	# Now, if the password is empty, confirm that that is what we wanted
	if( \Core\Utility::is_blank( $f_password ) ) {
		\Core\Helper::ensure_confirmed( \Core\Lang::get( 'empty_password_sure_msg' ),
				 \Core\Lang::get( 'empty_password_button' ) );
	}
}

# Don't allow the creation of accounts with access levels higher than that of
# the user creating the account.
\Core\Access::ensure_global_level( $f_access_level );

# Need to send the user creation mail in the tracker language, not in the creating admin's language
# Park the current language name until the user has been created
\Core\Lang::push( \Core\Config::mantis_get( 'default_language' ) );

# create the user
$t_admin_name = \Core\User::get_name( \Core\Auth::get_current_user_id() );
$t_cookie = \Core\User::create( $f_username, $f_password, $f_email, $f_access_level, $f_protected, $f_enabled, $t_realname, $t_admin_name );

# set language back to user language
\Core\Lang::pop();

\Core\Form::security_purge( 'manage_user_create' );

if( $t_cookie === false ) {
	$t_redirect_url = 'manage_user_page.php';
} else {
	# ok, we created the user, get the row again
	$t_user_id = \Core\User::get_id_by_name( $f_username );
	$t_redirect_url = 'manage_user_edit_page.php?user_id=' . $t_user_id;
}

\Core\HTML::page_top( null, $t_redirect_url );
?>

<br />
<div class="success-msg">
<?php
$t_access_level = \Core\Helper::get_enum_element( 'access_levels', $f_access_level );
echo \Core\Lang::get( 'created_user_part1' ) . ' <span class="bold">' . $f_username . '</span> ' . \Core\Lang::get( 'created_user_part2' ) . ' <span class="bold">' . $t_access_level . '</span><br />';

\Core\Print_Util::bracket_link( $t_redirect_url, \Core\Lang::get( 'proceed' ) );
?>
</div>

<?php \Core\HTML::page_bottom();
