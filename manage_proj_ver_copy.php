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
 * Copy Versions between projects
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
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses version_api.php
 */



\Core\Form::security_validate( 'manage_proj_ver_copy' );

\Core\Auth::reauthenticate();

$f_project_id		= \Core\GPC::get_int( 'project_id' );
$f_other_project_id	= \Core\GPC::get_int( 'other_project_id' );
$f_copy_from		= \Core\GPC::get_bool( 'copy_from' );
$f_copy_to			= \Core\GPC::get_bool( 'copy_to' );

\Core\Project::ensure_exists( $f_project_id );
\Core\Project::ensure_exists( $f_other_project_id );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $f_project_id );
\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $f_other_project_id );

if( $f_copy_from ) {
	$t_src_project_id = $f_other_project_id;
	$t_dst_project_id = $f_project_id;
} else if( $f_copy_to ) {
	$t_src_project_id = $f_project_id;
	$t_dst_project_id = $f_other_project_id;
} else {
	trigger_error( ERROR_VERSION_NO_ACTION, ERROR );
}

$t_rows = \Core\Version::get_all_rows( $t_src_project_id );

foreach ( $t_rows as $t_row ) {
	if( \Core\Version::is_unique( $t_row['version'], $t_dst_project_id ) ) {
		$t_version_id = \Core\Version::add( $t_dst_project_id, $t_row['version'], $t_row['released'], $t_row['description'], $t_row['date_order'] );
	}
}

\Core\Form::security_purge( 'manage_proj_ver_copy' );

\Core\Print_Util::header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
