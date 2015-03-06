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
require_api( 'config_api.php' );
require_api( 'print_api.php' );

\Flickerbox\News::ensure_enabled();

\Flickerbox\Form::security_validate( 'news_update' );

$f_news_id		= \Flickerbox\GPC::get_int( 'news_id' );
$f_project_id	= \Flickerbox\GPC::get_int( 'project_id' );
$f_view_state	= \Flickerbox\GPC::get_int( 'view_state' );
$f_headline		= \Flickerbox\GPC::get_string( 'headline' );
$f_announcement	= \Flickerbox\GPC::get_bool( 'announcement' );
$f_body			= \Flickerbox\GPC::get_string( 'body', '' );

$t_row = \Flickerbox\News::get_row( $f_news_id );

# Check both the old project and the new project
\Flickerbox\Access::ensure_project_level( config_get( 'manage_news_threshold' ), $t_row['project_id'] );
\Flickerbox\Access::ensure_project_level( config_get( 'manage_news_threshold' ), $f_project_id );

\Flickerbox\News::update( $f_news_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );

\Flickerbox\Form::security_purge( 'news_update' );

\Flickerbox\HTML::page_top();

echo '<div class="success-msg">';
echo \Flickerbox\Lang::get( 'operation_successful' );

echo '<br />';

print_bracket_link( 'news_edit_page.php?news_id=' . $f_news_id . '&action=edit', \Flickerbox\Lang::get( 'edit_link' ) );
print_bracket_link( 'news_menu_page.php', \Flickerbox\Lang::get( 'proceed' ) );

echo '<br /><br />';

print_news_entry( $f_headline, $f_body, $t_row['poster_id'], $f_view_state, $f_announcement, $t_row['date_posted'] );

echo '</div>';

\Flickerbox\HTML::page_bottom();
