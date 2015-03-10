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
 * A user to Project
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
 * @uses print_api.php
 * @uses project_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_user_proj_add' );

auth_reauthenticate();

$f_user_id		= \Core\GPC::get_int( 'user_id' );
$f_access_level	= \Core\GPC::get_int( 'access_level' );
$f_project_id	= \Core\GPC::get_int_array( 'project_id', array() );
$t_manage_user_threshold = \Core\Config::mantis_get( 'manage_user_threshold' );

\Core\User::ensure_exists( $f_user_id );

foreach ( $f_project_id as $t_proj_id ) {
	if( \Core\Access::has_project_level( $t_manage_user_threshold, $t_proj_id ) &&
		\Core\Access::has_project_level( $f_access_level, $t_proj_id ) ) {
		\Core\Project::add_user( $t_proj_id, $f_user_id, $f_access_level );
	}
}

\Core\Form::security_purge( 'manage_user_proj_add' );

\Core\Print_Util::header_redirect( 'manage_user_edit_page.php?user_id=' . $f_user_id );
