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
 * Bugnote add include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

if( !defined( 'BUGNOTE_ADD_INC_ALLOW' ) ) {
	return;
}


?>
<?php if( ( !\Flickerbox\Bug::is_readonly( $f_bug_id ) ) &&
		( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<a id="addbugnote"></a> <br />

<?php
	\Flickerbox\Collapse::open( 'bugnote_add', '', 'form-container' );
?>
<form id="bugnoteadd" method="post" action="bugnote_add.php">
	<?php echo \Flickerbox\Form::security_field( 'bugnote_add' ) ?>
	<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
	<table>
		<thead>
			<tr>
				<td class="form-title" colspan="2">
			<?php
				\Flickerbox\Collapse::icon( 'bugnote_add' );
				echo \Flickerbox\Lang::get( 'add_bugnote_title' ) ?>
				</td>
			</tr>
		</thead>

		<tbody>
			<tr class="row-2">
				<th class="category" width="25%">
					<?php echo \Flickerbox\Lang::get( 'bugnote' ) ?>
				</th>
				<td width="75%">
					<textarea name="bugnote_text" cols="80" rows="10"></textarea>
				</td>
			</tr>

<?php
	if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
			<tr class="row-1">
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'view_status' ) ?>
				</th>
				<td>
<?php
		$t_default_bugnote_view_status = \Flickerbox\Config::mantis_get( 'default_bugnote_view_status' );
		if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
					<input type="checkbox" id="bugnote_add_view_status" name="private" <?php \Flickerbox\Helper::check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
					<label for="bugnote_add_view_status"><?php echo \Flickerbox\Lang::get( 'private' ) ?></label>
<?php
		} else {
			echo \Flickerbox\Helper::get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
		}
?>
				</td>
			</tr>
<?php
	}

	if( \Flickerbox\Config::mantis_get( 'time_tracking_enabled' ) ) {
		if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( 'time_tracking_edit_threshold' ), $f_bug_id ) ) {
?>
			<tr>
				<th class="category">
					<?php echo \Flickerbox\Lang::get( 'time_tracking' ) ?>
				</th>
				<td>
					<?php if( \Flickerbox\Config::mantis_get( 'time_tracking_stopwatch' ) ) { ?>
					<input type="text" name="time_tracking" class="stopwatch_time" size="8" placeholder="hh:mm:ss" />
					<input type="button" name="time_tracking_toggle" class="stopwatch_toggle" value="<?php echo \Flickerbox\Lang::get( 'time_tracking_stopwatch_start' ) ?>" />
					<input type="button" name="time_tracking_reset" class="stopwatch_reset" value="<?php echo \Flickerbox\Lang::get( 'time_tracking_stopwatch_reset' ) ?>" />
					<?php } else { ?>
					<input type="text" name="time_tracking" size="5" placeholder="hh:mm" />
					<?php } ?>
				</td>
			</tr>
<?php
		}
	}

	\Flickerbox\Event::signal( 'EVENT_BUGNOTE_ADD_FORM', array( $f_bug_id ) );
?>
		</tbody>

		<tfoot>
			<tr>
				<td class="center" colspan="2">
					<input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'add_bugnote_button' ) ?>" />
				</td>
			</tr>
		</tfoot>
	</table>
</form>
<?php
	\Flickerbox\Collapse::closed( 'bugnote_add' );
?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
	<?php	\Flickerbox\Collapse::icon( 'bugnote_add' );
		echo \Flickerbox\Lang::get( 'add_bugnote_title' ) ?>
	</td>
</tr>
</table>
<?php
	\Flickerbox\Collapse::end( 'bugnote_add' );
?>

<?php # Bugnote Add Form END ?>
<?php
}
