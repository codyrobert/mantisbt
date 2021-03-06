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
 * Tags Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */



\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'tag_edit_threshold' ) );

\Core\Compress::enable();

$t_can_edit = \Core\Access::has_global_level( \Core\Config::mantis_get( 'tag_edit_threshold' ) );
$f_filter = utf8_strtoupper( \Core\GPC::get_string( 'filter', \Core\Config::mantis_get( 'default_manage_tag_prefix' ) ) );
$f_page_number = \Core\GPC::get_int( 'page_number', 1 );

# Start Index Menu
$t_prefix_array = array( 'ALL' );

for( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[] = $i;
}

for( $i = 0; $i <= 9; $i++ ) {
	$t_prefix_array[] = (string)$i;
}
if( $f_filter === 'ALL' ) {
	$t_name_filter = '';
} else {
	$t_name_filter = $f_filter;
}

# Set the number of Tags per page.
$t_per_page = 20;
$t_offset = (( $f_page_number - 1 ) * $t_per_page );

# Determine number of tags in tag table
$t_total_tag_count = \Core\Tag::count( $t_name_filter );

#Number of pages from result
$t_page_count = ceil( $t_total_tag_count / $t_per_page );

if( $t_page_count < 1 ) {
	$t_page_count = 1;
}

# Make sure $p_page_number isn't past the last page.
if( $f_page_number > $t_page_count ) {
	$f_page_number = $t_page_count;
}

# Make sure $p_page_number isn't before the first page
if( $f_page_number < 1 ) {
	$f_page_number = 1;
}

# Retrieve Tags from table
$t_result = \Core\Tag::get_all( $t_name_filter, $t_per_page, $t_offset ) ;

\Core\HTML::page_top( \Core\Lang::get( 'manage_tags_link' ) );
\Core\HTML::print_manage_menu( 'manage_tags_page.php' ); ?>

<div id="manage-tags-filter-menu">
	<ul class="menu"><?php
	foreach ( $t_prefix_array as $t_prefix ) {
		$t_caption = ( $t_prefix === 'ALL' ? \Core\Lang::get( 'show_all_tags' ) : $t_prefix );
		if( $t_prefix == $f_filter ) {
			$t_link = '<strong>' . $t_caption . '</strong>';
		} else {
			$t_link = '<a href="manage_tags_page.php?filter=' . $t_prefix .'">' . $t_caption . '</a>';
		}
		echo '<li>' . $t_link . '</li>';
	} ?>
	</ul>
</div>

<div class="table-container">
	<h2><?php echo \Core\Lang::get( 'manage_tags_link' ) ?> [<?php echo $t_total_tag_count ?>]</h2>
	<?php if( $t_can_edit ) { ?>
	<div class="section-link"><?php \Core\Print_Util::link( '#tagcreate', \Core\Lang::get( 'tag_create' ) ) ?></div>
	<?php } ?>
	<table>
		<thead>
			<tr class="row-category">
				<td><?php echo \Core\Lang::get( 'tag_name' ) ?></td>
				<td><?php echo \Core\Lang::get( 'tag_creator' ) ?></td>
				<td><?php echo \Core\Lang::get( 'tag_created' ) ?></td>
				<td><?php echo \Core\Lang::get( 'tag_updated' ) ?></td>
			</tr>
		</thead>
		<tbody>
<?php
		# Display all tags
		while( $t_tag_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_tag_name = \Core\String::display_line( $t_tag_row['name'] );
			$t_tag_description = \Core\String::display( $t_tag_row['description'] );
?>
			<tr>
			<?php if( $t_can_edit ) { ?>
				<td><a href="tag_view_page.php?tag_id=<?php echo $t_tag_row['id'] ?>" ><?php echo $t_tag_name ?></a></td>
			<?php } else { ?>
				<td><?php echo $t_tag_name ?></td>
			<?php } ?>
				<td><?php echo \Core\String::display_line( \Core\User::get_name( $t_tag_row['user_id'] ) ) ?></td>
				<td><?php echo date( \Core\Config::mantis_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?></td>
				<td><?php echo date( \Core\Config::mantis_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?></td>
			</tr>
<?php
		} # end while loop on tags
?>
		</tbody>
	</table>
	<div class="pager-links"><?php
		# @todo hack - pass in the hide inactive filter via cheating the actual filter value
		\Core\Print_Util::page_links( 'manage_tags_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter ); ?>
	</div>
</div>

<?php if( $t_can_edit ) { ?>
<div id="manage-tags-create-div" class="form-container">
	<a name="tagcreate" />
	<form id="manage-tags-create-form" method="post" action="tag_create.php">
		<fieldset class="has-required">
			<legend><span><?php echo \Core\Lang::get( 'tag_create' ) ?></span></legend>
			<?php echo \Core\Form::security_field( 'tag_create' ); ?>
			<div class="field-container">
				<label for="tag-name" class="required"><span><?php echo \Core\Lang::get( 'tag_name' ) ?></span></label>
				<span class="input"><input type="text" id="tag-name" name="name" size="40" maxlength="100" />
				<span><?php echo sprintf( \Core\Lang::get( 'tag_separate_by' ), \Core\Config::mantis_get( 'tag_separator' ) ); ?></span>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="tag-description"><span><?php echo \Core\Lang::get( 'tag_description' ) ?></span></label>
				<span class="textarea"><textarea id="tag-description" name="description" cols="80" rows="6"></textarea></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'tag_create' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
} #End can Edit

\Core\HTML::page_bottom();
