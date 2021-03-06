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
 * List News
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 * @uses string_api.php
 */



\Core\News::ensure_enabled();

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_bug_threshold' ) );

\Core\HTML::page_top();
?>

<br />
<?php
# Select the news posts
$t_rows = \Core\News::get_rows( \Core\Helper::get_current_project() );
$t_count = count( $t_rows );

if( $t_count > 0 ) { ?>
	<ul><?php
	# Loop through results
	for( $i=0; $i < $t_count; $i++ ) {
		extract( $t_rows[$i], EXTR_PREFIX_ALL, 'v' );
		if( VS_PRIVATE == $v_view_state && !\Core\Access::has_project_level( \Core\Config::mantis_get( 'private_news_threshold' ), $v_project_id ) ) {
			continue;
		}

		$v_headline 	= \Core\String::display( $v_headline );
		$v_date_posted 	= date( \Core\Config::mantis_get( 'complete_date_format' ), $v_date_posted ); ?>
		<li>
			<span class="news-date-posted"><?php echo $v_date_posted; ?></span>
			<span class="news-headline"><a href="news_view_page.php?news_id=<?php echo $v_id; ?>"><?php echo $v_headline; ?></a></span>
			<span class="news-author"><?php echo \Core\Prepare::user_name( $v_poster_id ); ?></span><?php
			if( 1 == $v_announcement ) { ?>
				<span class="news-announcement"><?php echo \Core\Lang::get( 'announcement' ); ?></span><?php
			}
			if( VS_PRIVATE == $v_view_state ) { ?>
				<span class="news-private"><?php echo \Core\Lang::get( 'private' ); ?></span><?php
			} ?>
		</li><?php
	}  	# end for loop ?>
	</ul><?php
}

\Core\HTML::page_bottom();
