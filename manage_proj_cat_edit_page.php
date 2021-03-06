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
 * Edit Project Categories
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */



\Core\Auth::reauthenticate();

$f_category_id		= \Core\GPC::get_int( 'id' );
$f_project_id		= \Core\GPC::get_int( 'project_id' );

$t_row = \Core\Category::get_row( $f_category_id );
$t_assigned_to = (int)$t_row['user_id'];
$t_project_id = (int)$t_row['project_id'];
$t_name = $t_row['name'];

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $t_project_id );

\Core\HTML::page_top();

\Core\HTML::print_manage_menu( 'manage_proj_cat_edit_page.php' ); ?>

<div id="manage-proj-category-update-div" class="form-container">
	<form id="manage-proj-category-update-form" method="post" action="manage_proj_cat_update.php">
		<fieldset>
			<legend><span><?php echo \Core\Lang::get( 'edit_project_category_title' ) ?></span></legend>
			<?php echo \Core\Form::security_field( 'manage_proj_cat_update' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>"/>
			<input type="hidden" name="category_id" value="<?php echo \Core\String::attribute( $f_category_id ) ?>" />
			<div class="field-container">
				<label for="proj-category-name"><span><?php echo \Core\Lang::get( 'category' ) ?></span></label>
				<span class="input"><input type="text" id="proj-category-name" name="name" size="32" maxlength="128" value="<?php echo \Core\String::attribute( $t_name ) ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="proj-category-assigned-to"><span><?php echo \Core\Lang::get( 'assigned_to' ) ?></span></label>
				<span class="select">
					<select id="proj-category-assigned-to" name="assigned_to">
						<option value="0"></option>
						<?php \Core\Print_Util::assign_to_option_list( $t_assigned_to, $t_project_id ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'update_category_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<div class="form-container">
	<form method="post" action="manage_proj_cat_delete.php" class="action-button">
		<fieldset>
			<?php echo \Core\Form::security_field( 'manage_proj_cat_delete' ) ?>
			<input type="hidden" name="id" value="<?php echo \Core\String::attribute( $f_category_id ) ?>" />
			<input type="hidden" name="project_id" value="<?php echo \Core\String::attribute( $f_project_id ) ?>" />
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'delete_category_button' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php

\Core\HTML::page_bottom();
