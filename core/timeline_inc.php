<?php
# MantisBT - a php based bugtracking system

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

require_once( 'core.php' );

define( 'MAX_EVENTS', 50 );

$f_days = \Flickerbox\GPC::get_int( 'days', 0 );
$f_all = \Flickerbox\GPC::get_int( 'all', 0 );

$t_end_time = time() - ( $f_days * SECONDS_PER_DAY );
$t_start_time = $t_end_time - ( 7 * SECONDS_PER_DAY );
$t_events = \Flickerbox\Timeline::events( $t_start_time, $t_end_time );

echo '<div class="timeline">';

$t_heading = \Flickerbox\Lang::get( 'timeline_title' );

echo '<div class="heading">' . $t_heading . '</div>';

$t_short_date_format = \Flickerbox\Config::mantis_get( 'short_date_format' );

$t_next_days = ( $f_days - 7 ) > 0 ? $f_days - 7 : 0;
$t_prev_link = ' [<a href="my_view_page.php?days=' . ( $f_days + 7 ) . '">' . \Flickerbox\Lang::get( 'prev' ) . '</a>]';

if( $t_next_days != $f_days ) {
	$t_next_link = ' [<a href="my_view_page.php?days=' . $t_next_days . '">' . \Flickerbox\Lang::get( 'next' ) . '</a>]';
} else {
	$t_next_link = '';
}

echo '<div class="date-range">' . date( $t_short_date_format, $t_start_time ) . ' .. ' . date( $t_short_date_format, $t_end_time ) . $t_prev_link . $t_next_link . '</div>';
$t_events = \Flickerbox\Timeline::sort_events( $t_events );

$t_num_events = \Flickerbox\Timeline::print_events( $t_events, ( $f_all ? 0 : MAX_EVENTS ) );

# Don't display "More Events" link if there are no more entries to show
# Note: as of 2015-01-19, this does not cover the case of entries excluded
# by filtering (e.g. Status Change not in RESOLVED, CLOSED, REOPENED)
if( !$f_all && $t_num_events < count( $t_events )) {
	echo '<p>' . $t_prev_link = ' [ <a href="my_view_page.php?days=' . $f_days . '&amp;all=1">' . \Flickerbox\Lang::get( 'timeline_more' ) . '</a> ]</p>';
}

echo '</div>';
