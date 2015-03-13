<?php
use Core\Config;
use Core\Lang;
use Core\URL;

$this->layout('Layouts/Auth', $this->data);
?>

<header class="page-title">
	<h2><?php echo Lang::get( 'lost_password_title' ) ?></h2>
</header>

<form method="post" action="<?php echo URL::get('login'); ?>">
	
	<p><?php echo Lang::get( 'lost_password_info' ); ?></p>

	<?php if ($error): ?>
	<p class="error"><?php echo $error; ?>
	<?php endif; ?>

	<fieldset>
	
		<div class="field-container">
			<label class="field-label" for="username"><div><?php echo Lang::get( 'username' ) ?></div></label>
			<div class="field-input"><input id="username" type="text" name="username" /></div>
		</div>
		<div class="field-container">
			<label class="field-label" for="email"><div><?php echo Lang::get( 'email' ) ?></div></label>
			<div class="field-input"><input id="email" type="email" name="email" /></div>
		</div>
	
	</fieldset>

	<div class="field-submit">
	
		<?php
		echo '<a href="'.URL::get('login').'">', Lang::get( 'login_link' ), '</a>';
		
		if( ( ON == Config::get_global( 'allow_signup' ) ) &&
			( LDAP != Config::get_global( 'login_method' ) ) &&
			( ON == Config::mantis_get( 'enable_email_notification' ) )
		) {
			echo '<a href="'.URL::get('register').'">', Lang::get( 'signup_link' ), '</a>';
		}
		?>
	
		<input type="submit" class="button" value="<?php echo Lang::get( 'submit_button' ) ?>" />
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