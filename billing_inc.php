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
 * @uses database_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

if( !defined( 'BILLING_INC_ALLOW' ) ) {
	return;
}


?>
<a id="bugnotestats"></a><br />
<?php
\Flickerbox\Collapse::open( 'bugnotestats' );

$t_today = date( 'd:m:Y' );
$t_date_submitted = isset( $t_bug ) ? date( 'd:m:Y', $t_bug->date_submitted ) : $t_today;

$t_bugnote_stats_from_def = $t_date_submitted;
$t_bugnote_stats_from_def_ar = explode( ':', $t_bugnote_stats_from_def );
$t_bugnote_stats_from_def_d = $t_bugnote_stats_from_def_ar[0];
$t_bugnote_stats_from_def_m = $t_bugnote_stats_from_def_ar[1];
$t_bugnote_stats_from_def_y = $t_bugnote_stats_from_def_ar[2];

$t_bugnote_stats_from_d = \Flickerbox\GPC::get_int( 'start_day', $t_bugnote_stats_from_def_d );
$t_bugnote_stats_from_m = \Flickerbox\GPC::get_int( 'start_month', $t_bugnote_stats_from_def_m );
$t_bugnote_stats_from_y = \Flickerbox\GPC::get_int( 'start_year', $t_bugnote_stats_from_def_y );

$t_bugnote_stats_to_def = $t_today;
$t_bugnote_stats_to_def_ar = explode( ':', $t_bugnote_stats_to_def );
$t_bugnote_stats_to_def_d = $t_bugnote_stats_to_def_ar[0];
$t_bugnote_stats_to_def_m = $t_bugnote_stats_to_def_ar[1];
$t_bugnote_stats_to_def_y = $t_bugnote_stats_to_def_ar[2];

$t_bugnote_stats_to_d = \Flickerbox\GPC::get_int( 'end_day', $t_bugnote_stats_to_def_d );
$t_bugnote_stats_to_m = \Flickerbox\GPC::get_int( 'end_month', $t_bugnote_stats_to_def_m );
$t_bugnote_stats_to_y = \Flickerbox\GPC::get_int( 'end_year', $t_bugnote_stats_to_def_y );

$f_get_bugnote_stats_button = \Flickerbox\GPC::get_string( 'get_bugnote_stats_button', '' );

# Retrieve the cost as a string and convert to floating point
$f_bugnote_cost = floatval( \Flickerbox\GPC::get_string( 'bugnote_cost', '' ) );

$f_project_id = \Flickerbox\Helper::get_current_project();

if( ON == \Flickerbox\Config::mantis_get( 'time_tracking_with_billing' ) ) {
	$t_cost_col = true;
} else {
	$t_cost_col = false;
}

# Time tracking date range input form
# CSRF protection not required here - form does not result in modifications
?>

<form method="post" action="">
	<input type="hidden" name="id" value="<?php echo isset( $f_bug_id ) ? $f_bug_id : 0 ?>" />
	<table class="width100" cellspacing="0">
		<tr>
			<td class="form-title" colspan="4">
				<?php
					\Flickerbox\Collapse::icon( 'bugnotestats' );
					echo \Flickerbox\Lang::get( 'time_tracking' )
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
					\Flickerbox\Filter::print_filter_do_filter_by_date( true );
				?>
			</td>
		</tr>
<?php
	if( $t_cost_col ) {
?>
		<tr class="row-1">
			<td>
				<?php echo \Flickerbox\Lang::get( 'time_tracking_cost_per_hour_label' ) ?>
				<input type="text" name="bugnote_cost" value="<?php echo $f_bugnote_cost ?>" />
			</td>
		</tr>
<?php
	}
?>
		<tr>
			<td class="center" colspan="2">
				<input type="submit" class="button"
					name="get_bugnote_stats_button"
					value="<?php echo \Flickerbox\Lang::get( 'time_tracking_get_info_button' ) ?>"
				/>
			</td>
		</tr>
	</table>
</form>

<?php
	if( !\Flickerbox\Utility::is_blank( $f_get_bugnote_stats_button ) ) {
		# Retrieve time tracking information
		$t_from = $t_bugnote_stats_from_y . '-' . $t_bugnote_stats_from_m . '-' . $t_bugnote_stats_from_d;
		$t_to = $t_bugnote_stats_to_y . '-' . $t_bugnote_stats_to_m . '-' . $t_bugnote_stats_to_d;
		$t_bugnote_stats = \Flickerbox\Bug\Note::stats_get_project_array( $f_project_id, $t_from, $t_to, $f_bugnote_cost );

		# Sort the array by bug_id, user/real name
		if( ON == \Flickerbox\Config::mantis_get( 'show_realname' ) ) {
			$t_name_field = 'realname';
		} else {
			$t_name_field = 'username';
		}
		$t_sort_bug = $t_sort_name = array();
		foreach ( $t_bugnote_stats as $t_key => $t_item ) {
			$t_sort_bug[$t_key] = $t_item['bug_id'];
			$t_sort_name[$t_key] = $t_item[$t_name_field];
		}
		array_multisort( $t_sort_bug, SORT_NUMERIC, $t_sort_name, $t_bugnote_stats );
		unset( $t_sort_bug, $t_sort_name );

		if( \Flickerbox\Utility::is_blank( $f_bugnote_cost ) || ( (double)$f_bugnote_cost == 0 ) ) {
			$t_cost_col = false;
		}

		$t_prev_id = -1;
?>
<br />
<table class="width100" cellspacing="0">
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo \Flickerbox\Lang::get( $t_name_field ) ?>
		</td>
		<td class="small-caption bold">
			<?php echo \Flickerbox\Lang::get( 'time_tracking' ) ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo \Flickerbox\Lang::get( 'time_tracking_cost' ) ?>
		</td>
<?php	} ?>

	</tr>
<?php
		$t_sum_in_minutes = 0;
		$t_user_summary = array();

		# Initialize the user summary array
		foreach ( $t_bugnote_stats as $t_item ) {
			$t_user_summary[$t_item[$t_name_field]] = 0;
		}

		# Calculate the totals
		foreach ( $t_bugnote_stats as $t_item ) {
			$t_sum_in_minutes += $t_item['sum_time_tracking'];
			$t_user_summary[$t_item[$t_name_field]] += $t_item['sum_time_tracking'];

			$t_item['sum_time_tracking'] = \Flickerbox\Database::minutes_to_hhmm( $t_item['sum_time_tracking'] );
			if( $t_item['bug_id'] != $t_prev_id ) {
				$t_link = sprintf( \Flickerbox\Lang::get( 'label' ), \Flickerbox\String::get_bug_view_link( $t_item['bug_id'] ) ) . \Flickerbox\Lang::get( 'word_separator' ) . \Flickerbox\String::display( $t_item['summary'] );
				echo '<tr class="row-category-history"><td colspan="4">' . $t_link . '</td></tr>';
				$t_prev_id = $t_item['bug_id'];
			}
?>
	<tr>
		<td class="small-caption">
			<?php echo $t_item[$t_name_field] ?>
		</td>
		<td class="small-caption">
			<?php echo $t_item['sum_time_tracking'] ?>
		</td>
<?php		if( $t_cost_col ) { ?>
		<td class="small-caption right">
			<?php echo \Flickerbox\String::attribute( number_format( $t_item['cost'], 2 ) ); ?>
		</td>
<?php		} ?>
	</tr>

<?php	} # end for loop ?>

	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo \Flickerbox\Lang::get( 'total_time' ); ?>
		</td>
		<td class="small-caption bold">
			<?php echo \Flickerbox\Database::minutes_to_hhmm( $t_sum_in_minutes ); ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo \Flickerbox\String::attribute( number_format( $t_sum_in_minutes * $f_bugnote_cost / 60, 2 ) ); ?>
		</td>
<?php 	} ?>
	</tr>
</table>

<br />
<br />

<table class="width100" cellspacing="0">
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo \Flickerbox\Lang::get( $t_name_field ) ?>
		</td>
		<td class="small-caption bold">
			<?php echo \Flickerbox\Lang::get( 'time_tracking' ) ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo \Flickerbox\Lang::get( 'time_tracking_cost' ) ?>
		</td>
<?php	} ?>
	</tr>

<?php
	foreach ( $t_user_summary as $t_username => $t_total_time ) {
?>
	<tr>
		<td class="small-caption">
			<?php echo $t_username; ?>
		</td>
		<td class="small-caption">
			<?php echo \Flickerbox\Database::minutes_to_hhmm( $t_total_time ); ?>
		</td>
<?php		if( $t_cost_col ) { ?>
		<td class="small-caption right">
			<?php echo \Flickerbox\String::attribute( number_format( $t_total_time * $f_bugnote_cost / 60, 2 ) ); ?>
		</td>
<?php		} ?>
	</tr>
<?php	} ?>
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo \Flickerbox\Lang::get( 'total_time' ); ?>
		</td>
		<td class="small-caption bold">
			<?php echo \Flickerbox\Database::minutes_to_hhmm( $t_sum_in_minutes ); ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo \Flickerbox\String::attribute( number_format( $t_sum_in_minutes * $f_bugnote_cost / 60, 2 ) ); ?>
		</td>
<?php	} ?>
	</tr>
</table>

<?php
	} # end if
	\Flickerbox\Collapse::closed( 'bugnotestats' );
?>

<table class="width100" cellspacing="0">
	<tr>
		<td class="form-title" colspan="4">
			<?php
				\Flickerbox\Collapse::icon( 'bugnotestats' );
				echo \Flickerbox\Lang::get( 'time_tracking' )
			?>
		</td>
	</tr>
</table>

<?php
	\Flickerbox\Collapse::end( 'bugnotestats' );
