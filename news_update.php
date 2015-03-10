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
 * Update News Post
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Core\News::ensure_enabled();

\Core\Form::security_validate( 'news_update' );

$f_news_id		= \Core\GPC::get_int( 'news_id' );
$f_project_id	= \Core\GPC::get_int( 'project_id' );
$f_view_state	= \Core\GPC::get_int( 'view_state' );
$f_headline		= \Core\GPC::get_string( 'headline' );
$f_announcement	= \Core\GPC::get_bool( 'announcement' );
$f_body			= \Core\GPC::get_string( 'body', '' );

$t_row = \Core\News::get_row( $f_news_id );

# Check both the old project and the new project
\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_news_threshold' ), $t_row['project_id'] );
\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_news_threshold' ), $f_project_id );

\Core\News::update( $f_news_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );

\Core\Form::security_purge( 'news_update' );

\Core\HTML::page_top();

echo '<div class="success-msg">';
echo \Core\Lang::get( 'operation_successful' );

echo '<br />';

\Core\Print_Util::bracket_link( 'news_edit_page.php?news_id=' . $f_news_id . '&action=edit', \Core\Lang::get( 'edit_link' ) );
\Core\Print_Util::bracket_link( 'news_menu_page.php', \Core\Lang::get( 'proceed' ) );

echo '<br /><br />';

\Core\Print_Util::news_entry( $f_headline, $f_body, $t_row['poster_id'], $f_view_state, $f_announcement, $t_row['date_posted'] );

echo '</div>';

\Core\HTML::page_bottom();
