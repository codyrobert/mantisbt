<?php
# Copyright (c) 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

\Flickerbox\Access::ensure_global_level( config_get( 'manage_plugin_threshold' ) );

\Flickerbox\HTML::page_top();
//\Flickerbox\HTML::print_manage_menu();
?>

<br />
<div class="form-container">
<form action="<?php echo plugin_page( 'config' ) ?>" method="post">
<fieldset>
	<legend>
		<?php echo plugin_langget( 'config_title' ) ?>
	</legend>

	<?php echo \Flickerbox\Form::security_field( 'plugin_XmlImportExport_config' ) ?>

	<!-- Import Access Level  -->
	<div class="field-container">
		<label for="import_threshold">
			<span><?php echo plugin_langget( 'import_threshold' ) ?></span>
		</label>
		<span class="select">
			<select id="import_threshold" name="import_threshold"><?php
				print_enum_string_option_list(
					'access_levels',
					plugin_config_get( 'import_threshold' )
				);
			?></select>
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Export Access Level  -->
	<div class="field-container">
		<label for="export_threshold">
			<span><?php echo plugin_langget( 'export_threshold' ) ?></span>
		</label>
		<span class="select">
			<select id="export_threshold" name="export_threshold"><?php
				print_enum_string_option_list(
					'access_levels',
					plugin_config_get( 'export_threshold' )
				);
			?></select>
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Update button -->
	<div class="submit-button">
		<input type="submit" value="<?php echo plugin_langget( 'action_update' ) ?>"/>
	</div>

</fieldset>
</form>
</div>

<?php
\Flickerbox\HTML::page_bottom();
