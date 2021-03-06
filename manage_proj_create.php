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
 * Create a project
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
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 */



\Core\Form::security_validate( 'manage_proj_create' );

\Core\Auth::reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'create_project_threshold' ) );

$f_name 		= \Core\GPC::get_string( 'name' );
$f_description 	= \Core\GPC::get_string( 'description' );
$f_view_state	= \Core\GPC::get_int( 'view_state' );
$f_status		= \Core\GPC::get_int( 'status' );
$f_file_path	= \Core\GPC::get_string( 'file_path', '' );
$f_inherit_global = \Core\GPC::get_bool( 'inherit_global', 0 );
$f_inherit_parent = \Core\GPC::get_bool( 'inherit_parent', 0 );

$f_parent_id	= \Core\GPC::get_int( 'parent_id', 0 );

if( 0 != $f_parent_id ) {
	\Core\Project::ensure_exists( $f_parent_id );
}

$t_project_id = \Core\Project::create( strip_tags( $f_name ), $f_description, $f_status, $f_view_state, $f_file_path, true, $f_inherit_global );

if( ( $f_view_state == VS_PRIVATE ) && ( false === \Core\Current_User::is_administrator() ) ) {
	$t_access_level = \Core\Access::get_global_level();
	$t_current_user_id = \Core\Auth::get_current_user_id();
	\Core\Project::add_user( $t_project_id, $t_current_user_id, $t_access_level );
}

if( 0 != $f_parent_id ) {
	\Core\Project\Hierarchy::add( $t_project_id, $f_parent_id, $f_inherit_parent );
}

\Core\Event::signal( 'EVENT_MANAGE_PROJECT_CREATE', array( $t_project_id ) );

\Core\Form::security_purge( 'manage_proj_create' );

$t_redirect_url = 'manage_proj_page.php';

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
