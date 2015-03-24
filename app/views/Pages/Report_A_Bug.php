<?php
use Core\Lang;

$this->layout('Layouts/Master', $this->data);
?>

<header class="page-title">
	<h2><?php echo Lang::get( 'enter_report_details_title' ); ?></h2>
</header>


<form id="report-bug-form" method="post" <?php echo $t_form_encoding; ?> action="bug_report.php?posted=1">
	<fieldset class="has-required">
	
		<?php echo \Core\Form::security_field( 'bug_report' ) ?>
		<input type="hidden" name="m_id" value="<?php echo $f_master_bug_id ?>" />
		<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />

		<?php
		\Core\Event::signal( 'EVENT_REPORT_BUG_FORM_TOP', array( $t_project_id ) );

		if( $t_show_category ) {
		?>
		<div class="field-container">
			<label class="field-label"><?php
				echo \Core\Config::mantis_get( 'allow_no_category' ) ? '' : '<span class="required">*</span>';
				\Core\Print_Util::documentation_link( 'category' );
			?></label>
			<div class="field-input">
				<?php if( $t_changed_project ) {
					echo '[' . \Core\Project::get_field( $t_bug->project_id, 'name' ) . '] ';
				} ?>
					<select <?php echo \Core\Helper::get_tab_index() ?> id="category_id" name="category_id" class="autofocus">
				<?php
				\Core\Print_Util::category_option_list( $f_category_id );
				?>
				</select>
			</div>
		</div>
<?php }

if( $t_show_reproducibility ) {
?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'reproducibility' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="reproducibility" name="reproducibility">
					<?php \Core\Print_Util::enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
				</select>
			</div>
		</div>
<?php
}

if( $t_show_eta ) {
?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'eta' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="eta" name="eta">
					<?php \Core\Print_Util::enum_string_option_list( 'eta', $f_eta ) ?>
				</select>
			</div>
		</div>
<?php
}

if( $t_show_severity ) {
?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'severity' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="severity" name="severity">
					<?php \Core\Print_Util::enum_string_option_list( 'severity', $f_severity ) ?>
				</select>
			</div>
		</div>
<?php
}

if( $t_show_priority ) {
?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'priority' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="priority" name="priority">
					<?php \Core\Print_Util::enum_string_option_list( 'priority', $f_priority ) ?>
				</select>
			</div>
		</div>
<?php
}

if( $t_show_due_date ) {
	$t_date_to_display = '';

	if( !\Core\Date::is_null( $f_due_date ) ) {
		$t_date_to_display = date( \Core\Config::mantis_get( 'calendar_date_format' ), $f_due_date );
	}
?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'due_date' ) ?></label>
			<div class="field-input">
				<?php echo '<input ' . \Core\Helper::get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetime" size="20" maxlength="16" value="' . $t_date_to_display . '" />' ?>
			</div>
		</div>
	<?php } ?>
	<?php if( $t_show_platform || $t_show_os || $t_show_os_version ) { ?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'select_profile' ) ?></label>
			<div class="field-input">
				<?php if( count( \Core\Profile::get_all_for_user( \Core\Auth::get_current_user_id() ) ) > 0 ) { ?>
					<select <?php echo \Core\Helper::get_tab_index() ?> id="profile_id" name="profile_id">
						<?php \Core\Print_Util::profile_option_list( \Core\Auth::get_current_user_id(), $f_profile_id ) ?>
					</select>
				<?php } ?>

				<?php \Core\Collapse::icon( 'profile' ); ?>
				<?php echo \Core\Lang::get( 'or_fill_in' ); ?>
			</div>
		</div>

		<?php \Core\Collapse::open( 'profile' ); ?>
			<div class="field-container">
				<label class="field-label"><?php echo \Core\Lang::get( 'platform' ) ?></label>
				<div class="field-input">
					<?php if( \Core\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
					<select id="platform" name="platform">
						<option value=""></option>
						<?php \Core\Print_Util::platform_option_list( $f_platform ); ?>
					</select>
					<?php
						} else {
							echo '<input type="text" id="platform" name="platform" class="autocomplete" size="32" maxlength="32" tabindex="' . \Core\Helper::get_tab_index_value() . '" value="' . \Core\String::attribute( $f_platform ) . '" />';
						}
					?>
				</div>
			</div>
			<div class="field-container">
				<label class="field-label"><?php echo \Core\Lang::get( 'os' ) ?></label>
				<div class="field-input">
					<?php if( \Core\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
					<select id="os" name="os">
						<option value=""></option>
						<?php \Core\Print_Util::os_option_list( $f_os ); ?>
					</select>
					<?php
						} else {
							echo '<input type="text" id="os" name="os" class="autocomplete" size="32" maxlength="32" tabindex="' . \Core\Helper::get_tab_index_value() . '" value="' . \Core\String::attribute( $f_os ) . '" />';
						}
					?>
				</div>
			</div>
			<div class="field-container">
				<label class="field-label"><?php echo \Core\Lang::get( 'os_version' ) ?></label>
				<div class="field-input">
					<?php
					if( \Core\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
				?>
				<select id="os_build" name="os_build">
					<option value=""></option>
						<?php \Core\Print_Util::os_build_option_list( $f_os_build ); ?>
					</select>
				<?php
					} else {
						echo '<input type="text" id="os_build" name="os_build" class="autocomplete" size="16" maxlength="16" tabindex="' . \Core\Helper::get_tab_index_value() . '" value="' . \Core\String::attribute( $f_os_build ) . '" />';
					}
				?>
				</div>
			</div>
		<?php \Core\Collapse::closed( 'profile' );?>
		<?php \Core\Collapse::end( 'profile' ); ?>
<?php } ?>
<?php
if( $t_show_product_version ) {
	$t_product_version_released_mask = VERSION_RELEASED;

	if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
		$t_product_version_released_mask = VERSION_ALL;
	}
?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'product_version' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="product_version" name="product_version">
					<?php \Core\Print_Util::version_option_list( $f_product_version, $t_project_id, $t_product_version_released_mask ) ?>
				</select>
			</div>
		</div>
<?php
}
?>
<?php if( $t_show_product_build ) { ?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'product_build' ) ?></label>
			<div class="field-input">
				<input <?php echo \Core\Helper::get_tab_index() ?> type="text" id="build" name="build" size="32" maxlength="32" value="<?php echo \Core\String::attribute( $f_build ) ?>" />
			</div>
		</div>
<?php } ?>

<?php if( $t_show_handler ) { ?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'assign_to' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="handler_id" name="handler_id">
					<option value="0" selected="selected"></option>
					<?php \Core\Print_Util::assign_to_option_list( $f_handler_id ) ?>
				</select>
			</div>
		</div>
<?php } ?>

<?php if( $t_show_status ) { ?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'status' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> name="status">
				<?php
				$t_resolution_options = \Core\Print_Util::get_status_option_list(
					\Core\Access::get_project_level( $t_project_id ),
					\Core\Config::mantis_get( 'bug_submit_status' ),
					true,
					ON == \Core\Config::mantis_get( 'allow_reporter_close' ),
					$t_project_id );
				foreach ( $t_resolution_options as $t_key => $t_value ) {
				?>
					<option value="<?php echo $t_key ?>" <?php \Core\Helper::check_selected( $t_key, \Core\Config::mantis_get( 'bug_submit_status' ) ); ?> >
						<?php echo $t_value ?>
					</option>
				<?php } ?>
				</select>
			</div>
		</div>
<?php } ?>

<?php if( $t_show_resolution ) { ?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'resolution' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> name="resolution">
					<?php
					\Core\Print_Util::enum_string_option_list( 'resolution', \Core\Config::mantis_get( 'default_bug_resolution' ) );
					?>
				</select>
			</div>
		</div>
<?php } ?>

<?php # Target Version (if permissions allow)
if( $t_show_target_version ) { ?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'target_version' ) ?></label>
			<div class="field-input">
				<select <?php echo \Core\Helper::get_tab_index() ?> id="target_version" name="target_version">
					<?php \Core\Print_Util::version_option_list( '', null, VERSION_FUTURE ) ?>
				</select>
			</div>
		</div>
<?php } ?>
<?php \Core\Event::signal( 'EVENT_REPORT_BUG_FORM', array( $t_project_id ) ) ?>

		<div class="field-container">
			<label class="field-label"><span class="required">*</span><?php \Core\Print_Util::documentation_link( 'summary' ) ?></label>
			<div class="field-input">
				<input <?php echo \Core\Helper::get_tab_index() ?> type="text" id="summary" name="summary" size="105" maxlength="128" value="<?php echo \Core\String::attribute( $f_summary ) ?>" />
			</div>
		</div>

		<div class="field-container">
			<label class="field-label"><span class="required">*</span><?php \Core\Print_Util::documentation_link( 'description' ) ?></label>
			<div class="field-input">
				<textarea <?php echo \Core\Helper::get_tab_index() ?> id="description" name="description" cols="80" rows="10"><?php echo \Core\String::textarea( $f_description ) ?></textarea>
			</div>
		</div>

<?php if( $t_show_steps_to_reproduce ) { ?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'steps_to_reproduce' ) ?></label>
			<div class="field-input">
				<textarea <?php echo \Core\Helper::get_tab_index() ?> id="steps_to_reproduce" name="steps_to_reproduce" cols="80" rows="10"><?php echo \Core\String::textarea( $f_steps_to_reproduce ) ?></textarea>
			</div>
		</div>
<?php } ?>

<?php if( $t_show_additional_info ) { ?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'additional_information' ) ?></label>
			<div class="field-input">
				<textarea <?php echo \Core\Helper::get_tab_index() ?> id="additional_info" name="additional_info" cols="80" rows="10"><?php echo \Core\String::textarea( $f_additional_info ) ?></textarea>
			</div>
		</div>
<?php
}

$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	if( ( $t_def['display_report'] || $t_def['require_report']) && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
		$t_custom_fields_found = true;
?>
		<div class="field-container">
			<label class="field-label">
				<span>
					<?php if( $t_def['require_report'] ) { ?>
					<span class="required">*</span>
					<?php } ?>
					<?php if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) { ?>
						<label for="custom_field_<?php echo \Core\String::attribute( $t_def['id'] ) ?>"><?php echo \Core\String::display( \Core\Lang::get_defaulted( $t_def['name'] ) ) ?></label>
					<?php } else {
						echo \Core\String::display( \Core\Lang::get_defaulted( $t_def['name'] ) );
					} ?>
				</span>
			</label>
			<div class="field-input">
				<?php print_custom_field_input( $t_def, ( $f_master_bug_id === 0 ) ? null : $f_master_bug_id ) ?>
			</div>
		</div>
<?php
	}
} # foreach( $t_related_custom_field_ids as $t_id )
?>
<?php
# File Upload (if enabled)
if( $t_show_attachments ) {
	$t_max_file_size = (int)min( \Core\Utility::ini_get_number( 'upload_max_filesize' ), \Core\Utility::ini_get_number( 'post_max_size' ), \Core\Config::mantis_get( 'max_file_size' ) );
	$t_file_upload_max_num = max( 1, \Core\Config::mantis_get( 'file_upload_max_num' ) );
?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' ) ?><br />
				<?php echo \Core\Print_Util::max_filesize( $t_max_file_size ); ?>
			</label>
			<div class="field-input">
				<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
<?php
	# Display multiple file upload fields
	for( $i = 0; $i < $t_file_upload_max_num; $i++ ) {
?>
				<input <?php echo \Core\Helper::get_tab_index() ?> id="ufile[]" name="ufile[]" type="file" />
<?php
		if( $t_file_upload_max_num > 1 ) {
			echo '<br />';
		}
	}
}
?>
			</div>
		</div>
<?php
if( $t_show_view_state ) {
?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'view_status' ) ?></label>
			<div class="field-input">
				<label><input <?php echo \Core\Helper::get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php \Core\Helper::check_checked( $f_view_state, VS_PUBLIC ) ?> /> <?php echo \Core\Lang::get( 'public' ) ?></label>
				<label><input <?php echo \Core\Helper::get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php \Core\Helper::check_checked( $f_view_state, VS_PRIVATE ) ?> /> <?php echo \Core\Lang::get( 'private' ) ?></label>
			</div>
		</div>
<?php
}

# Relationship (in case of cloned bug creation...)
if( $f_master_bug_id > 0 ) {
?>
		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'relationship_with_parent' ) ?></label>
			<div class="field-input">
				<?php \Core\Relationship::list_box( \Core\Config::mantis_get( 'default_bug_relationship_clone' ), 'rel_type', false, true ) ?>
				<?php echo '<strong>' . \Core\Lang::get( 'bug' ) . ' ' . \Core\Bug::format_id( $f_master_bug_id ) . '</strong>' ?>
			</div>
		</div>

		<div class="field-container">
			<label class="field-label"><?php echo \Core\Lang::get( 'copy_from_parent' ) ?></label>
			<div class="field-input">
				<label><input <?php echo \Core\Helper::get_tab_index() ?> type="checkbox" id="copy_notes_from_parent" name="copy_notes_from_parent" <?php \Core\Helper::check_checked( $f_copy_notes_from_parent ) ?> /> <?php echo \Core\Lang::get( 'copy_notes_from_parent' ) ?></label>
				<label><input <?php echo \Core\Helper::get_tab_index() ?> type="checkbox" id="copy_attachments_from_parent" name="copy_attachments_from_parent" <?php \Core\Helper::check_checked( $f_copy_attachments_from_parent ) ?> /> <?php echo \Core\Lang::get( 'copy_attachments_from_parent' ) ?></label>
			</div>
		</div>
<?php
}
?>
		<div class="field-container">
			<label class="field-label"><?php \Core\Print_Util::documentation_link( 'report_stay' ) ?></label>
			<div class="field-input">
				<label><input <?php echo \Core\Helper::get_tab_index() ?> type="checkbox" id="report_stay" name="report_stay" <?php \Core\Helper::check_checked( $f_report_stay ) ?> /> <?php echo \Core\Lang::get( 'check_report_more_bugs' ) ?></label>
			</div>
		</div>

		<div class="field-submit">
			<input <?php echo \Core\Helper::get_tab_index() ?> type="submit" class="button" value="<?php echo \Core\Lang::get( 'submit_report_button' ) ?>" />
		</div>
	</fieldset>
</form>