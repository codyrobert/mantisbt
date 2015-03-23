<?php
use Core\Form;
use Core\Helper;
use Core\Lang;
use Core\Menu;


$this->layout('Layouts/Master', $this->data);

$t_columns = \Core\Columns::get_all( $project_id );
$t_all = implode( ', ', $t_columns );

$t_columns = Helper::get_columns_to_view( COLUMNS_TARGET_CSV_PAGE, false, $user_id );
$t_csv = implode( ', ', $t_columns );

$t_columns = Helper::get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE, false, $user_id );
$t_view_issues = implode( ', ', $t_columns );

$t_columns = Helper::get_columns_to_view( COLUMNS_TARGET_PRINT_PAGE, false, $user_id );
$t_print_issues = implode( ', ', $t_columns );

$t_columns = Helper::get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE, false, $user_id );
$t_excel = implode( ', ', $t_columns );
?>

<header class="page-title">
	<?php $this->insert('Partials/Menu', array('items' => Menu::account())); ?>
	<h2><?php echo $this->e($page_title); ?></h2>
</header>

<?php if (@$message): ?>
<p class="notice"><?php echo $this->e($message); ?></p>
<?php endif; ?>

<section>

<form method="post" action="manage_config_columns_set.php">
	<fieldset>
		
		<?php echo Form::security_field( 'manage_config_columns_set' ) ?>
		<input type="hidden" name="project_id" value="<?php echo $project_id ?>" />
		<input type="hidden" name="form_page" value="account" />

		<div class="field-container">
			<label class="field-label"><?php echo Lang::get( 'all_columns_title' )?></label>
			<div class="field-input"><textarea name="all_columns" readonly="readonly" cols="80" rows="5"><?php echo $t_all; ?></textarea></div>
		</div>

		<div class="field-container">
			<label class="field-label"><?php echo Lang::get( 'view_issues_columns_title' )?></label>
			<div class="field-input"><textarea name="view-issues-columns" cols="80" rows="5"><?php echo $t_view_issues; ?></textarea></div>
		</div>

		<div class="field-container">
			<label class="field-label"><?php echo Lang::get( 'print_issues_columns_title' )?></label>
			<div class="field-input"><textarea name="print_issues_columns" cols="80" rows="5"><?php echo $t_print_issues; ?></textarea></div>
		</div>

		<div class="field-container">
			<label class="field-label"><?php echo Lang::get( 'csv_columns_title' )?></label>
			<div class="field-input"><textarea name="csv_columns" cols="80" rows="5"><?php echo $t_csv; ?></textarea></div>
		</div>

		<div class="field-container">
			<label class="field-label"><?php echo Lang::get( 'excel_columns_title' )?></label>
			<div class="field-input"><textarea name="excel_columns" cols="80" rows="5"><?php echo $t_excel; ?></textarea></div>
		</div>
		
		<?php if($project_id == ALL_PROJECTS): ?>
		<div class="field-submit"><input type="submit" class="button" value="<?php echo Lang::get( 'update_columns_as_my_default' ) ?>" /></div>
		<?php else: ?>
		<div class="field-submit"><input type="submit" class="button" value="<?php echo Lang::get( 'update_columns_for_current_project' ) ?>" /></div>
		<?php endif; ?>
		
	</fieldset>
</form>
	
</section>

<section>

	<form method="post" action="manage_columns_copy.php">
		<fieldset>
		
			<?php echo Form::security_field( 'manage_columns_copy' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $project_id ?>" />
	
			<div class="field-container">
				<label class="field-label">Copy Columns</label>
				<div class="field-input"><select name="other_project_id"><?php \Core\Print_Util::project_option_list( null, true, $project_id ); ?></select></div>
			</div>
			
			<div class="field-submit">
				<input type="submit" class="button" name="copy_from" value="<?php echo Lang::get('copy_columns_from') ?>" />
				<input type="submit" class="button" name="copy_to" value="<?php echo Lang::get('copy_columns_to') ?>" />
			</div>
		
		</fieldset>
	</form>

</section>

<section>

	<form method="post">
		<fieldset>
			<?php echo Form::security_field( 'manage_config_columns_reset' ) ?>
			
			<div class="field-submit">
				<input type="submit" class="button" value="<?php echo Lang::get( 'reset_columns_configuration' ) ?>" />
			</div>
		</fieldset>
	</form>

</section>