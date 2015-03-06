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
 * Edit Project Versions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'event_api.php' );
require_api( 'helper_api.php' );

\Flickerbox\HTML::require_js( 'jscalendar/calendar.js' );
\Flickerbox\HTML::require_js( 'jscalendar/lang/calendar-en.js' );
\Flickerbox\HTML::require_js( 'jscalendar/calendar-setup.js' );
\Flickerbox\HTML::require_css( 'calendar-blue.css' );

auth_reauthenticate();

$f_version_id = \Flickerbox\GPC::get_int( 'version_id' );

$t_version = \Flickerbox\Version::get( $f_version_id );

\Flickerbox\Access::ensure_project_level( config_get( 'manage_project_threshold' ), $t_version->project_id );

\Flickerbox\HTML::page_top();

\Flickerbox\HTML::print_manage_menu( 'manage_proj_ver_edit_page.php' ); ?>

<div id="manage-proj-version-update-div" class="form-container">
	<form id="manage-proj-version-update-form" method="post" action="manage_proj_ver_update.php">
		<fieldset>
			<legend><span><?php echo \Flickerbox\Lang::get( 'edit_project_version_title' ) ?></span></legend>
			<?php echo \Flickerbox\Form::security_field( 'manage_proj_ver_update' ) ?>
			<input type="hidden" name="version_id" value="<?php echo \Flickerbox\String::attribute( $t_version->id ) ?>" />
			<div class="field-container">
				<label for="proj-version-new-version"><span><?php echo \Flickerbox\Lang::get( 'version' ) ?></span></label>
				<span class="input"><input type="text" id="proj-version-new-version" name="new_version" size="32" maxlength="64" value="<?php echo \Flickerbox\String::attribute( $t_version->version ) ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="proj-version-date-order"><span><?php echo \Flickerbox\Lang::get( 'date_order' ) ?></span></label>
				<span class="input"><input type="text" id="proj-version-date-order" name="date_order" class="datetime" size="32" value="<?php echo (\Flickerbox\Date::is_null( $t_version->date_order ) ? '' : \Flickerbox\String::attribute( date( config_get( 'calendar_date_format' ), $t_version->date_order ) ) ) ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="proj-version-description"><span><?php echo \Flickerbox\Lang::get( 'description' ) ?></span></label>
				<span class="textarea"><textarea id="proj-version-description" name="description" cols="60" rows="5"><?php echo \Flickerbox\String::attribute( $t_version->description ) ?></textarea></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="proj-version-released"><span><?php echo \Flickerbox\Lang::get( 'released' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="proj-version-released" name="released" <?php check_checked( (boolean)$t_version->released, VERSION_RELEASED ); ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="proj-version-obsolete"><span><?php echo \Flickerbox\Lang::get( 'obsolete' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="proj-version-obsolete" name="obsolete" <?php check_checked( (boolean)$t_version->obsolete, true ); ?> /></span>
				<span class="label-style"></span>
			</div>

			<?php event_signal( 'EVENT_MANAGE_VERSION_UPDATE_FORM', array( $t_version->id ) ); ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'update_version_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<div class="form-container">
	<form method="post" action="manage_proj_ver_delete.php" class="action-button">
		<fieldset>
			<?php echo \Flickerbox\Form::security_field( 'manage_proj_ver_delete' ) ?>
			<input type="hidden" name="version_id" value="<?php echo \Flickerbox\String::attribute( $t_version->id ) ?>" />
			<input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'delete_version_button' ) ?>" />
		</fieldset>
	</form>
</div>

<?php
\Flickerbox\HTML::page_bottom();
