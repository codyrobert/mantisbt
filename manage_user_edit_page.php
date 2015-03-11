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
 * User Edit Page
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
 * @uses error_api.php
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

\Core\Auth::reauthenticate();

\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) );

$f_username = \Core\GPC::get_string( 'username', '' );

if( \Core\Utility::is_blank( $f_username ) ) {
	$t_user_id = \Core\GPC::get_int( 'user_id' );
} else {
	$t_user_id = \Core\User::get_id_by_name( $f_username );
	if( $t_user_id === false ) {
		# If we can't find the user by name, attempt to find by email.
		$t_user_id = \Core\User::get_id_by_email( $f_username );
		if( $t_user_id === false ) {
			\Core\Error::parameters( $f_username );
			trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, ERROR );
		}
	}
}

$t_user = \Core\User::get_row( $t_user_id );

# Ensure that the account to be updated is of equal or lower access to the
# current user.
\Core\Access::ensure_global_level( $t_user['access_level'] );

$t_ldap = ( LDAP == \Core\Config::mantis_get( 'login_method' ) );

\Core\HTML::page_top();

\Core\HTML::print_manage_menu();
?>

<!-- USER INFO -->
<div id="edit-user-div" class="form-container">
	<form id="edit-user-form" method="post" action="manage_user_update.php">
		<fieldset>
			<legend><span><?php echo \Core\Lang::get( 'edit_user_title' ) ?></span></legend>
			<?php echo \Core\Form::security_field( 'manage_user_update' ) ?>
			<!-- Title -->
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />

			<!-- Username -->
			<div class="field-container">
				<label for="edit-username"><span><?php echo \Core\Lang::get( 'username_label' ) ?></span></label>
				<span class="input"><input id="edit-username" type="text" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" name="username" value="<?php echo \Core\String::attribute( $t_user['username'] ) ?>" /></span>
				<span class="label-style"></span>
			</div>

			<!-- Realname -->
			<div class="field-container"><?php
			if( $t_ldap && ON == \Core\Config::mantis_get( 'use_ldap_realname' ) ) {
				# With LDAP
				echo '<span class="display-label"><span>' . \Core\Lang::get( 'realname_label' ) . '</span></span>';
				echo '<span class="input">';
				echo \Core\String::display_line( \Core\User::get_realname( $t_user_id ) );
				echo '</span>';
			} else {
				# Without LDAP ?>
				<label for="edit-realname"><span><?php echo \Core\Lang::get( 'realname_label' ) ?></span></label>
				<span class="input"><input id="edit-realname" type="text" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" name="realname" value="<?php echo \Core\String::attribute( $t_user['realname'] ) ?>" /></span><?php
			}
		?>
				<span class="label-style"></span>
			</div>
			<!-- Email -->
			<div class="field-container"><?php
			if( $t_ldap && ON == \Core\Config::mantis_get( 'use_ldap_email' ) ) {
				# With LDAP
				echo '<span class="display-label"><span>' . \Core\Lang::get( 'email_label' ) . '</span></span>';
				echo '<span class="input">' . \Core\String::display_line( \Core\User::get_email( $t_user_id ) ) . '</span>';
			} else {
				# Without LDAP
				echo '<label for="email-field"><span>' . \Core\Lang::get( 'email_label' ) . '</span></label>';
				echo '<span class="input">';
				\Core\Print_Util::email_input( 'email', $t_user['email'] );
				echo '</span>';
			} ?>
				<span class="label-style"></span>
			</div>
			<!-- Access Level -->
			<div class="field-container">
				<label for="edit-access-level"><span><?php echo \Core\Lang::get( 'access_level_label' ) ?></span></label>
				<span class="select">
					<select id="edit-access-level" name="access_level"><?php
						$t_access_level = $t_user['access_level'];
						if( !\Core\MantisEnum::hasValue( \Core\Config::mantis_get( 'access_levels_enum_string' ), $t_access_level ) ) {
							$t_access_level = \Core\Config::mantis_get( 'default_new_account_access_level' );
						}
						\Core\Print_Util::project_access_levels_option_list( (int)$t_access_level ); ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<!-- Enabled Checkbox -->
			<div class="field-container">
				<label for="edit-enabled"><span><?php echo \Core\Lang::get( 'enabled_label' ) ?></span></label>
				<span class="checkbox"><input id="edit-enabled" type="checkbox" name="enabled" <?php \Core\Helper::check_checked( (int)$t_user['enabled'], ON ); ?> /></span>
				<span class="label-style"></span>
			</div>
			<!-- Protected Checkbox -->
			<div class="field-container">
				<label for="edit-protected"><span><?php echo \Core\Lang::get( 'protected_label' ) ?></span></label>
				<span class="checkbox"><input id="edit-protected" type="checkbox" name="protected" <?php \Core\Helper::check_checked( (int)$t_user['protected'], ON ); ?> /></span>
				<span class="label-style"></span>
			</div><?php
			if( \Core\Config::mantis_get( 'enable_email_notification' ) == ON ) {
				echo '<div class="field-container">';
				echo '<label for="send-email"><span>' . \Core\Lang::get( 'notify_user' ) . '</span></label>';
				echo '<span class="checkbox"><input id="send-email" type="checkbox" name="send_email_notification" checked="checked" /></span>';
				echo '<span class="label-style"></span>';
				echo '</div>';
			} ?>
			<!-- Submit Button -->
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'update_user_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
# User action buttons: RESET/UNLOCK and DELETE

$t_reset = $t_user['id'] != \Core\Auth::get_current_user_id()
	&& \Core\Helper::call_custom_function( 'auth_can_change_password', array() );
$t_unlock = OFF != \Core\Config::mantis_get( 'max_failed_login_count' ) && $t_user['failed_login_count'] > 0;
$t_delete = !( ( \Core\User::is_administrator( $t_user_id ) && ( \Core\User::count_level( \Core\Config::get_global( 'admin_site_threshold' ) ) <= 1 ) ) );

if( $t_reset || $t_unlock || $t_delete ) {
?>
<div id="manage-user-actions-div" class="form-container">

<!-- Reset/Unlock Button -->
<?php if( $t_reset || $t_unlock ) { ?>
	<form id="manage-user-reset-form" method="post" action="manage_user_reset.php" class="action-button">
		<fieldset>
			<?php echo \Core\Form::security_field( 'manage_user_reset' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
<?php	if( $t_reset ) { ?>
			<span><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'reset_password_button' ) ?>" /></span>
<?php	} else { ?>
			<span><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'account_unlock_button' ) ?>" /></span>
<?php	} ?>
		</fieldset>
	</form>
<?php } ?>

<!-- Delete Button -->
<?php if( $t_delete ) { ?>
	<form id="manage-user-delete-form" method="post" action="manage_user_delete.php" class="action-button">
		<fieldset>
			<?php echo \Core\Form::security_field( 'manage_user_delete' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
			<span><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'delete_user_button' ) ?>" /></span>
		</fieldset>
	</form>
<?php } ?>
</div>
<?php } ?>

<?php if( $t_reset ) { ?>
<div class="important-msg">
<?php
	if( ( ON == \Core\Config::mantis_get( 'send_reset_password' ) ) && ( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
		echo \Core\Lang::get( 'reset_password_msg' );
	} else {
		echo \Core\Lang::get( 'reset_password_msg2' );
	}
?>
</div>
<?php } ?>

<!-- PROJECT ACCESS (if permissions allow) and user is not ADMINISTRATOR -->
<?php if( \Core\Access::has_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) ) &&
	!\Core\User::is_administrator( $t_user_id ) ) {
?>
<div class="form-container">
	<h2><?php echo \Core\Lang::get( 'add_user_title' ) ?></h2>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Core\Lang::get( 'assigned_projects_label' ) ?></span></span>
		<div class="input"><?php \Core\Print_Util::project_user_list( $t_user['id'] ) ?></div>
		<span class="label-style"></span>
	</div>
	<form id="manage-user-project-add-form" method="post" action="manage_user_proj_add.php">
		<fieldset>
			<?php echo \Core\Form::security_field( 'manage_user_proj_add' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
			<div class="field-container">
				<label for="add-user-project-id"><span><?php echo \Core\Lang::get( 'unassigned_projects_label' ) ?></span></label>
				<span class="select">
					<select id="add-user-project-id" name="project_id[]" multiple="multiple" size="5">
						<?php \Core\Print_Util::project_user_list_option_list2( $t_user['id'] ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="add-user-project-access"><span><?php echo \Core\Lang::get( 'access_level_label' ) ?></span></label>
				<span class="select">
					<select id="add-user-project-access" name="access_level">
						<?php \Core\Print_Util::project_access_levels_option_list( (int)\Core\Config::mantis_get( 'default_new_account_access_level' ) ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'add_user_button' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php
} # End of PROJECT ACCESS conditional section

define( 'ACCOUNT_PREFS_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/account_prefs_inc.php' );
edit_account_prefs( $t_user['id'], false, false, 'manage_user_edit_page.php?user_id=' . $t_user_id );

\Core\HTML::page_bottom();
