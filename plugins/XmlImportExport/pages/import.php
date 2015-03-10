<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Import XML Issues Page
 */

\Flickerbox\Access::ensure_project_level( \Flickerbox\Plugin::config_get( 'import_threshold' ) );

auth_reauthenticate( );

\Flickerbox\HTML::page_top( \Flickerbox\Plugin::langget( 'import' ) );

$t_this_page = \Flickerbox\Plugin::page( 'import' ); # FIXME with plugins this does not work...
\Flickerbox\HTML::print_manage_menu( $t_this_page );

$t_max_file_size = (int)min(
	\Flickerbox\Utility::ini_get_number( 'upload_max_filesize' ),
	\Flickerbox\Utility::ini_get_number( 'post_max_size' ),
	\Flickerbox\Config::mantis_get( 'max_file_size' )
);

# We need a project to import into
$t_project_id = \Flickerbox\Helper::get_current_project( );
if( ALL_PROJECTS == $t_project_id ) {
	\Flickerbox\Print_Util::header_redirect( 'login_select_proj_page.php?ref=' . $t_this_page );
}

?>

<div id="importexport-import-div" class="form-container">
	<form id="file_upload" method="post" enctype="multipart/form-data" action="<?php echo \Flickerbox\Plugin::page( 'import_action' )?>">
		<fieldset>
			<legend>
				<span>
					<?php printf(
						\Flickerbox\Plugin::langget( 'importing_in_project' ),
						\Flickerbox\String::display( \Flickerbox\Project::get_field( $t_project_id, 'name' ) )
					); ?>
				</span>
			</legend>
			<?php echo \Flickerbox\Form::security_field( 'plugin_xml_import_action' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $t_project_id;?>" />
			<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size?>" />
			<input type="hidden" name="step" value="1" />

			<div class="field-container">
				<label><span>
				<?php echo \Flickerbox\Lang::get( 'select_file' )?><br />
				<?php echo '<span class="small">(' . \Flickerbox\Lang::get( 'max_file_size_label' ) . ' ' . number_format( $t_max_file_size / 1000 ) . 'k)</span>'?>
				</span></label>
				<span class="file">
					<input name="file" type="file" size="40" />
				</span>
				<span class="label-style"></span>
			</div>

			<h2>
				<?php echo \Flickerbox\Plugin::langget( 'import_options' ); ?>
			</h2>

			<div class="field-container">
				<label><span><?php echo \Flickerbox\Plugin::langget( 'cross_references' );?></span></label>
				<span class="select">
					<?php echo \Flickerbox\Plugin::langget( 'default_strategy' );?>
					<select name="strategy">
						<option value="renumber" title="<?php echo \Flickerbox\Plugin::langget( 'renumber_desc' );?>">
						<?php echo \Flickerbox\Plugin::langget( 'renumber' );?></option>
						<option value="link" title="<?php echo \Flickerbox\Plugin::langget( 'link_desc' );?>">
						<?php echo \Flickerbox\Plugin::langget( 'link' );?></option>
						<option value="disable" title="<?php echo \Flickerbox\Plugin::langget( 'disable_desc' );?>">
						<?php echo \Flickerbox\Plugin::langget( 'disable' );?></option>
					</select>
					<br><br>
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo \Flickerbox\Plugin::langget( 'fallback' );?></span></label>
				<span class="input">
					<select name="fallback">
						<option value="link" title="<?php echo \Flickerbox\Plugin::langget( 'link_desc' );?>">
						<?php echo \Flickerbox\Plugin::langget( 'link' );?></option>
						<option value="disable" title="<?php echo \Flickerbox\Plugin::langget( 'disable_desc' );?>">
						<?php echo \Flickerbox\Plugin::langget( 'disable' );?></option>
					</select>
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'categories' );?></span></label>
				<span class="checkbox">
					<input type="checkbox" checked="checked" id="keepcategory" name="keepcategory" />
					<label for="keepcategory"><?php echo \Flickerbox\Plugin::langget( 'keep_same_category' );?></label>
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo \Flickerbox\Plugin::langget( 'fallback_category' );?></span></label>
				<span class="select">
					<select name="defaultcategory">
						<?php \Flickerbox\Print_Util::category_option_list( );?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>

			<span class="submit-button">
				<input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'upload_file_button' )?>" />
			</span>
		</fieldset>
	</form>
</div>

<?php
\Flickerbox\HTML::page_bottom();
