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
 * Update Project
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
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses project_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_proj_update' );

\Core\Auth::reauthenticate();

$f_project_id 	= \Core\GPC::get_int( 'project_id' );
$f_name 		= \Core\GPC::get_string( 'name' );
$f_description 	= \Core\GPC::get_string( 'description' );
$f_status 		= \Core\GPC::get_int( 'status' );
$f_view_state 	= \Core\GPC::get_int( 'view_state' );
$f_file_path 	= \Core\GPC::get_string( 'file_path', '' );
$f_enabled	 	= \Core\GPC::get_bool( 'enabled' );
$f_inherit_global = \Core\GPC::get_bool( 'inherit_global', 0 );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $f_project_id );

\Core\Project::update( $f_project_id, $f_name, $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled, $f_inherit_global );
\Core\Event::signal( 'EVENT_MANAGE_PROJECT_UPDATE', array( $f_project_id ) );

\Core\Form::security_purge( 'manage_proj_update' );

\Core\Print_Util::header_redirect( 'manage_proj_page.php' );
