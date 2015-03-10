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
 * Update User
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
 * @uses database_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_user_update' );

auth_reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) );

$f_protected	= \Core\GPC::get_bool( 'protected' );
$f_enabled		= \Core\GPC::get_bool( 'enabled' );
$f_email		= \Core\GPC::get_string( 'email', '' );
$f_username		= \Core\GPC::get_string( 'username', '' );
$f_realname		= \Core\GPC::get_string( 'realname', '' );
$f_access_level	= \Core\GPC::get_int( 'access_level' );
$f_user_id		= \Core\GPC::get_int( 'user_id' );

if( \Core\Config::mantis_get( 'enable_email_notification' ) == ON ) {
	$f_send_email_notification = \Core\GPC::get_bool( 'send_email_notification' );
} else {
	$f_send_email_notification = 0;
}

\Core\User::ensure_exists( $f_user_id );

$t_user = \Core\User::get_row( $f_user_id );

$f_username	= trim( $f_username );

$t_old_username = $t_user['username'];

if( $f_send_email_notification ) {
	$t_old_realname = $t_user['realname'];
	$t_old_email = $t_user['email'];
	$t_old_access_level = $t_user['access_level'];
}

# Ensure that the account to be updated is of equal or lower access to the
# current user.
\Core\Access::ensure_global_level( $t_user['access_level'] );

# check that the username is unique
if( 0 != strcasecmp( $t_old_username, $f_username )
	&& false == \Core\User::is_name_unique( $f_username ) ) {
	trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
}

\Core\User::ensure_name_valid( $f_username );

$t_ldap = ( LDAP == \Core\Config::mantis_get( 'login_method' ) );

if( $t_ldap && \Core\Config::mantis_get( 'use_ldap_realname' ) ) {
	$t_realname = \Core\LDAP::realname_from_username( $f_username );
} else {
	# strip extra space from real name
	$t_realname = string_normalize( $f_realname );
	\Core\User::ensure_realname_unique( $t_old_username, $t_realname );
}

if( $t_ldap && \Core\Config::mantis_get( 'use_ldap_email' ) ) {
	$t_email = \Core\LDAP::email( $f_user_id );
} else {
	$t_email = trim( $f_email );
	\Core\Email::ensure_valid( $t_email );
	\Core\Email::ensure_not_disposable( $t_email );
}

$c_email = $t_email;
$c_username = $f_username;
$c_realname = $t_realname;
$c_protected = (bool)$f_protected;
$c_enabled = (bool)$f_enabled;
$c_user_id = (int)$f_user_id;
$c_access_level = (int)$f_access_level;

$t_old_protected = $t_user['protected'];

# Ensure that users aren't escalating privileges of accounts beyond their
# own global access level.
\Core\Access::ensure_global_level( $f_access_level );

# check that we are not downgrading the last administrator
$t_admin_threshold = \Core\Config::get_global( 'admin_site_threshold' );
if( \Core\User::is_administrator( $f_user_id ) &&
	 $f_access_level < $t_admin_threshold &&
	 \Core\User::count_level( $t_admin_threshold ) <= 1 ) {
	trigger_error( ERROR_USER_CHANGE_LAST_ADMIN, ERROR );
}

# Project specific access rights override global levels, hence, for users who are changed
# to be administrators, we have to remove project specific rights.
if( ( $f_access_level >= $t_admin_threshold ) && ( !\Core\User::is_administrator( $f_user_id ) ) ) {
	\Core\User::delete_project_specific_access_levels( $f_user_id );
}

# if the user is already protected and the admin is not removing the
#  protected flag then don't update the access level and enabled flag.
#  If the user was unprotected or the protected flag is being turned off
#  then proceed with a full update.
$t_query_params = array();
if( $f_protected && $t_old_protected ) {
	$t_query = 'UPDATE {user}
			SET username=' . \Core\Database::param() . ', email=' . \Core\Database::param() . ',
				protected=' . \Core\Database::param() . ', realname=' . \Core\Database::param() . '
			WHERE id=' . \Core\Database::param();
	$t_query_params = array( $c_username, $c_email, $c_protected, $c_realname, $c_user_id );
	# Prevent e-mail notification for a change that did not happen
	$f_access_level = $t_old_access_level;
} else {
	$t_query = 'UPDATE {user}
			SET username=' . \Core\Database::param() . ', email=' . \Core\Database::param() . ',
				access_level=' . \Core\Database::param() . ', enabled=' . \Core\Database::param() . ',
				protected=' . \Core\Database::param() . ', realname=' . \Core\Database::param() . '
			WHERE id=' . \Core\Database::param();
	$t_query_params = array( $c_username, $c_email, $c_access_level, $c_enabled, $c_protected, $c_realname, $c_user_id );
}

$t_result = \Core\Database::query( $t_query, $t_query_params );

if( $f_send_email_notification ) {
	\Core\Lang::push( \Core\User\Pref::get_language( $f_user_id ) );
	$t_changes = '';
	if( strcmp( $f_username, $t_old_username ) ) {
		$t_changes .= \Core\Lang::get( 'username_label' ) . \Core\Lang::get( 'word_separator' ) . $t_old_username . ' => ' . $f_username . "\n";
	}
	if( strcmp( $t_realname, $t_old_realname ) ) {
		$t_changes .= \Core\Lang::get( 'realname_label' ) . \Core\Lang::get( 'word_separator' ) . $t_old_realname . ' => ' . $t_realname . "\n";
	}
	if( strcmp( $t_email, $t_old_email ) ) {
		$t_changes .= \Core\Lang::get( 'email_label' ) . \Core\Lang::get( 'word_separator' ) . $t_old_email . ' => ' . $t_email . "\n";
	}
	if( strcmp( $f_access_level, $t_old_access_level ) ) {
		$t_old_access_string = \Core\Helper::get_enum_element( 'access_levels', $t_old_access_level );
		$t_new_access_string = \Core\Helper::get_enum_element( 'access_levels', $f_access_level );
		$t_changes .= \Core\Lang::get( 'access_level_label' ) . \Core\Lang::get( 'word_separator' ) . $t_old_access_string . ' => ' . $t_new_access_string . "\n\n";
	}
	if( !empty( $t_changes ) ) {
		$t_subject = '[' . \Core\Config::mantis_get( 'window_title' ) . '] ' . \Core\Lang::get( 'email_user_updated_subject' );
		$t_updated_msg = \Core\Lang::get( 'email_user_updated_msg' );
		$t_message = $t_updated_msg . "\n\n" . \Core\Config::mantis_get( 'path' ) . 'account_page.php' . "\n\n" . $t_changes;

		if( null === \Core\Email::store( $t_email, $t_subject, $t_message ) ) {
			\Core\Log::event( LOG_EMAIL, 'Notification was NOT sent to ' . $f_username );
		} else {
			\Core\Log::event( LOG_EMAIL, 'Account update notification sent to ' . $f_username . ' (' . $t_email . ')' );
			if( \Core\Config::mantis_get( 'email_send_using_cronjob' ) == OFF ) {
				\Core\Email::send_all();
			}
		}
	}
	\Core\Lang::pop();
}

$t_redirect_url = 'manage_user_edit_page.php?user_id=' . $c_user_id;

\Core\Form::security_purge( 'manage_user_update' );

\Core\HTML::page_top( null, $t_result ? $t_redirect_url : null );

if( $f_protected && $t_old_protected ) {				# PROTECTED
	echo '<div class="failure-msg">';
	echo \Core\Lang::get( 'manage_user_protected_msg' ) . '<br />';
	\Core\Print_Util::bracket_link( $t_redirect_url, \Core\Lang::get( 'proceed' ) );
	echo '</div>';
} else if( $t_result ) {					# SUCCESS
	\Core\HTML::operation_successful( $t_redirect_url );
}

\Core\HTML::page_bottom();
