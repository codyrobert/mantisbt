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
 * Generate custom graphs e.g. by status for given date range
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @uses core.php
 * @uses Period.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses plugin_api.php
 */

require_once( 'core.php' );
\Core\Plugin::require_api( 'core/Period.php' );

\Core\HTML::require_js( 'jscalendar/calendar.js' );
\Core\HTML::require_js( 'jscalendar/lang/calendar-en.js' );
\Core\HTML::require_js( 'jscalendar/calendar-setup.js' );
\Core\HTML::require_css( 'calendar-blue.css' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_summary_threshold' ) );

$f_interval = \Core\GPC::get_int( 'interval', 0 );
$t_today = date( 'Y-m-d' );
$f_type = \Core\GPC::get_int( 'graph_type', 0 );
$f_show_as_table = \Core\GPC::get_bool( 'show_table', false );

\Core\HTML::page_top1( \Core\Plugin::langget( 'graph_page' ) );
$t_path = \Core\Config::mantis_get( 'path' );
\Core\HTML::page_top2();

$t_period = new Period();
$t_period->set_period_from_selector( 'interval' );
$t_types = array(
				0 => \Core\Plugin::langget( 'select' ),
				2 => \Core\Plugin::langget( 'select_bystatus' ),
				3 => \Core\Plugin::langget( 'select_summbystatus' ),
				4 => \Core\Plugin::langget( 'select_bycat' ),
				6 => \Core\Plugin::langget( 'select_both' ),
		   );

$t_show = array(
				0 => \Core\Plugin::langget( 'show_as_graph' ),
				1 => \Core\Plugin::langget( 'show_as_table' ),
		  );
?>
		<form id="graph_form" method="post" action="<?php echo \Core\Plugin::page( 'bug_graph_page.php' ); ?>">
			<table class="width100" cellspacing="1">

				<tr>
					<td>
						<?php echo \Core\Print_Util::get_dropdown( $t_types, 'graph_type', $f_type ); ?>
					</td>
					<td>
						<?php echo $t_period->period_selector( 'interval' ); ?>
					</td>
					<td>
						<?php echo \Core\Print_Util::get_dropdown( $t_show, 'show_table', $f_show_as_table ? 1 : 0 ); ?>
					</td>
					<td>
						<input type="submit" class="button" name="show" value="<?php echo \Core\Plugin::langget( 'show_graph' ); ?>"/>
					</td>
				</tr>
			</table>
		</form>
<?php
# build the graphs if both an interval and graph type are selected
if( ( 0 != $f_type ) && ( $f_interval > 0 ) && ( \Core\GPC::get( 'show', '' ) != '') ) {
	$t_width = \Core\Plugin::config_get( 'window_width' );
	$t_summary = ( $f_type % 2 ) != 0;
	$t_body = (int)( $f_type / 2 );
	$f_start = $t_period->get_start_formatted();
	$f_end = $t_period->get_end_formatted();
	if( ($t_body == 1 ) || ($t_body == 3) ) {
		if( $f_show_as_table ) {
			include(
				\Core\Config::get_global( 'plugin_path' ) . \Core\Plugin::get_current() . '/pages/bug_graph_bystatus.php'
			);
		} else {
			echo '<br /><img src="' . \Core\Plugin::page( 'bug_graph_bystatus.php' )
				. '&amp;width=600&amp;interval=' . $f_interval
				. '&amp;start_date=' . $f_start . '&amp;end_date=' . $f_end
				. '&amp;summary=' . $t_summary . '&amp;show_table=0" alt="Bug Graph" />';
		}
	}
	if( ($t_body == 2 ) || ($t_body == 3) ) {
		if( $f_show_as_table ) {
			include( \Core\Config::get_global( 'plugin_path' ) . \Core\Plugin::get_current() .  '/pages/bug_graph_bycategory.php' );
		} else {
			echo '<br /><img src="' . \Core\Plugin::page( 'bug_graph_bycategory.php' )
				. '&amp;width=600&amp;interval=' . $f_interval
				. '&amp;start_date=' . $f_start . '&amp;end_date=' . $f_end
				. '&amp;summary=' . $t_summary . '&amp;show_table=0" alt="Bug Graph" />';
		}
	}
}

\Core\HTML::page_bottom();
