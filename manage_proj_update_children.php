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
 * Update project hierarchy
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
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses project_hierarchy_api.php
 */



\Core\Form::security_validate( 'manage_proj_update_children' );

\Core\Auth::reauthenticate();

$f_project_id = \Core\GPC::get_int( 'project_id' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $f_project_id );

$t_subproject_ids = \Core\Current_User::get_accessible_subprojects( $f_project_id, true );
foreach ( $t_subproject_ids as $t_subproject_id ) {
	$f_inherit_child = \Core\GPC::get_bool( 'inherit_child_' . $t_subproject_id, false );
	\Core\Project\Hierarchy::update( $t_subproject_id, $f_project_id, $f_inherit_child );
}

\Core\Form::security_purge( 'manage_proj_update_children' );

\Core\Print_Util::successful_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
