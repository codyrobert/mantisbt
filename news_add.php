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
 * Add News
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Core\News::ensure_enabled();

\Core\Form::security_validate( 'news_add' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_news_threshold' ) );

$f_view_state	= \Core\GPC::get_int( 'view_state' );
$f_headline		= \Core\GPC::get_string( 'headline' );
$f_announcement	= \Core\GPC::get_bool( 'announcement' );
$f_body			= \Core\GPC::get_string( 'body' );

$t_news_id = \Core\News::create( \Core\Helper::get_current_project(), auth_get_current_user_id(), $f_view_state, $f_announcement, $f_headline, $f_body );

\Core\Form::security_purge( 'news_add' );

$t_news_row = \Core\News::get_row( $t_news_id );

\Core\HTML::page_top();

\Core\HTML::operation_successful( 'news_menu_page.php' );

\Core\Print_Util::news_entry_from_row( $t_news_row );

\Core\HTML::page_bottom();
