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
 * News View Page
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 */



\Core\News::ensure_enabled();

$f_news_id = \Core\GPC::get_int( 'news_id', null );

\Core\HTML::page_top();
?>

<?php
if( $f_news_id !== null ) {
	$t_project_id = \Core\News::get_field( $f_news_id, 'project_id' );
	if( \Core\News::is_private( $f_news_id ) ) {
		\Core\Access::ensure_project_level(	\Core\Config::mantis_get( 'private_news_threshold' ),
						$t_project_id );
	} else {
		\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_bug_threshold', null, null, $t_project_id ), $t_project_id );
	}

	\Core\Print_Util::news_string_by_news_id( $f_news_id );
}
?>

<div id="news-menu">
	<?php \Core\Print_Util::bracket_link( 'news_list_page.php', \Core\Lang::get( 'archives' ) ); ?>
</div>

<?php
\Core\HTML::page_bottom();
