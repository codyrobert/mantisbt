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
 * Display summary page of Statistics
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
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses summary_api.php
 * @uses user_api.php
 */



$f_project_id = \Core\GPC::get_int( 'project_id', \Core\Helper::get_current_project() );

# Override the current page to make sure we get the appropriate project-specific configuration
$g_project_override = $f_project_id;

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_summary_threshold' ) );

$t_user_id = \Core\Auth::get_current_user_id();

$t_project_ids = \Core\User::get_all_accessible_projects( $t_user_id, $f_project_id );
$t_specific_where = \Core\Helper::project_specific_where( $f_project_id, $t_user_id );

$t_resolved = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
# the issue may have passed through the status we consider resolved
#  (e.g., bug is CLOSED, not RESOLVED). The linkage to the history field
#  will look up the most recent 'resolved' status change and return it as well
$t_query = 'SELECT b.id, b.date_submitted, b.last_updated, MAX(h.date_modified) as hist_update, b.status
	FROM {bug} b LEFT JOIN {bug_history} h
		ON b.id = h.bug_id  AND h.type=0 AND h.field_name=\'status\' AND h.new_value=' . \Core\Database::param() . '
		WHERE b.status >=' . \Core\Database::param() . ' AND ' . $t_specific_where . '
		GROUP BY b.id, b.status, b.date_submitted, b.last_updated
		ORDER BY b.id ASC';
$t_result = \Core\Database::query( $t_query, array( $t_resolved, $t_resolved ) );
$t_bug_count = 0;

$t_bug_id       = 0;
$t_largest_diff = 0;
$t_total_time   = 0;
while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
	$t_bug_count++;
	$t_date_submitted = $t_row['date_submitted'];
	$t_id = $t_row['id'];
	$t_status = $t_row['status'];
	if( $t_row['hist_update'] !== null ) {
		$t_last_updated   = $t_row['hist_update'];
	} else {
		$t_last_updated   = $t_row['last_updated'];
	}

	if( $t_last_updated < $t_date_submitted ) {
		$t_last_updated   = 0;
		$t_date_submitted = 0;
	}

	$t_diff = $t_last_updated - $t_date_submitted;
	$t_total_time = $t_total_time + $t_diff;
	if( $t_diff > $t_largest_diff ) {
		$t_largest_diff = $t_diff;
		$t_bug_id = $t_row['id'];
	}
}
if( $t_bug_count < 1 ) {
	$t_bug_count = 1;
}
$t_average_time 	= $t_total_time / $t_bug_count;

$t_largest_diff 	= number_format( $t_largest_diff / SECONDS_PER_DAY, 2 );
$t_total_time		= number_format( $t_total_time / SECONDS_PER_DAY, 2 );
$t_average_time 	= number_format( $t_average_time / SECONDS_PER_DAY, 2 );

$t_orct_arr = preg_split( '/[\)\/\(]/', \Core\Lang::get( 'orct' ), -1, PREG_SPLIT_NO_EMPTY );

$t_orcttab = '';
foreach ( $t_orct_arr as $t_orct_s ) {
	$t_orcttab .= '<td class="right">';
	$t_orcttab .= $t_orct_s;
	$t_orcttab .= '</td>';
}

\Core\HTML::page_top( \Core\Lang::get( 'summary_link' ) );
?>

<br />
<?php
\Core\HTML::print_summary_menu( 'summary_page.php' );
\Core\HTML::print_summary_submenu(); ?>
<br />

<div id="summary" class="section-container">

<h2><?php echo \Core\Lang::get( 'summary_title' ) ?></h2>
<br>

<!-- LEFT COLUMN -->
<div id="summary-left" class="summary-container">

	<?php if( 1 < count( $t_project_ids ) ) { ?>
	<!-- BY PROJECT -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_project' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_project(); ?>
	</table>
	<?php } ?>

	<!-- BY STATUS -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_status' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_enum( 'status' ) ?>
	</table>

	<!-- BY SEVERITY -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_severity' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_enum( 'severity' ) ?>
	</table>

	<!-- BY CATEGORY -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_category' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_category() ?>
	</table>

	<!-- TIME STATS -->
	<table>
		<thead>
			<tr class="row-category2">
				<th colspan="2"><?php echo \Core\Lang::get( 'time_stats' ) ?></th>
			</tr>
		</thead>
		<tr>
			<td><?php echo \Core\Lang::get( 'longest_open_bug' ) ?></td>
			<td><?php
				if( $t_bug_id > 0 ) {
					\Core\Print_Util::bug_link( $t_bug_id );
				}
			?></td>
		</tr>
		<tr>
			<td><?php echo \Core\Lang::get( 'longest_open' ) ?></td>
			<td><?php echo $t_largest_diff ?></td>
		</tr>
		<tr>
			<td><?php echo \Core\Lang::get( 'average_time' ) ?></td>
			<td><?php echo $t_average_time ?></td>
		</tr>
		<tr>
			<td><?php echo \Core\Lang::get( 'total_time' ) ?></td>
			<td><?php echo $t_total_time ?></td>
		</tr>
	</table>

	<!-- DEVELOPER STATS -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'developer_stats' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_developer() ?>
	</table>

</div>

<!-- RIGHT COLUMN -->
<div id="summary-right" class="summary-container">

	<!-- DEVELOPER STATS -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_date' ) ?></th>
				<td class="right"><?php echo \Core\Lang::get( 'opened' ); ?></td>
				<td class="right"><?php echo \Core\Lang::get( 'resolved' ); ?></td>
				<td class="right"><?php echo \Core\Lang::get( 'balance' ); ?></td>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_date( \Core\Config::mantis_get( 'date_partitions' ) ) ?>
	</table>

	<!-- MOST ACTIVE -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'most_active' ) ?></th>
				<td class="right"><?php echo \Core\Lang::get( 'score' ); ?></td>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_activity() ?>
	</table>

	<!-- LONGEST OPEN -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'longest_open' ) ?></th>
				<td class="right"><?php echo \Core\Lang::get( 'days' ); ?></td>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_age() ?>
	</table>

	<!-- BY RESOLUTION -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_resolution' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_enum( 'resolution' ) ?>
	</table>

	<!-- BY PRIORITY -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'by_priority' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_enum( 'priority' ) ?>
	</table>

	<!-- REPORTER STATS -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'reporter_stats' ) ?></th>
				<?php echo $t_orcttab ?>
			</tr>
		</thead>
		<?php \Core\Summary::print_by_reporter() ?>
	</table>

	<!-- REPORTER EFFECTIVENESS -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'reporter_effectiveness' ) ?></th>
				<td class="right"><?php echo \Core\Lang::get( 'severity' ); ?></td>
				<td class="right"><?php echo \Core\Lang::get( 'errors' ); ?></td>
				<td class="right"><?php echo \Core\Lang::get( 'total' ); ?></td>
			</tr>
		</thead>
		<?php \Core\Summary::print_reporter_effectiveness( \Core\Config::mantis_get( 'severity_enum_string' ), \Core\Config::mantis_get( 'resolution_enum_string' ) ) ?>
	</table>

</div>

<!-- BOTTOM -->
<div id="summary-bottom" class="summary-container">

	<!-- REPORTER BY RESOLUTION -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'reporter_by_resolution' ) ?></th>
				<?php
					$t_resolutions = \Core\Enum::getValues( \Core\Config::mantis_get( 'resolution_enum_string' ) );

					foreach ( $t_resolutions as $t_resolution ) {
						echo '<td class="right">', \Core\Helper::get_enum_element( 'resolution', $t_resolution ), "</td>\n";
					}

					echo '<td class="right">', \Core\Lang::get( 'percentage_errors' ), "</td>\n";
				?>
			</tr>
		</thead>
		<?php \Core\Summary::print_reporter_resolution( \Core\Config::mantis_get( 'resolution_enum_string' ) ) ?>
	</table>

	<!-- DEVELOPER BY RESOLUTION -->
	<table>
		<thead>
			<tr class="row-category2">
				<th><?php echo \Core\Lang::get( 'developer_by_resolution' ) ?></th>
				<?php
					$t_resolutions = \Core\Enum::getValues( \Core\Config::mantis_get( 'resolution_enum_string' ) );

					foreach ( $t_resolutions as $t_resolution ) {
						echo '<td class="right">', \Core\Helper::get_enum_element( 'resolution', $t_resolution ), "</td>\n";
					}

					echo '<td class="right">', \Core\Lang::get( 'percentage_fixed' ), "</td>\n";
				?>
			</tr>
		</thead>
		<?php \Core\Summary::print_developer_resolution( \Core\Config::mantis_get( 'resolution_enum_string' ) ) ?>
	</table>

</div>

</div>

<?php
\Core\HTML::page_bottom();
