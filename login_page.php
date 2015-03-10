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
 * Login page POSTs results to login.php
 * Check to see if the user is already logged in
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
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
\Core\HTML::require_css( 'login.css' );

$f_error                 = \Core\GPC::get_bool( 'error' );
$f_cookie_error          = \Core\GPC::get_bool( 'cookie_error' );
$f_return                = \Core\String::sanitize_url( \Core\GPC::get_string( 'return', '' ) );
$f_username              = \Core\GPC::get_string( 'username', '' );
$f_perm_login            = \Core\GPC::get_bool( 'perm_login', false );
$f_secure_session        = \Core\GPC::get_bool( 'secure_session', false );
$f_secure_session_cookie = \Core\GPC::get_cookie( \Core\Config::get_global( 'cookie_prefix' ) . '_secure_session', null );

# Set username to blank if invalid to prevent possible XSS exploits
if( !\Core\User::is_name_valid( $f_username ) ) {
	$f_username = '';
}

$t_session_validation = ( ON == \Core\Config::get_global( 'session_validation' ) );

# If user is already authenticated and not anonymous
if( auth_is_user_authenticated() && !\Core\Current_User::is_anonymous() ) {
	# If return URL is specified redirect to it; otherwise use default page
	if( !\Core\Utility::is_blank( $f_return ) ) {
		\Core\Print_Util::header_redirect( $f_return, false, false, true );
	} else {
		\Core\Print_Util::header_redirect( \Core\Config::mantis_get( 'default_home_page' ) );
	}
}

# Check for automatic logon methods where we want the logon to just be handled by login.php
if( auth_automatic_logon_bypass_form() ) {
	$t_uri = 'login.php';

	if( ON == \Core\Config::mantis_get( 'allow_anonymous_login' ) ) {
		$t_uri = 'login_anon.php';
	}

	if( !\Core\Utility::is_blank( $f_return ) ) {
		$t_uri .= '?return=' . \Core\String::url( $f_return );
	}

	\Core\Print_Util::header_redirect( $t_uri );
	exit;
}

# Determine if secure_session should default on or off?
# - If no errors, and no cookies set, default to on.
# - If no errors, but cookie is set, use the cookie value.
# - If errors, use the value passed in.
if( $t_session_validation ) {
	if( !$f_error && !$f_cookie_error ) {
		$t_default_secure_session = ( is_null( $f_secure_session_cookie ) ? true : $f_secure_session_cookie );
	} else {
		$t_default_secure_session = $f_secure_session;
	}
}

# Determine whether the username or password field should receive automatic focus.
$t_username_field_autofocus = 'autofocus';
$t_password_field_autofocus = '';
if( $f_username ) {
	$t_username_field_autofocus = '';
	$t_password_field_autofocus = 'autofocus';
}

# Login page shouldn't be indexed by search engines
\Core\HTML::robots_noindex();

\Core\HTML::page_top1();
\Core\HTML::page_top2a();

if( $f_error || $f_cookie_error ) {
	echo '<div class="important-msg">';
	echo '<ul>';

	# Display short greeting message
	# echo \Core\Lang::get( 'login_page_info' ) . '<br />';

	# Only echo error message if error variable is set
	if( $f_error ) {
		echo '<li>' . \Core\Lang::get( 'login_error' ) . '</li>';
	}
	if( $f_cookie_error ) {
		echo '<li>' . \Core\Lang::get( 'login_cookies_disabled' ) . '</li>';
	}
	echo '</ul>';
	echo '</div>';
}

$t_warnings = array();
$t_upgrade_required = false;
if( \Core\Config::get_global( 'admin_checks' ) == ON && file_exists( dirname( __FILE__ ) .'/admin' ) ) {
	# Generate a warning if default user administrator/root is valid.
	$t_admin_user_id = \Core\User::get_id_by_name( 'administrator' );
	if( $t_admin_user_id !== false ) {
		if( \Core\User::is_enabled( $t_admin_user_id ) && auth_does_password_match( $t_admin_user_id, 'root' ) ) {
			$t_warnings[] = \Core\Lang::get( 'warning_default_administrator_account_present' );
		}
	}

	/**
	 * Display Warnings for enabled debugging / developer settings
	 * @param string $p_type    Message Type.
	 * @param string $p_setting Setting.
	 * @param string $p_value   Value.
	 * @return string
	 */
	function debug_setting_message ( $p_type, $p_setting, $p_value ) {
		return sprintf( \Core\Lang::get( 'warning_change_setting' ), $p_setting, $p_value )
			. sprintf( \Core\Lang::get( 'word_separator' ) )
			. sprintf( \Core\Lang::get( "warning_${p_type}_hazard" ) );
	}

	$t_config = 'show_detailed_errors';
	if( \Core\Config::mantis_get( $t_config ) != OFF ) {
		$t_warnings[] = debug_setting_message( 'security', $t_config, 'OFF' );
	}
	$t_config = 'display_errors';
	$t_errors = \Core\Config::get_global( $t_config );
	if( $t_errors[E_USER_ERROR] != DISPLAY_ERROR_HALT ) {
		$t_warnings[] = debug_setting_message(
			'integrity',
			$t_config . '[E_USER_ERROR]',
			DISPLAY_ERROR_HALT );
	}

	# since admin directory and db_upgrade lists are available check for missing db upgrades
	# if db version is 0, we do not have a valid database.
	$t_db_version = \Core\Config::mantis_get( 'database_version', 0 );
	if( $t_db_version == 0 ) {
		$t_warnings[] = \Core\Lang::get( 'error_database_no_schema_version' );
	}

	# Check for db upgrade for versions > 1.0.0 using new installer and schema
	# Note: install_helper_functions_api.php required for db_null_date() function definition
	require_api( 'install_helper_functions_api.php' );
	require_once( 'admin' . DIRECTORY_SEPARATOR . 'schema.php' );
	$t_upgrades_reqd = count( $g_upgrade ) - 1;

	if( ( 0 < $t_db_version ) &&
			( $t_db_version != $t_upgrades_reqd ) ) {

		if( $t_db_version < $t_upgrades_reqd ) {
			$t_warnings[] = \Core\Lang::get( 'error_database_version_out_of_date_2' );
			$t_upgrade_required = true;
		} else {
			$t_warnings[] = \Core\Lang::get( 'error_code_version_out_of_date' );
		}
	}
}
?>

<!-- Login Form BEGIN -->
<div id="login-div" class="form-container">
	<form id="login-form" method="post" action="login.php">
		<fieldset>
			<legend><span><?php echo \Core\Lang::get( 'login_title' ) ?></span></legend>
			<?php
			if( !\Core\Utility::is_blank( $f_return ) ) {
				echo '<input type="hidden" name="return" value="', \Core\String::html_specialchars( $f_return ), '" />';
			}

			if( $t_upgrade_required ) {
				echo '<input type="hidden" name="install" value="true" />';
			}

			# CSRF protection not required here - form does not result in modifications
			echo '<ul id="login-links">';

			if( ON == \Core\Config::mantis_get( 'allow_anonymous_login' ) ) {
				echo '<li><a href="login_anon.php?return=' . \Core\String::url( $f_return ) . '">' . \Core\Lang::get( 'login_anonymously' ) . '</a></li>';
			}

			if( ( ON == \Core\Config::get_global( 'allow_signup' ) ) &&
				( LDAP != \Core\Config::get_global( 'login_method' ) ) &&
				( ON == \Core\Config::mantis_get( 'enable_email_notification' ) )
			) {
				echo '<li><a href="signup_page.php">', \Core\Lang::get( 'signup_link' ), '</a></li>';
			}
			# lost password feature disabled or reset password via email disabled -> stop here!
			if( ( LDAP != \Core\Config::get_global( 'login_method' ) ) &&
				( ON == \Core\Config::mantis_get( 'lost_password_feature' ) ) &&
				( ON == \Core\Config::mantis_get( 'send_reset_password' ) ) &&
				( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
				echo '<li><a href="lost_pwd_page.php">', \Core\Lang::get( 'lost_password_link' ), '</a></li>';
			}
			?>
			</ul>
			<div class="field-container">
				<label for="username"><span><?php echo \Core\Lang::get( 'username' ) ?></span></label>
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" value="<?php echo \Core\String::attribute( $f_username ); ?>" class="<?php echo $t_username_field_autofocus ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="password"><span><?php echo \Core\Lang::get( 'password' ) ?></span></label>
				<span class="input"><input id="password" type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" class="<?php echo $t_password_field_autofocus ?>" /></span>
				<span class="label-style"></span>
			</div>
			<?php if( ON == \Core\Config::mantis_get( 'allow_permanent_cookie' ) ) { ?>
			<div class="field-container">
				<label for="remember-login"><span><?php echo \Core\Lang::get( 'save_login' ) ?></span></label>
				<span class="input"><input id="remember-login" type="checkbox" name="perm_login" <?php echo ( $f_perm_login ? 'checked="checked" ' : '' ) ?>/></span>
				<span class="label-style"></span>
			</div>
			<?php } ?>
			<?php if( $t_session_validation ) { ?>
			<div class="field-container">
				<label id="secure-session-label" for="secure-session"><span><?php echo \Core\Lang::get( 'secure_session' ) ?></span></label>
				<span class="input">
					<input id="secure-session" type="checkbox" name="secure_session" <?php echo ( $t_default_secure_session ? 'checked="checked" ' : '' ) ?>/>
					<span id="session-msg"><?php echo \Core\Lang::get( 'secure_session_long' ); ?></span>
				</span>
				<span class="label-style"></span>
			</div>
			<?php } ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'login_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
#
# Do some checks to warn administrators of possible security holes.
#

if( count( $t_warnings ) > 0 ) {
	echo '<div class="important-msg">';
	echo '<ul>';
	foreach( $t_warnings as $t_warning ) {
		echo '<li>' . $t_warning . '</li>';
	}
	echo '</ul>';
	echo '</div>';
}

\Core\HTML::page_bottom1a( __FILE__ );
