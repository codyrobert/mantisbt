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
 * Tag Update Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 */



\Core\Compress::enable();

$f_tag_id = \Core\GPC::get_int( 'tag_id' );
$t_tag_row = \Core\Tag::get( $f_tag_id );

$t_name = \Core\String::display_line( $t_tag_row['name'] );
$t_description = \Core\String::display( $t_tag_row['description'] );

if( !( \Core\Access::has_global_level( \Core\Config::mantis_get( 'tag_edit_threshold' ) )
	|| ( \Core\Auth::get_current_user_id() == $t_tag_row['user_id'] )
		&& \Core\Access::has_global_level( \Core\Config::mantis_get( 'tag_edit_own_threshold' ) ) ) ) {
	\Core\Access::denied();
}

\Core\HTML::page_top( sprintf( \Core\Lang::get( 'tag_update' ), $t_name ) );
?>
<div class="form-container">
	<form method="post" action="tag_update.php">
		<fieldset>
			<legend><span><?php echo sprintf( \Core\Lang::get( 'tag_update' ), $t_name ) ?></span></legend>
			<div class="section-link"><?php \Core\Print_Util::bracket_link( 'tag_view_page.php?tag_id='.$f_tag_id, \Core\Lang::get( 'tag_update_return' ) ); ?></div>
			<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>"/>
			<?php echo \Core\Form::security_field( 'tag_update' ) ?>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'tag_id' ) ?></span></span>
				<span class="display-value"><span><?php echo $t_tag_row['id'] ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="tag-name"><span><?php echo \Core\Lang::get( 'tag_name' ) ?></span></label>
				<span class="input"><input type="text" <?php echo \Core\Helper::get_tab_index() ?> id="tag-name" name="name" value="<?php echo $t_name ?>"/></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<?php
					if( \Core\Access::has_global_level( \Core\Config::mantis_get( 'tag_edit_threshold' ) ) ) {
						echo '<label for="tag-user-id"><span>', \Core\Lang::get( 'tag_creator' ), '</span></label>';
						echo '<span class="select"><select ', \Core\Helper::get_tab_index(), ' id="tag-user-id" name="user_id">';
						\Core\Print_Util::user_option_list( (int)$t_tag_row['user_id'], ALL_PROJECTS, (int)\Core\Config::mantis_get( 'tag_create_threshold' ) );
						echo '</select></span>';
					} else { ?>
						<span class="display-label"><span><?php echo \Core\Lang::get( 'tag_creator' ); ?></span></span>
						<span class="display-value"><span><?php echo \Core\String::display_line( \Core\User::get_name( $t_tag_row['user_id'] ) ); ?></span></span><?php
					} ?>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'tag_created' ) ?></span></span>
				<span class="display-value"><span><?php echo date( \Core\Config::mantis_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<span class="display-label"><span><?php echo \Core\Lang::get( 'tag_updated' ) ?></span></span>
				<span class="display-value"><span><?php echo date( \Core\Config::mantis_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?></span></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="tag-description"><span><?php echo \Core\Lang::get( 'tag_description' ) ?></span></label>
				<span class="textarea"><textarea id="tag-description" name="description" <?php echo \Core\Helper::get_tab_index() ?> cols="80" rows="6"><?php echo \Core\String::textarea( $t_description ) ?></textarea></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input <?php echo \Core\Helper::get_tab_index() ?> type="submit" class="button" value="<?php echo \Core\Lang::get( 'tag_update_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
\Core\HTML::page_bottom();
