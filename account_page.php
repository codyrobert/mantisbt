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
 * CALLERS
 * This page is called from:
 * - \Core\HTML::print_menu()
 * - \Core\HTML::print_account_menu()
 * - header redirects from account_*.php
 * - included by verify.php to allow user to change their password
 *
 * EXPECTED BEHAVIOUR
 * - Display the user's current settings
 * - Allow the user to edit their settings
 * - Allow the user to save their changes
 * - Allow the user to delete their account if account deletion is enabled
 *
 * CALLS
 * This page calls the following pages:
 * - account_update.php  (to save changes)
 * - account_delete.php  (to delete the user's account)
 *
 * RESTRICTIONS & PERMISSIONS
 * - User must be authenticated
 * - The user's account must not be protected
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
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses ldap_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

$t_account_verification = defined( 'ACCOUNT_VERIFICATION_INC' );

#============ Permissions ============
\Core\Auth::ensure_user_authenticated();

if( !$t_account_verification ) {
	\Core\Auth::reauthenticate();
}

\Core\Current_User::ensure_unprotected();

\Core\HTML::page_top( \Core\Lang::get( 'account_link' ) );

# extracts the user information for the currently logged in user
# and prefixes it with u_
$t_row = \Core\User::get_row( \Core\Auth::get_current_user_id() );

extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_ldap = ( LDAP == \Core\Config::mantis_get( 'login_method' ) );

# In case we're using LDAP to get the email address... this will pull out
#  that version instead of the one in the DB
$u_email = \Core\User::get_email( $u_id );

# If the password is the default password, then prompt user to change it.
$t_reset_password = $u_username == 'administrator' && \Core\Auth::does_password_match( $u_id, 'root' );

# note if we are being included by a script of a different name, if so,
# this is a mandatory password change request
$t_verify = \Core\Utility::is_page_name( 'verify.php' );

$t_force_pw_reset = false;

if( $t_verify || $t_reset_password ) {
	$t_can_change_password = \Core\Helper::call_custom_function( 'auth_can_change_password', array() );

	echo '<div id="reset-passwd-msg" class="important-msg">';
	echo '<ul>';

	if( $t_verify ) {
		echo '<li>' . \Core\Lang::get( 'verify_warning' ) . '</li>';

		if( $t_can_change_password ) {
			echo '<li>' . \Core\Lang::get( 'verify_change_password' ) . '</li>';
			$t_force_pw_reset = true;
		}
	} else if( $t_reset_password && $t_can_change_password ) {
		echo '<li>' . \Core\Lang::get( 'warning_default_administrator_account_present' ) . '</li>';
		$t_force_pw_reset = true;
	}

	echo '</ul>';
	echo '</div>';
}

$t_force_pw_reset_html = '';
if( $t_force_pw_reset ) {
	$t_force_pw_reset_html = ' class="has-required"';
}
?>

<div id="account-update-div" class="form-container">
	<form id="account-update-form" method="post" action="account_update.php">
		<fieldset <?php echo $t_force_pw_reset_html ?>>
			<legend><span><?php echo \Core\Lang::get( 'edit_account_title' ); ?></span></legend>
			<?php echo \Core\Form::security_field( 'account_update' );
			\Core\HTML::print_account_menu( 'account_page.php' );

			if( !\Core\Helper::call_custom_function( '\\Core\\Auth::can_change_password', array() ) ) {
				# With LDAP -->
			?>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'username' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo \Core\String::display_line( $u_username ) ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'password' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo \Core\Lang::get( 'no_password_change' ) ?></span></span>
				<span class="label-style"></span>
			</div><?php
			} else {
				# Without LDAP
				$t_show_update_button = true;
			?>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'username' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo \Core\String::display_line( $u_username ) ?></span></span>
				<span class="label-style"></span>
			</div><?php
			# When verifying account, set a token and don't display current password
			if( $t_account_verification ) {
				\Core\Token::set( TOKEN_ACCOUNT_VERIFY, true, TOKEN_EXPIRY_AUTHENTICATED, $u_id );
			} else {
			?>
			<div class="field-container">
				<label for="password" <?php echo $t_force_pw_reset_html ?>><span><?php echo \Core\Lang::get( 'current_password' ) ?></span></label>
				<span class="input"><input id="password-current" type="password" name="password_current" size="32" maxlength="<?php echo \Core\Auth::get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<?php } ?>
			<div class="field-container">
				<label for="password" <?php echo $t_force_pw_reset_html ?>><span><?php echo \Core\Lang::get( 'password' ) ?></span></label>
				<span class="input"><input id="password" type="password" name="password" size="32" maxlength="<?php echo \Core\Auth::get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="password-confirm" <?php echo $t_force_pw_reset_html ?>><span><?php echo \Core\Lang::get( 'confirm_password' ) ?></span></label>
				<span class="input"><input id="password-confirm" type="password" name="password_confirm" size="32" maxlength="<?php echo \Core\Auth::get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<?php } ?>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'email' ) ?></span></span>
				<span class="input"><?php
				if( $t_ldap && ON == \Core\Config::mantis_get( 'use_ldap_email' ) ) {
					# With LDAP
					echo '<span class="field-value">' . \Core\String::display_line( $u_email ) . '</span>';
				} else {
					# Without LDAP
					$t_show_update_button = true;
					\Core\Print_Util::email_input( 'email', $u_email );
				} ?>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container"><?php
				if( $t_ldap && ON == \Core\Config::mantis_get( 'use_ldap_realname' ) ) {
					# With LDAP
					echo '<span class="display-label"><span>' . \Core\Lang::get( 'realname' ) . '</span></span>';
					echo '<span class="input">';
					echo '<span class="field-value">';
					echo \Core\String::display_line( \Core\LDAP::realname_from_username( $u_username ) );
					echo '</span>';
					echo '</span>';
				} else {
					# Without LDAP
					$t_show_update_button = true;
					echo '<label for="realname"><span>' . \Core\Lang::get( 'realname' ) . '</span></label>';
					echo '<span class="input">';
					echo '<input id="realname" type="text" size="32" maxlength="' . DB_FIELD_SIZE_REALNAME . '" name="realname" value="' . \Core\String::attribute( $u_realname ) . '" />';
					echo '</span>';
				} ?>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'access_level' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo \Core\Helper::get_enum_element( 'access_levels', $u_access_level ); ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'access_level_project' ) ?></span></span>
				<span class="input"><span class="field-value"><?php echo \Core\Helper::get_enum_element( 'access_levels', \Core\Current_User::get_access_level() ); ?></span></span>
				<span class="label-style"></span>
			</div>
			<?php
			$t_projects = \Core\User::get_assigned_projects( \Core\Auth::get_current_user_id() );
			if( count( $t_projects ) > 0 ) {
				echo '<div class="field-container">';
				echo '<span class="display-label"><span>' . \Core\Lang::get( 'assigned_projects' ) . '</span></span>';
				echo '<div class="input">';
				echo '<ul class="project-list">';
				foreach( $t_projects as $t_project_id=>$t_project ) {
					$t_project_name = \Core\String::attribute( $t_project['name'] );
					$t_view_state = $t_project['view_state'];
					$t_access_level = $t_project['access_level'];
					$t_access_level = \Core\Helper::get_enum_element( 'access_levels', $t_access_level );
					$t_view_state = \Core\Helper::get_enum_element( 'project_view_state', $t_view_state );

					echo '<li><span class="project-name">' . $t_project_name . '</span> <span class="access-level">' . $t_access_level . '</span> <span class="view-state">' . $t_view_state . '</span></li>';
				}
				echo '</ul>';
				echo '</div>';
				echo '<span class="label-style"></span>';
				echo '</div>';
			}
			?>
	<?php if( $t_show_update_button ) { ?>
		<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'update_user_button' ) ?>" /></span>
	<?php } ?>
		</fieldset>
	</form>
</div>
<?php # check if users can't delete their own accounts
if( ON == \Core\Config::mantis_get( 'allow_account_delete' ) ) { ?>

<!-- Delete Button -->
<div class="form-container">
	<form method="post" action="account_delete.php">
		<fieldset>
			<?php echo \Core\Form::security_field( 'account_delete' ) ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'delete_account_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
}
\Core\HTML::page_bottom();
