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
 * Remove Project Category
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Flickerbox\Form::security_validate( 'manage_proj_cat_delete' );

auth_reauthenticate();

$f_category_id = \Flickerbox\GPC::get_int( 'id' );
$f_project_id = \Flickerbox\GPC::get_int( 'project_id' );

$t_row = \Flickerbox\Category::get_row( $f_category_id );
$t_name = \Flickerbox\Category::full_name( $f_category_id );
$t_project_id = $t_row['project_id'];

\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'manage_project_threshold' ), $t_project_id );

# Protect the 'default category for moves' from deletion
$t_default_cat = 'default_category_for_moves';
$t_query = 'SELECT count(config_id) FROM {config} WHERE config_id = ' . \Flickerbox\Database::param() . ' AND value = ' . \Flickerbox\Database::param();
$t_default_cat_count = \Flickerbox\Database::result( \Flickerbox\Database::query( $t_query, array( $t_default_cat, $f_category_id ) ) );
if( $t_default_cat_count > 0 || $f_category_id == \Flickerbox\Config::get_global( $t_default_cat ) ) {
	trigger_error( ERROR_CATEGORY_CANNOT_DELETE_DEFAULT, ERROR );
}

# Get a bug count
$t_query = 'SELECT COUNT(id) FROM {bug} WHERE category_id=' . \Flickerbox\Database::param();
$t_bug_count = \Flickerbox\Database::result( \Flickerbox\Database::query( $t_query, array( $f_category_id ) ) );

# Confirm with the user
\Flickerbox\Helper::ensure_confirmed( sprintf( \Flickerbox\Lang::get( 'category_delete_sure_msg' ), \Flickerbox\String::display_line( $t_name ), $t_bug_count ),
	\Flickerbox\Lang::get( 'delete_category_button' ) );

\Flickerbox\Category::remove( $f_category_id );

\Flickerbox\Form::security_purge( 'manage_proj_cat_delete' );

if( $f_project_id == ALL_PROJECTS ) {
	$t_redirect_url = 'manage_proj_page.php';
} else {
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
}

\Flickerbox\HTML::page_top( null, $t_redirect_url );

\Flickerbox\HTML::operation_successful( $t_redirect_url );

\Flickerbox\HTML::page_bottom();
