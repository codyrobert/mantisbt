<?php
use Core\Lang;
use Core\Menu;


$this->layout('Layouts/Master', $this->data);
?>

<header class="page-title">
	<?php $this->insert('Partials/Menu', array('items' => Menu::account())); ?>
	<h2><?php echo Lang::get('default_account_preferences_title'); ?></h2>
</header>

<?php $this->insert('Partials/Forms/User/Preferences'); ?>

<div id="account-prefs-reset-div" class="form-container">
	<form id="account-prefs-reset-form" method="post" action="account_prefs_reset.php">
		<fieldset>
			<?php echo \Core\Form::security_field( 'account_prefs_reset' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
			<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'reset_prefs_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>