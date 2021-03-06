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
 * This include file prints out the bug history
 * $f_bug_id must already be defined
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

if( !defined( 'HISTORY_INC_ALLOW' ) ) {
	return;
}


$t_access_level_needed = \Core\Config::mantis_get( 'view_history_threshold' );
if( !\Core\Access::has_bug_level( $t_access_level_needed, $f_bug_id ) ) {
	return;
}
?>

<a id="history"></a><br />

<?php
	\Core\Collapse::open( 'history', '', 'table-container' );
	$t_history = \Core\History::get_events_array( $f_bug_id );
?>
<table>
	<thead>
		<tr>
			<td class="form-title" colspan="4">
<?php
			\Core\Collapse::icon( 'history' );
			echo \Core\Lang::get( 'bug_history' ) ?>
			</td>
		</tr>

		<tr class="row-category-history">
			<th class="small-caption">
				<?php echo \Core\Lang::get( 'date_modified' ) ?>
			</th>
			<th class="small-caption">
				<?php echo \Core\Lang::get( 'username' ) ?>
			</th>
			<th class="small-caption">
				<?php echo \Core\Lang::get( 'field' ) ?>
			</th>
			<th class="small-caption">
				<?php echo \Core\Lang::get( 'change' ) ?>
			</th>
		</tr>
	</thead>

	<tbody>
<?php
	foreach( $t_history as $t_item ) {
?>
		<tr>
			<td class="small-caption">
				<?php echo $t_item['date'] ?>
			</td>
			<td class="small-caption">
				<?php \Core\Print_Util::user( $t_item['userid'] ) ?>
			</td>
			<td class="small-caption">
				<?php echo \Core\String::display( $t_item['note'] ) ?>
			</td>
			<td class="small-caption">
				<?php echo ( $t_item['raw'] ? \Core\String::display_line_links( $t_item['change'] ) : $t_item['change'] ) ?>
			</td>
		</tr>
<?php
	} # end for loop
?>
	</tbody>
</table>
<?php
	\Core\Collapse::closed( 'history' );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
	<?php	\Core\Collapse::icon( 'history' );
		echo \Core\Lang::get( 'bug_history' ) ?>
	</td>
</tr>
</table>

<?php
\Core\Collapse::end( 'history' );
