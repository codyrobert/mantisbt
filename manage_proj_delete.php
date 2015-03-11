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
 * Project Delete
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_proj_delete' );

\Core\Auth::reauthenticate();

$f_project_id = \Core\GPC::get_int( 'project_id' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'delete_project_threshold' ), $f_project_id );

$t_project_name = \Core\Project::get_name( $f_project_id );

\Core\Helper::ensure_confirmed( \Core\Lang::get( 'project_delete_msg' ) .
		'<br/>' . \Core\Lang::get( 'project_name_label' ) . \Core\Lang::get( 'word_separator' ) . $t_project_name,
		\Core\Lang::get( 'project_delete_button' ) );

\Core\Project::delete( $f_project_id );

\Core\Form::security_purge( 'manage_proj_delete' );

# Don't leave the current project set to a deleted project -
#  set it to All Projects
if( \Core\Helper::get_current_project() == $f_project_id ) {
	\Core\Helper::set_current_project( ALL_PROJECTS );
}

\Core\Print_Util::header_redirect( 'manage_proj_page.php' );
