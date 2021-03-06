<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit Core Formatting Configuration
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

\Core\Auth::reauthenticate( );
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_plugin_threshold' ) );

\Core\HTML::page_top( \Core\Lang::get( 'plugin_format_title' ) );

\Core\HTML::print_manage_menu( );

?>

<div id="formatting-config-div" class="form-container">
	<form id="formatting-config-form" action="<?php echo \Core\Plugin::page( 'config_edit' )?>" method="post">
		<fieldset>
			<legend><span><?php echo \Core\Lang::get( 'plugin_format_title' ) . ': ' . \Core\Lang::get( 'plugin_format_config' )?></span></legend>
			<?php echo \Core\Form::security_field( 'plugin_format_config_edit' ) ?>

			<div class="field-container">
				<label><span><?php echo \Core\Lang::get( 'plugin_format_process_text' )?>
				<br /><span class="small"><?php echo \Core\Lang::get( 'plugin_format_process_text_warning_notice' )?></span>
				</span></label>
				<span class="radio">
					<label><input type="radio" name="process_text" value="1" <?php echo( ON == \Core\Plugin::config_get( 'process_text' ) ) ? 'checked="checked" ' : ''?>/>
					<?php echo \Core\Lang::get( 'plugin_format_enabled' )?></label>
					<label><input type="radio" name="process_text" value="0" <?php echo( OFF == \Core\Plugin::config_get( 'process_text' ) ) ? 'checked="checked" ' : ''?>/>
					<?php echo \Core\Lang::get( 'plugin_format_disabled' )?></label>
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo \Core\Lang::get( 'plugin_format_process_urls' )?></span></label>
				<span class="radio">
					<label><input type="radio" name="process_urls" value="1" <?php echo( ON == \Core\Plugin::config_get( 'process_urls' ) ) ? 'checked="checked" ' : ''?>/>
					<?php echo \Core\Lang::get( 'plugin_format_enabled' )?></label>
					<label><input type="radio" name="process_urls" value="0" <?php echo( OFF == \Core\Plugin::config_get( 'process_urls' ) ) ? 'checked="checked" ' : ''?>/>
					<?php echo \Core\Lang::get( 'plugin_format_disabled' )?></label>
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span>
				<?php echo \Core\Lang::get( 'plugin_format_process_buglinks' )?>
				<br />
				<span class="small"><?php
					printf( \Core\Lang::get( 'plugin_format_process_buglinks_info' ),
						\Core\Config::mantis_get( 'bug_link_tag' ), \Core\Config::mantis_get( 'bugnote_link_tag' ) );
				?></span>
				</span></label>
				<span class="radio">
					<label><input type="radio" name="process_buglinks" value="1" <?php echo( ON == \Core\Plugin::config_get( 'process_buglinks' ) ) ? 'checked="checked" ' : ''?>/>
					<?php echo \Core\Lang::get( 'plugin_format_enabled' )?></label>
					<label><input type="radio" name="process_buglinks" value="0" <?php echo( OFF == \Core\Plugin::config_get( 'process_buglinks' ) ) ? 'checked="checked" ' : ''?>/>
					<?php echo \Core\Lang::get( 'plugin_format_disabled' )?></label>
				</span>
				<span class="label-style"></span>
			</div>

			<span class="submit-button">
				<input type="submit" class="button" value="<?php echo \Core\Lang::get( 'change_configuration' )?>" />
			</span>
		</fieldset>
	</form>
</div>

<?php
\Core\HTML::page_bottom();
