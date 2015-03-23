<?php
use Core\Lang;
use Core\Menu;

$this->layout('Layouts/Master', $this->data);


if( !\Core\Config::mantis_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

if( isset( $g_global_profiles ) ) {
	$g_global_profiles = true;
} else {
	$g_global_profiles = false;
}

\Core\Current_User::ensure_unprotected();

if( $g_global_profiles ) {
	\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_global_profile_threshold' ) );
} else {
	\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'add_profile_threshold' ) );
}

if( $g_global_profiles ) {
	\Core\HTML::print_manage_menu( 'manage_prof_menu_page.php' );
}

if( $g_global_profiles ) {
	$t_user_id = ALL_USERS;
} else {
	$t_user_id = \Core\Auth::get_current_user_id();
}

# Add Profile Form BEGIN
?>
<header class="page-title">
	<?php $this->insert('Partials/Menu', array('items' => Menu::account())); ?>
	<h2><?php echo Lang::get( 'add_profile_title' ); ?></h2>
</header>



<div id="account-profile-div" class="form-container">
	<form id="account-profile-form" method="post" action="account_prof_update.php">
		<fieldset class="has-required">
			<?php  echo \Core\Form::security_field( 'profile_update' )?>
			<input type="hidden" name="action" value="add" />
			<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />
			
			<div class="field-container">
				<label for="platform" class="required"><span><?php echo \Core\Lang::get( 'platform' ) ?></span></label>
				<span class="input"><input id="platform" type="text" name="platform" size="32" maxlength="32" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="os" class="required"><span><?php echo \Core\Lang::get( 'os' ) ?></span></label>
				<span class="input"><input id="os" type="text" name="os" size="32" maxlength="32" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="os-version" class="required"><span><?php echo \Core\Lang::get( 'os_version' ) ?></span></label>
				<span class="input"><input id="os-version" type="text" name="os_build" size="16" maxlength="16" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="description"><span><?php echo \Core\Lang::get( 'additional_description' ) ?></span></label>
				<span class="textarea"><textarea id="description" name="description" cols="80" rows="8"></textarea></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'add_profile_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
	# Add Profile Form END
	# Edit or Delete Profile Form BEGIN

	$t_profiles = \Core\Profile::get_all_for_user( $t_user_id );
	if( $t_profiles ) {
?>

<div id="account-profile-update-div" class="form-container">
	<form id="account-profile-update-form" method="post" action="account_prof_update.php">
		<fieldset>
			<legend><span><?php echo \Core\Lang::get( 'edit_or_delete_profiles_title' ) ?></span></legend>
			<?php  echo \Core\Form::security_field( 'profile_update' )?>
			<div class="field-container">
				<label for="action-edit"><span><?php echo \Core\Lang::get( 'edit_profile' ) ?></span></label>
				<span class="input"><input id="action-edit" type="radio" name="action" value="edit" /></span>
				<span class="label-style"></span>
			</div>
<?php
	if( !$g_global_profiles ) {
?>
			<div class="field-container">
				<label for="action-default"><span><?php echo \Core\Lang::get( 'make_default' ) ?></span></label>
				<span class="input"><input id="action-default" type="radio" name="action" value="make_default" /></span>
				<span class="label-style"></span>
			</div>
<?php
	}
?>
			<div class="field-container">
				<label for="action-delete"><span><?php echo \Core\Lang::get( 'delete_profile' ) ?></span></label>
				<span class="input"><input id="action-delete" type="radio" name="action" value="delete" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="select-profile"><span><?php echo \Core\Lang::get( 'select_profile' ) ?></span></label>
				<span class="input">
					<select id="select-profile" name="profile_id">
						<?php \Core\Print_Util::profile_option_list( $t_user_id, '', $t_profiles ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'submit_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
}