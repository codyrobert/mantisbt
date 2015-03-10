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
 * This include file prints out the bug bugnote_stats
 * $f_bug_id must already be defined
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bugnote_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUGNOTE_STATS_INC_ALLOW' ) ) {
	return;
}


if( OFF == \Core\Config::mantis_get( 'time_tracking_enabled' ) ) {
	return;
}
?>

<a id="bugnotestats"></a><br />

<?php
\Core\Collapse::open( 'bugnotestats' );

$t_bugnote_stats_from_def = date( 'd:m:Y', $t_bug->date_submitted );
$t_bugnote_stats_from_def_ar = explode( ':', $t_bugnote_stats_from_def );
$t_bugnote_stats_from_def_d = $t_bugnote_stats_from_def_ar[0];
$t_bugnote_stats_from_def_m = $t_bugnote_stats_from_def_ar[1];
$t_bugnote_stats_from_def_y = $t_bugnote_stats_from_def_ar[2];

$t_bugnote_stats_from_d = \Core\GPC::get_string( 'start_day', $t_bugnote_stats_from_def_d );
$t_bugnote_stats_from_m = \Core\GPC::get_string( 'start_month', $t_bugnote_stats_from_def_m );
$t_bugnote_stats_from_y = \Core\GPC::get_string( 'start_year', $t_bugnote_stats_from_def_y );

$t_bugnote_stats_to_def = date( 'd:m:Y' );
$t_bugnote_stats_to_def_ar = explode( ':', $t_bugnote_stats_to_def );
$t_bugnote_stats_to_def_d = $t_bugnote_stats_to_def_ar[0];
$t_bugnote_stats_to_def_m = $t_bugnote_stats_to_def_ar[1];
$t_bugnote_stats_to_def_y = $t_bugnote_stats_to_def_ar[2];

$t_bugnote_stats_to_d = \Core\GPC::get_string( 'end_day', $t_bugnote_stats_to_def_d );
$t_bugnote_stats_to_m = \Core\GPC::get_string( 'end_month', $t_bugnote_stats_to_def_m );
$t_bugnote_stats_to_y = \Core\GPC::get_string( 'end_year', $t_bugnote_stats_to_def_y );

$f_get_bugnote_stats_button = \Core\GPC::get_string( 'get_bugnote_stats_button', '' );

# Time tracking date range input form
# CSRF protection not required here - form does not result in modifications
?>

<form method="post" action="#bugnotestats">
	<input type="hidden" name="id" value="<?php echo $f_bug_id ?>" />
	<table class="width100" cellspacing="0">
		<tr>
			<td class="form-title" colspan="4">
				<?php
					\Core\Collapse::icon( 'bugnotestats' );
					echo \Core\Lang::get( 'time_tracking' )
				?>
			</td>
		</tr>
		<tr class="row-2">
			<td class="category" width="25%">
				<?php
					$t_filter = array();
					$t_filter[FILTER_PROPERTY_FILTER_BY_DATE] = 'on';
					$t_filter[FILTER_PROPERTY_START_DAY] = $t_bugnote_stats_from_d;
					$t_filter[FILTER_PROPERTY_START_MONTH] = $t_bugnote_stats_from_m;
					$t_filter[FILTER_PROPERTY_START_YEAR] = $t_bugnote_stats_from_y;
					$t_filter[FILTER_PROPERTY_END_DAY] = $t_bugnote_stats_to_d;
					$t_filter[FILTER_PROPERTY_END_MONTH] = $t_bugnote_stats_to_m;
					$t_filter[FILTER_PROPERTY_END_YEAR] = $t_bugnote_stats_to_y;
					\Core\Filter::print_filter_do_filter_by_date( true );
				?>
			</td>
		</tr>
		<tr>
			<td class="center" colspan="2">
				<input type="submit" class="button"
					name="get_bugnote_stats_button"
					value="<?php echo \Core\Lang::get( 'time_tracking_get_info_button' ) ?>" />
			</td>
		</tr>
	</table>
</form>

<?php
	# Print time tracking information if requested
	if( !\Core\Utility::is_blank( $f_get_bugnote_stats_button ) ) {
		# Retrieve time tracking information
		$t_from = $t_bugnote_stats_from_y . '-' . $t_bugnote_stats_from_m . '-' . $t_bugnote_stats_from_d;
		$t_to = $t_bugnote_stats_to_y . '-' . $t_bugnote_stats_to_m . '-' . $t_bugnote_stats_to_d;
		$t_bugnote_stats = \Core\Bug\Note::stats_get_events_array( $f_bug_id, $t_from, $t_to );

		# Sort the array by user/real name
		if( ON == \Core\Config::mantis_get( 'show_realname' ) ) {
			$t_name_field = 'realname';
		} else {
			$t_name_field = 'username';
		}
		$t_sort_name = array();
		foreach ( $t_bugnote_stats as $t_key => $t_item ) {
			$t_sort_name[$t_key] = $t_item[$t_name_field];
		}
		array_multisort( $t_sort_name, $t_bugnote_stats );
		unset( $t_sort_name );
?>
<br />
<table class="width100" cellspacing="0">
	<tr class="row-category-history">
		<td class="small-caption">
			<?php echo \Core\Lang::get( $t_name_field ) ?>
		</td>
		<td class="small-caption">
			<?php echo \Core\Lang::get( 'time_tracking' ) ?>
		</td>
	</tr>
<?php
		# Loop on all time tracking entries
		$t_sum_in_minutes = 0;
		foreach ( $t_bugnote_stats as $t_item ) {
			$t_sum_in_minutes += $t_item['sum_time_tracking'];
			$t_item['sum_time_tracking'] = \Core\Database::minutes_to_hhmm( $t_item['sum_time_tracking'] );
?>
	<tr>
		<td class="small-caption">
			<?php echo \Core\String::display_line( $t_item[$t_name_field] ) ?>
		</td>
		<td class="small-caption">
			<?php echo $t_item['sum_time_tracking'] ?>
		</td>
	</tr>
<?php
		} # end for loop
?>
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo \Core\Lang::get( 'total_time' ) ?>
		</td>
		<td class="small-caption bold">
			<?php echo \Core\Database::minutes_to_hhmm( $t_sum_in_minutes ) ?>
		</td>
	</tr>
</table>

<?php
	} # end if

	\Core\Collapse::closed( 'bugnotestats' );
?>

<table class="width100" cellspacing="0">
	<tr>
		<td class="form-title" colspan="4">
			<?php
				\Core\Collapse::icon( 'bugnotestats' );
				echo \Core\Lang::get( 'time_tracking' )
			?>
		</td>
	</tr>
</table>

<?php
	\Core\Collapse::end( 'bugnotestats' );
