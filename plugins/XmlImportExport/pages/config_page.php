<?php
# Copyright (c) 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_plugin_threshold' ) );

\Core\HTML::page_top();
//\Core\HTML::print_manage_menu();
?>

<br />
<div class="form-container">
<form action="<?php echo \Core\Plugin::page( 'config' ) ?>" method="post">
<fieldset>
	<legend>
		<?php echo \Core\Plugin::langget( 'config_title' ) ?>
	</legend>

	<?php echo \Core\Form::security_field( 'plugin_XmlImportExport_config' ) ?>

	<!-- Import Access Level  -->
	<div class="field-container">
		<label for="import_threshold">
			<span><?php echo \Core\Plugin::langget( 'import_threshold' ) ?></span>
		</label>
		<span class="select">
			<select id="import_threshold" name="import_threshold"><?php
				\Core\Print_Util::enum_string_option_list(
					'access_levels',
					\Core\Plugin::config_get( 'import_threshold' )
				);
			?></select>
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Export Access Level  -->
	<div class="field-container">
		<label for="export_threshold">
			<span><?php echo \Core\Plugin::langget( 'export_threshold' ) ?></span>
		</label>
		<span class="select">
			<select id="export_threshold" name="export_threshold"><?php
				\Core\Print_Util::enum_string_option_list(
					'access_levels',
					\Core\Plugin::config_get( 'export_threshold' )
				);
			?></select>
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Update button -->
	<div class="submit-button">
		<input type="submit" value="<?php echo \Core\Plugin::langget( 'action_update' ) ?>"/>
	</div>

</fieldset>
</form>
</div>

<?php
\Core\HTML::page_bottom();
