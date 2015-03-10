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
 * Tag View Page
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

\Flickerbox\Access::ensure_global_level( \Flickerbox\Config::mantis_get( 'tag_view_threshold' ) );
\Flickerbox\Compress::enable();

$f_tag_id = \Flickerbox\GPC::get_int( 'tag_id' );
$t_tag_row = \Flickerbox\Tag::get( $f_tag_id );

$t_name = \Flickerbox\String::display_line( $t_tag_row['name'] );
$t_description = \Flickerbox\String::display( $t_tag_row['description'] );
$t_can_edit = \Flickerbox\Access::has_global_level( \Flickerbox\Config::mantis_get( 'tag_edit_threshold' ) );
$t_can_edit_own = $t_can_edit || auth_get_current_user_id() == \Flickerbox\Tag::get_field( $f_tag_id, 'user_id' )
	&& \Flickerbox\Access::has_global_level( \Flickerbox\Config::mantis_get( 'tag_edit_own_threshold' ) );


\Flickerbox\HTML::page_top( sprintf( \Flickerbox\Lang::get( 'tag_details' ), $t_name ) ); ?>

<div id="tag-view-div" class="form-container">
	<h2><?php echo sprintf( \Flickerbox\Lang::get( 'tag_details' ), $t_name ) ?></h2>
	<div class="section-link">
		<?php \Flickerbox\Print_Util::bracket_link( 'search.php?tag_string='.urlencode( $t_tag_row['name'] ), sprintf( \Flickerbox\Lang::get( 'tag_filter_default' ), \Flickerbox\Tag::stats_attached( $f_tag_id ) ) ); ?>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_id' ) ?></span></span>
		<span class="display-value"><span><?php echo $t_tag_row['id'] ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_name' ) ?></span></span>
		<span class="display-value"><span><?php echo $t_name ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_creator' ) ?></span></span>
		<span class="display-value"><span><?php echo \Flickerbox\String::display_line( \Flickerbox\User::get_name( $t_tag_row['user_id'] ) ) ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_created' ) ?></span></span>
		<span class="display-value"><span><?php echo date( \Flickerbox\Config::mantis_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_updated' ) ?></span></span>
		<span class="display-value"><span><?php echo date( \Flickerbox\Config::mantis_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_description' ) ?></span></span>
		<span class="display-value"><span><?php echo $t_description ?></span></span>
		<span class="label-style"></span>
	</div>

<?php
	# Related tags

	$t_tags_related = \Flickerbox\Tag::stats_related( $f_tag_id );
	if( count( $t_tags_related ) ) {
?>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Flickerbox\Lang::get( 'tag_related' ); ?></span></span>
		<div class="display-value">
			<table id="related-tags" class="tag-list">
<?php
		foreach( $t_tags_related as $t_tag ) {
			$t_name = \Flickerbox\String::display_line( $t_tag['name'] );
			$t_description = \Flickerbox\String::display_line( $t_tag['description'] );
			$t_count = $t_tag['count'];
			$t_link = \Flickerbox\String::html_specialchars( 'search.php?tag_string='.urlencode( '+' . $t_tag_row['name'] . \Flickerbox\Config::mantis_get( 'tag_separator' ) . '+' . $t_name ) );
			$t_label = sprintf( \Flickerbox\Lang::get( 'tag_related_issues' ), $t_tag['count'] ); ?>
			<tr>
				<td><span class="tag-link"><a href="tag_view_page.php?tag_id=<?php echo $t_tag['id']; ?>" title="<?php echo $t_description; ?>"><?php echo $t_name; ?></a></span></td>
				<td><span class="tag-filter"><a href="<?php echo $t_link; ?>"><?php echo $t_label; ?></a></span></td>
			</tr>
<?php
		}
?>
			</table>
		</div>
		<span class="label-style"></span>
	</div>
<?php
	}

	if( $t_can_edit_own || $t_can_edit ) {
?>
	<div class="action-buttons center">
<?php
		if( $t_can_edit_own ) {
?>
		<form class="action-button" action="tag_update_page.php" method="post">
			<fieldset>
				<?php # CSRF protection not required here - form does not result in modifications ?>
				<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>" />
				<input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'tag_update_button' ) ?>" />
			</fieldset>
		</form><?php
		}

		if( $t_can_edit ) { ?>
		<form action="tag_delete.php" method="post" class="action-button">
			<fieldset>
				<?php echo \Flickerbox\Form::security_field( 'tag_delete' ) ?>
				<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>" />
				<input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'tag_delete_button' ) ?>" />
			</fieldset>
		</form><?php
		} ?>
	</div><?php
	} ?>
</div><?php

\Flickerbox\HTML::page_bottom();
