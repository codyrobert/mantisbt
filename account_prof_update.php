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
 * This page updates the users profile information then redirects to
 * account_prof_menu_page.php
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
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses profile_api.php
 */

require_once( 'core.php' );

if( !\Core\Config::mantis_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

\Core\Form::security_validate( 'profile_update' );

\Core\Auth::ensure_user_authenticated();

\Core\Current_User::ensure_unprotected();

$f_action = \Core\GPC::get_string( 'action' );

if( $f_action != 'add' ) {
	$f_profile_id = \Core\GPC::get_int( 'profile_id' );

	# Make sure user did select an existing profile from the list
	if( $f_action != 'make_default' && $f_profile_id == 0 ) {
		\Core\Error::parameters( \Core\Lang::get( 'select_profile' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}
}

switch( $f_action ) {
	case 'edit':
		\Core\Form::security_purge( 'profile_update' );
		\Core\Print_Util::header_redirect( 'account_prof_edit_page.php?profile_id=' . $f_profile_id );
		break;

	case 'add':
		$f_platform		= \Core\GPC::get_string( 'platform' );
		$f_os			= \Core\GPC::get_string( 'os' );
		$f_os_build		= \Core\GPC::get_string( 'os_build' );
		$f_description	= \Core\GPC::get_string( 'description' );

		$t_user_id		= \Core\GPC::get_int( 'user_id' );
		if( ALL_USERS != $t_user_id ) {
			$t_user_id = \Core\Auth::get_current_user_id();
		}

		if( ALL_USERS == $t_user_id ) {
			\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_global_profile_threshold' ) );
		} else {
			\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'add_profile_threshold' ) );
		}

		\Core\Profile::create( $t_user_id, $f_platform, $f_os, $f_os_build, $f_description );
		\Core\Form::security_purge( 'profile_update' );

		if( ALL_USERS == $t_user_id ) {
			\Core\Print_Util::header_redirect( 'manage_prof_menu_page.php' );
		} else {
			\Core\Print_Util::header_redirect( 'account_prof_menu_page.php' );
		}
		break;

	case 'update':
		$f_platform = \Core\GPC::get_string( 'platform' );
		$f_os = \Core\GPC::get_string( 'os' );
		$f_os_build = \Core\GPC::get_string( 'os_build' );
		$f_description = \Core\GPC::get_string( 'description' );

		if( \Core\Profile::is_global( $f_profile_id ) ) {
			\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_global_profile_threshold' ) );

			\Core\Profile::update( ALL_USERS, $f_profile_id, $f_platform, $f_os, $f_os_build, $f_description );
			\Core\Form::security_purge( 'profile_update' );
			\Core\Print_Util::header_redirect( 'manage_prof_menu_page.php' );
		} else {
			\Core\Profile::update( \Core\Auth::get_current_user_id(), $f_profile_id, $f_platform, $f_os, $f_os_build, $f_description );
			\Core\Form::security_purge( 'profile_update' );
			\Core\Print_Util::header_redirect( 'account_prof_menu_page.php' );
		}
		break;

	case 'delete':
		if( \Core\Profile::is_global( $f_profile_id ) ) {
			\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_global_profile_threshold' ) );

			\Core\Profile::delete( ALL_USERS, $f_profile_id );
			\Core\Form::security_purge( 'profile_update' );
			\Core\Print_Util::header_redirect( 'manage_prof_menu_page.php' );
		} else {
			\Core\Profile::delete( \Core\Auth::get_current_user_id(), $f_profile_id );
			\Core\Form::security_purge( 'profile_update' );
			\Core\Print_Util::header_redirect( 'account_prof_menu_page.php' );
		}
		break;

	case 'make_default':
		\Core\Current_User::set_pref( 'default_profile', $f_profile_id );
		\Core\Form::security_purge( 'profile_update' );
		\Core\Print_Util::header_redirect( 'account_prof_menu_page.php' );
		break;
}
