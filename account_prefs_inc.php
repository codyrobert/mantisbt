<header class="page-title">
	<?php $engine = \Core\Template::engine(); ?>
	<?php echo $engine->render('Partials/Menu', array('items' => \Core\Menu::account())); ?>
	<h2><?php echo \Core\Lang::get( 'default_account_preferences_title' ); ?></h2>
</header>

<div id="account-prefs-update-div" class="form-container">
	<form id="account-prefs-update-form" method="post" action="account_prefs_update.php">
		<fieldset>
			<?php echo \Core\Form::security_field( 'account_prefs_update' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
			<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
			
			<div class="field-container">
				<label for="default-project-id"><span><?php echo \Core\Lang::get( 'default_project' ) ?></span></label>
				<span class="select">
					<select id="default-project-id" name="default_project">
<?php
	# Count number of available projects
	$t_projects = \Core\Current_User::get_accessible_projects();
	$t_num_proj = count( $t_projects );
	if( $t_num_proj == 1 ) {
		$t_num_proj += count( \Core\Current_User::get_accessible_subprojects( $t_projects[0] ) );
	}
	# Don't display "All projects" in selection list if there is only 1
	\Core\Print_Util::project_option_list( (int)$t_pref->default_project, $t_num_proj != 1 );
?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="refresh-delay"><span><?php echo \Core\Lang::get( 'refresh_delay' ) ?></span></label>
				<span class="input"><input id="refresh-delay" type="text" name="refresh_delay" size="4" maxlength="4" value="<?php echo $t_pref->refresh_delay ?>" /> <?php echo \Core\Lang::get( 'minutes' ) ?></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="redirect-delay"><span><?php echo \Core\Lang::get( 'redirect_delay' ) ?></span></label>
				<span class="input"><input id="redirect-delay" type="text" name="redirect_delay" size="4" maxlength="3" value="<?php echo $t_pref->redirect_delay ?>" /> <?php echo \Core\Lang::get( 'seconds' ) ?></span>
				<span class="label-style"></span>
			</div>
			<fieldset class="field-container">
				<legend><span><?php echo \Core\Lang::get( 'bugnote_order' ) ?></span></legend>
				<span class="radio"><input id="bugnote-order-desc" type="radio" name="bugnote_order" value="DESC" <?php \Core\Helper::check_checked( $t_pref->bugnote_order, 'DESC' ); ?> /></span>
				<label for="bugnote-order-desc"><span><?php echo \Core\Lang::get( 'bugnote_order_desc' ) ?></span></label>
				<span class="radio"><input id="bugnote-order-asc" type="radio" name="bugnote_order" value="ASC" <?php \Core\Helper::check_checked( $t_pref->bugnote_order, 'ASC' ); ?> /></span>
				<label for="bugnote-order-asc"><span><?php echo \Core\Lang::get( 'bugnote_order_asc' ) ?></span></label>
				<span class="label-style"></span>
			</fieldset>
			<?php if( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) { ?>
			<fieldset class="field-container">
				<legend><label for="email-on-new"><?php echo \Core\Lang::get( 'email_on_new' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-new" type="checkbox" name="email_on_new" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_new, ON ); ?> /></span>
				<label for="email-on-new-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-new-min-severity" name="email_on_new_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_new_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-assigned"><?php echo \Core\Lang::get( 'email_on_assigned' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-assigned" type="checkbox" name="email_on_assigned" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_assigned, ON ); ?> /></span>
				<label for="email-on-assigned-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-assigned-min-severity" name="email_on_assigned_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_assigned_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-feedback"><?php echo \Core\Lang::get( 'email_on_feedback' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-feedback" type="checkbox" name="email_on_feedback" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_feedback, ON ); ?> /></span>
				<label for="email-on-feedback-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-feedback-min-severity" name="email_on_feedback_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_feedback_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-resolved"><?php echo \Core\Lang::get( 'email_on_resolved' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-resolved" type="checkbox" name="email_on_resolved" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_resolved, ON ); ?> /></span>
				<label for="email-on-resolved-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-resolved-min-severity" name="email_on_resolved_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_resolved_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-closed"><?php echo \Core\Lang::get( 'email_on_closed' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-closed" type="checkbox" name="email_on_closed" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_closed, ON ); ?> /></span>
				<label for="email-on-closed-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-closed-min-severity" name="email_on_closed_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_closed_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-reopened"><?php echo \Core\Lang::get( 'email_on_reopened' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-reopened" type="checkbox" name="email_on_reopened" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_reopened, ON ); ?> /></span>
				<label for="email-on-reopened-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-reopened-min-severity" name="email_on_reopened_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_reopened_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-bugnote-added"><?php echo \Core\Lang::get( 'email_on_bugnote_added' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-bugnote-added" type="checkbox" name="email_on_bugnote" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_bugnote, ON ); ?> /></span>
				<label for="email-on-bugnote-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-bugnote-min-severity" name="email_on_bugnote_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_bugnote_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-status"><?php echo \Core\Lang::get( 'email_on_status_change' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-status" type="checkbox" name="email_on_status" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_status, ON ); ?> /></span>
				<label for="email-on-status-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-status-min-severity" name="email_on_status_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_status_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<fieldset class="field-container">
				<legend><label for="email-on-priority-change"><?php echo \Core\Lang::get( 'email_on_priority_change' ) ?></label></legend>
				<span class="checkbox"><input id="email-on-priority-change" type="checkbox" name="email_on_priority" <?php \Core\Helper::check_checked( (int)$t_pref->email_on_priority, ON ); ?> /></span>
				<label for="email-on-priority-min-severity" class="email-on-severity-label"><span><?php echo \Core\Lang::get( 'with_minimum_severity' ) ?></span></label>
				<span class="select email-on-severity">
					<select id="email-on-priority-min-severity" name="email_on_priority_min_severity">
						<option value="<?php echo OFF ?>"><?php echo \Core\Lang::get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php \Core\Print_Util::enum_string_option_list( 'severity', (int)$t_pref->email_on_priority_min_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</fieldset>
			<div class="field-container">
				<label for="email-bugnote-limit"><span><?php echo \Core\Lang::get( 'email_bugnote_limit' ) ?></span></label>
				<span class="input"><input id="email-bugnote-limit" type="text" name="email_bugnote_limit" maxlength="2" size="2" value="<?php echo $t_pref->email_bugnote_limit ?>" /></span>
				<span class="label-style"></span>
			</div>
<?php } else { ?>
			<input type="hidden" name="email_on_new"      value="<?php echo $t_pref->email_on_new ?>" />
			<input type="hidden" name="email_on_assigned" value="<?php echo $t_pref->email_on_assigned ?>" />
			<input type="hidden" name="email_on_feedback" value="<?php echo $t_pref->email_on_feedback ?>" />
			<input type="hidden" name="email_on_resolved" value="<?php echo $t_pref->email_on_resolved ?>" />
			<input type="hidden" name="email_on_closed"   value="<?php echo $t_pref->email_on_closed ?>" />
			<input type="hidden" name="email_on_reopened" value="<?php echo $t_pref->email_on_reopened ?>" />
			<input type="hidden" name="email_on_bugnote"  value="<?php echo $t_pref->email_on_bugnote ?>" />
			<input type="hidden" name="email_on_status"   value="<?php echo $t_pref->email_on_status ?>" />
			<input type="hidden" name="email_on_priority" value="<?php echo $t_pref->email_on_priority ?>" />
			<input type="hidden" name="email_on_new_min_severity"      value="<?php echo $t_pref->email_on_new_min_severity ?>" />
			<input type="hidden" name="email_on_assigned_min_severity" value="<?php echo $t_pref->email_on_assigned_min_severity ?>" />
			<input type="hidden" name="email_on_feedback_min_severity" value="<?php echo $t_pref->email_on_feedback_min_severity ?>" />
			<input type="hidden" name="email_on_resolved_min_severity" value="<?php echo $t_pref->email_on_resolved_min_severity ?>" />
			<input type="hidden" name="email_on_closed_min_severity"   value="<?php echo $t_pref->email_on_closed_min_severity ?>" />
			<input type="hidden" name="email_on_reopened_min_severity" value="<?php echo $t_pref->email_on_reopened_min_severity ?>" />
			<input type="hidden" name="email_on_bugnote_min_severity"  value="<?php echo $t_pref->email_on_bugnote_min_severity ?>" />
			<input type="hidden" name="email_on_status_min_severity"   value="<?php echo $t_pref->email_on_status_min_severity ?>" />
			<input type="hidden" name="email_on_priority_min_severity" value="<?php echo $t_pref->email_on_priority_min_severity ?>" />
			<input type="hidden" name="email_bugnote_limit" value="<?php echo $t_pref->email_bugnote_limit ?>" />
<?php } ?>
			<div class="field-container">
				<label for="timezone"><span><?php echo \Core\Lang::get( 'timezone' ) ?></span></label>
				<span class="select">
					<select id="timezone" name="timezone">
						<?php \Core\Print_Util::timezone_option_list( $t_pref->timezone ?  $t_pref->timezone  : \Core\Config::get_global( 'default_timezone' ) ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="language"><span><?php echo \Core\Lang::get( 'language' ) ?></span></label>
				<span class="select">
					<select id="language" name="language">
						<?php \Core\Print_Util::language_option_list( $t_pref->language ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>

			<?php \Core\Event::signal( 'EVENT_ACCOUNT_PREF_UPDATE_FORM', array( $p_user_id ) ); ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'update_prefs_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

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