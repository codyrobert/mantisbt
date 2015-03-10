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
 * Update Project Categories
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_proj_cat_update' );

auth_reauthenticate();

$f_category_id		= \Core\GPC::get_int( 'category_id' );
$f_project_id		= \Core\GPC::get_int( 'project_id', ALL_PROJECTS );
$f_name				= trim( \Core\GPC::get_string( 'name' ) );
$f_assigned_to		= \Core\GPC::get_int( 'assigned_to', 0 );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $f_project_id );

if( \Core\Utility::is_blank( $f_name ) ) {
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_row = \Core\Category::get_row( $f_category_id );
$t_old_name = $t_row['name'];
$t_project_id = $t_row['project_id'];

# check for duplicate
if( utf8_strtolower( $f_name ) != utf8_strtolower( $t_old_name ) ) {
	\Core\Category::ensure_unique( $t_project_id, $f_name );
}

\Core\Category::update( $f_category_id, $f_name, $f_assigned_to );

\Core\Form::security_purge( 'manage_proj_cat_update' );

if( $f_project_id == ALL_PROJECTS ) {
	$t_redirect_url = 'manage_proj_page.php';
} else {
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
}

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
