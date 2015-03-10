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
 * This is the first page a user sees when they login to the bugtracker
 * News is displayed which can notify users of any important changes
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
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 * @uses rss_api.php
 */

require_once( 'core.php' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_bug_threshold' ) );

$f_offset = \Core\GPC::get_int( 'offset', 0 );

$t_project_id = \Core\Helper::get_current_project();

$t_rss_enabled = \Core\Config::mantis_get( 'rss_enabled' );

if( OFF != $t_rss_enabled && \Core\News::is_enabled() ) {
	$t_rss_link = \Core\RSS::get_news_feed_url( $t_project_id );
	\Core\HTML::set_rss_link( $t_rss_link );
}

\Core\HTML::page_top( \Core\Lang::get( 'main_link' ) );

if( !\Core\Current_User::is_anonymous() ) {
	$t_current_user_id = \Core\Auth::get_current_user_id();
	$t_hide_status = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
	echo '<div class="quick-summary-left">';
	echo \Core\Lang::get( 'open_and_assigned_to_me_label' ) . \Core\Lang::get( 'word_separator' );
	\Core\Print_Util::link( 'view_all_set.php?type=1&handler_id=' . $t_current_user_id . '&hide_status=' . $t_hide_status, \Core\Current_User::get_assigned_open_bug_count(), false, 'subtle' );
	echo '</div>';

	echo '<div class="quick-summary-right">';
	echo \Core\Lang::get( 'open_and_reported_to_me_label' ) . \Core\Lang::get( 'word_separator' );
	\Core\Print_Util::link( 'view_all_set.php?type=1&reporter_id=' . $t_current_user_id . '&hide_status=' .$t_hide_status, \Core\Current_User::get_reported_open_bug_count(), false, 'subtle' );
	echo '</div>';

	echo '<div class="quick-summary-left">';
	echo \Core\Lang::get( 'last_visit_label' ) . \Core\Lang::get( 'word_separator' );
	echo date( \Core\Config::mantis_get( 'normal_date_format' ), \Core\Current_User::get_field( 'last_visit' ) );
	echo '</div>';
}

if( \Core\News::is_enabled() ) {
	$t_news_rows = \Core\News::get_limited_rows( $f_offset, $t_project_id );
	$t_news_count = count( $t_news_rows );

	if( $t_news_count ) {
		echo '<div id="news-items">';
		# Loop through results
		for( $i = 0; $i < $t_news_count; $i++ ) {
			$t_row = $t_news_rows[$i];

			# only show VS_PRIVATE posts to configured threshold and above
			if( ( VS_PRIVATE == $t_row['view_state'] ) &&
				 !\Core\Access::has_project_level( \Core\Config::mantis_get( 'private_news_threshold' ) ) ) {
				continue;
			}

			\Core\Print_Util::news_entry_from_row( $t_row );
		}  # end for loop
		echo '</div>';
	}

	echo '<div id="news-menu">';

	\Core\Print_Util::bracket_link( 'news_list_page.php', \Core\Lang::get( 'archives' ) );
	$t_news_view_limit = \Core\Config::mantis_get( 'news_view_limit' );
	$f_offset_next = $f_offset + $t_news_view_limit;
	$f_offset_prev = $f_offset - $t_news_view_limit;

	if( $f_offset_prev >= 0 ) {
		\Core\Print_Util::bracket_link( 'main_page.php?offset=' . $f_offset_prev, \Core\Lang::get( 'newer_news_link' ) );
	}

	if( $t_news_count == $t_news_view_limit ) {
		\Core\Print_Util::bracket_link( 'main_page.php?offset=' . $f_offset_next, \Core\Lang::get( 'older_news_link' ) );
	}

	if( OFF != $t_rss_enabled ) {
		\Core\Print_Util::bracket_link( $t_rss_link, \Core\Lang::get( 'rss' ) );
	}

	echo '</div>';
}

\Core\HTML::page_bottom();
