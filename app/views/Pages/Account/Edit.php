<?php
use Core\Access;
use Core\App;
use Core\Auth;
use Core\Category;
use Core\Config;
use Core\Current_User;
use Core\Form;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Menu;
use Core\Print_Util;
use Core\String;
use Core\Template;
use Core\URL;
use Core\User;
use Core\Utility;

$this->layout('Layouts/Master', $this->data);
		

# extracts the user information for the currently logged in user
# and prefixes it with u_
$t_row = User::get_row( Auth::get_current_user_id() );

extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_ldap = ( LDAP == Config::mantis_get( 'login_method' ) );

# In case we're using LDAP to get the email address... this will pull out
#  that version instead of the one in the DB
$u_email = User::get_email( $u_id );

# If the password is the default password, then prompt user to change it.
$t_reset_password = $u_username == 'administrator' && Auth::does_password_match( $u_id, 'root' );

# note if we are being included by a script of a different name, if so,
# this is a mandatory password change request
$t_verify = Utility::is_page_name( 'verify.php' );

$t_force_pw_reset = false;

if( $t_verify || $t_reset_password ) {
	$t_can_change_password = Helper::call_custom_function( 'auth_can_change_password', array() );

	echo '<div id="reset-passwd-msg" class="important-msg">';
	echo '<ul>';

	if( $t_verify ) {
		echo '<li>' . Lang::get( 'verify_warning' ) . '</li>';

		if( $t_can_change_password ) {
			echo '<li>' . Lang::get( 'verify_change_password' ) . '</li>';
			$t_force_pw_reset = true;
		}
	} else if( $t_reset_password && $t_can_change_password ) {
		echo '<li>' . Lang::get( 'warning_default_administrator_account_present' ) . '</li>';
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

<header class="page-title">
	<?php $this->insert('Partials/Menu', array('items' => Menu::account())); ?>
	<h2><?php echo Lang::get( 'edit_account_title' ); ?></h2>
</header>

<form method="post" action="<?php echo URL::get('account'); ?>">
	<fieldset <?php echo $t_force_pw_reset_html ?>>
	
		<?php echo Form::security_field( 'account_update' );

		if( !Helper::call_custom_function( 'auth_can_change_password', array() ) ) {
			# With LDAP -->
		?>
		<div class="field-container">
			<div class="field-label"><div><?php echo Lang::get( 'username' ) ?></div></div>
			<div class="field-input"><div class="field-value"><?php echo String::display_line( $u_username ) ?></div></div>
		</div>
		<div class="field-container">
			<div class="field-label"><div><?php echo Lang::get( 'password' ) ?></div></div>
			<div class="field-input"><div class="field-value"><?php echo Lang::get( 'no_password_change' ) ?></div></div>
		</div><?php
		} else {
			# Without LDAP
			$t_show_update_button = true;
		?>
		<div class="field-container">
			<div class="field-label"><div><?php echo Lang::get( 'username' ) ?></div></div>
			<div class="field-input"><div class="field-value"><?php echo String::display_line( $u_username ) ?></div></div>
		</div><?php
		# When verifying account, set a token and don't display current password
		if( $t_account_verification ) {
			Token::set( TOKEN_ACCOUNT_VERIFY, true, TOKEN_EXPIRY_AUTHENTICATED, $u_id );
		} else {
		?>
		<div class="field-container">
			<label class="field-label" for="password" <?php echo $t_force_pw_reset_html ?>><?php echo Lang::get( 'current_password' ) ?></label>
			<div class="field-input"><input id="password-current" type="password" name="password_current" size="32" maxlength="<?php echo Auth::get_password_max_size(); ?>" /></div>
		</div>
		<?php } ?>
		<div class="field-container">
			<label class="field-label" for="password" <?php echo $t_force_pw_reset_html ?>><?php echo Lang::get( 'password' ) ?></label>
			<div class="field-input"><input id="password" type="password" name="password" size="32" maxlength="<?php echo Auth::get_password_max_size(); ?>" /></div>
		</div>
		<div class="field-container">
			<label class="field-label" for="password-confirm" <?php echo $t_force_pw_reset_html ?>><?php echo Lang::get( 'confirm_password' ) ?></label>
			<div class="field-input"><input id="password-confirm" type="password" name="password_confirm" size="32" maxlength="<?php echo Auth::get_password_max_size(); ?>" /></div>
		</div>
		<?php } ?>
		<div class="field-container">
			<div class="field-label"><div><?php echo Lang::get( 'email' ) ?></div></div>
			<div class="field-input"><?php
			if( $t_ldap && ON == Config::mantis_get( 'use_ldap_email' ) ) {
				# With LDAP
				echo '<div class="field-value">' . String::display_line( $u_email ) . '</div>';
			} else {
				# Without LDAP
				$t_show_update_button = true;
				Print_Util::email_input( 'email', $u_email );
			} ?>
			</div>
		</div>
		<div class="field-container"><?php
			if( $t_ldap && ON == Config::mantis_get( 'use_ldap_realname' ) ) {
				# With LDAP
				echo '<div class="field-label">' . Lang::get( 'realname' ) . '</div>';
				echo '<div class="field-input">';
				echo '<div class="field-value">';
				echo String::display_line( LDAP::realname_from_username( $u_username ) );
				echo '</div>';
				echo '</div>';
			} else {
				# Without LDAP
				$t_show_update_button = true;
				echo '<label class="field-label" for="realname"><div>' . Lang::get( 'realname' ) . '</div></label>';
				echo '<div class="field-input">';
				echo '<input id="realname" type="text" size="32" maxlength="' . DB_FIELD_SIZE_REALNAME . '" name="realname" value="' . String::attribute( $u_realname ) . '" />';
				echo '</div>';
			} ?>
		</div>
		<div class="field-container">
			<div class="field-label"><div><?php echo Lang::get( 'access_level' ) ?></div></div>
			<div class="field-input"><div class="field-value"><?php echo Helper::get_enum_element( 'access_levels', $u_access_level ); ?></div></div>
		</div>
		<div class="field-container">
			<div class="field-label"><div><?php echo Lang::get( 'access_level_project' ) ?></div></div>
			<div class="field-input"><div class="field-value"><?php echo Helper::get_enum_element( 'access_levels', Current_User::get_access_level() ); ?></div></div>
		</div>
		<?php
		$t_projects = User::get_assigned_projects( Auth::get_current_user_id() );
		if( count( $t_projects ) > 0 ) {
			echo '<div class="field-container">';
			echo '<div class="field-label">' . Lang::get( 'assigned_projects' ) . '</div>';
			echo '<div class="field-input">';
			echo '<ul>';
			foreach( $t_projects as $t_project_id=>$t_project ) {
				$t_project_name = String::attribute( $t_project['name'] );
				$t_view_state = $t_project['view_state'];
				$t_access_level = $t_project['access_level'];
				$t_access_level = Helper::get_enum_element( 'access_levels', $t_access_level );
				$t_view_state = Helper::get_enum_element( 'project_view_state', $t_view_state );

				echo '<li><strong class="project-name">' . $t_project_name . '</strong> <span class="access-level">' . $t_access_level . '</span> <span class="view-state">' . $t_view_state . '</span></li>';
			}
			echo '</ul>';
			echo '</div>';
			echo '</div>';
		}
		?>
<?php if( $t_show_update_button ) { ?>
	<div class="field-submit"><input type="submit" class="button" value="<?php echo Lang::get( 'update_user_button' ) ?>" /></div>
<?php } ?>
	</fieldset>
</form>

<?php # check if users can't delete their own accounts
if( ON == Config::mantis_get( 'allow_account_delete' ) ) { ?>

<!-- Delete Button -->
<div class="form-container">
	<form method="post" action="account_delete.php">
		<fieldset>
			<?php echo Form::security_field( 'account_delete' ) ?>
			<div class="submit-button"><input type="submit" class="button" value="<?php echo Lang::get( 'delete_account_button' ) ?>" /></div>
		</fieldset>
	</form>
</div>
<?php
}