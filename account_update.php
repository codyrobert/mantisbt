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
 * This page updates a user's information
 * If an account is protected then changes are forbidden
 * The page gets redirected back to account_page.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'email_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );

\Flickerbox\Form::security_validate( 'account_update' );

$t_user_id = \Flickerbox\Auth::get_current_user_id();

# If token is set, it's a password reset request from verify.php, and if
# not we need to reauthenticate the user
$t_account_verification = \Flickerbox\Token::get_value( TOKEN_ACCOUNT_VERIFY, $t_user_id );
if( !$t_account_verification ) {
	auth_reauthenticate();
}

\Flickerbox\Auth::ensure_user_authenticated();

\Flickerbox\Current_User::ensure_unprotected();

$f_email           	= \Flickerbox\GPC::get_string( 'email', '' );
$f_realname        	= \Flickerbox\GPC::get_string( 'realname', '' );
$f_password_current = \Flickerbox\GPC::get_string( 'password_current', '' );
$f_password        	= \Flickerbox\GPC::get_string( 'password', '' );
$f_password_confirm	= \Flickerbox\GPC::get_string( 'password_confirm', '' );

$t_redirect_url = 'index.php';

# @todo Listing what fields were updated is not standard behaviour of MantisBT - it also complicates the code.
$t_email_updated = false;
$t_password_updated = false;
$t_realname_updated = false;

$t_ldap = ( LDAP == config_get( 'login_method' ) );

# Update email (but only if LDAP isn't being used)
if( !( $t_ldap && config_get( 'use_ldap_email' ) ) ) {
	email_ensure_valid( $f_email );
	email_ensure_not_disposable( $f_email );

	if( $f_email != user_get_email( $t_user_id ) ) {
		user_set_email( $t_user_id, $f_email );
		$t_email_updated = true;
	}
}

# Update real name (but only if LDAP isn't being used)
if( !( $t_ldap && config_get( 'use_ldap_realname' ) ) ) {
	# strip extra spaces from real name
	$t_realname = string_normalize( $f_realname );
	if( $t_realname != user_get_field( $t_user_id, 'realname' ) ) {
		# checks for problems with realnames
		$t_username = user_get_field( $t_user_id, 'username' );
		user_ensure_realname_unique( $t_username, $t_realname );
		user_set_realname( $t_user_id, $t_realname );
		$t_realname_updated = true;
	}
}

# Update password if the two match and are not empty
if( !\Flickerbox\Utility::is_blank( $f_password ) ) {
	if( $f_password != $f_password_confirm ) {
		trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
	} else {
		if( !$t_account_verification && !auth_does_password_match( $t_user_id, $f_password_current ) ) {
			trigger_error( ERROR_USER_CURRENT_PASSWORD_MISMATCH, ERROR );
		}

		if( !auth_does_password_match( $t_user_id, $f_password ) ) {
			user_set_password( $t_user_id, $f_password );
			$t_password_updated = true;
		}
	}
}

\Flickerbox\Form::security_purge( 'account_update' );

# Clear the verification token
if( $t_account_verification ) {
	\Flickerbox\Token::delete( TOKEN_ACCOUNT_VERIFY, $t_user_id );
}

\Flickerbox\HTML::page_top( null, $t_redirect_url );

$t_message = '';

if( $t_email_updated ) {
	$t_message .= \Flickerbox\Lang::get( 'email_updated' );
}

if( $t_password_updated ) {
	$t_message = \Flickerbox\Utility::is_blank( $t_message ) ? '' : $t_message . '<br />';
	$t_message .= \Flickerbox\Lang::get( 'password_updated' );
}

if( $t_realname_updated ) {
	$t_message = \Flickerbox\Utility::is_blank( $t_message ) ? '' : $t_message . '<br />';
	$t_message .= \Flickerbox\Lang::get( 'realname_updated' );
}

\Flickerbox\HTML::operation_successful( $t_redirect_url, $t_message );

\Flickerbox\HTML::page_bottom();
