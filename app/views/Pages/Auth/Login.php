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

$this->layout('Layouts/Auth', $this->data);
?>

<header class="page-title">
	<h2><?php echo Lang::get( 'login_title' ) ?></h2>
</header>

<form method="post" action="<?php echo URL::get('login'); ?>">

	<?php if ($error): ?>
	<p class="error"><?php echo $error; ?>
	<?php endif; ?>

	<fieldset>
		<?php
		/*if( !Utility::is_blank( $f_return ) ) {
			echo '<input type="hidden" name="return" value="', String::html_specialchars( $f_return ), '" />';
		}

		if( $t_upgrade_required ) {
			echo '<input type="hidden" name="install" value="true" />';
		}*/
		?>
		
		<div class="field-container">
			<label class="field-label" for="username"><div><?php echo Lang::get( 'username' ) ?></div></label>
			<div class="field-input"><input id="username" type="text" name="username" /></div>
		</div>
		<div class="field-container">
			<label class="field-label" for="password"><div><?php echo Lang::get( 'password' ) ?></div></label>
			<div class="field-input"><input id="password" type="password" name="password" /></div>
		</div>
		<?php if( ON == Config::mantis_get( 'allow_permanent_cookie' ) ) { ?>
		<div class="field-container">
			<label class="field-label" for="remember-login"><div><?php echo Lang::get( 'save_login' ) ?></div></label>
			<div class="field-input"><input id="remember-login" type="checkbox" name="perm_login" <?php echo ( $f_perm_login ? 'checked="checked" ' : '' ) ?>/></div>
		</div>
		<?php } ?>
		<?php if( $t_session_validation ) { ?>
		<div class="field-container">
			<label class="field-label" id="secure-session-label" for="secure-session"><div><?php echo Lang::get( 'secure_session' ) ?></div></label>
			<div class="field-input">
				<input id="secure-session" type="checkbox" name="secure_session" <?php echo ( $t_default_secure_session ? 'checked="checked" ' : '' ) ?>/>
				<div id="session-msg"><?php echo Lang::get( 'secure_session_long' ); ?></div>
			</div>
		</div>
		<?php } ?>
	
	</fieldset>

	<div class="field-submit">
	
		<?php
		if( ( ON == Config::get_global( 'allow_signup' ) ) &&
			( LDAP != Config::get_global( 'login_method' ) ) &&
			( ON == Config::mantis_get( 'enable_email_notification' ) )
		) {
			echo '<a href="'.URL::get('signup').'">', Lang::get( 'signup_link' ), '</a>';
		}
		
		# lost password feature disabled or reset password via email disabled -> stop here!
		if( ( LDAP != Config::get_global( 'login_method' ) ) &&
			( ON == Config::mantis_get( 'lost_password_feature' ) ) &&
			( ON == Config::mantis_get( 'send_reset_password' ) ) &&
			( ON == Config::mantis_get( 'enable_email_notification' ) ) ) {
			echo '<a href="'.URL::get('lost_password').'">', Lang::get( 'lost_password_link' ), '</a>';
		}
		?>
	
		<input type="submit" class="button" value="<?php echo Lang::get( 'login_button' ) ?>" />
	</div>
	
</form>
	
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