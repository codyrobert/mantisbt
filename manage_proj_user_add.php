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
 * Add User to Project
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

\Flickerbox\Form::security_validate( 'manage_proj_user_add' );

auth_reauthenticate();

$f_project_id	= \Flickerbox\GPC::get_int( 'project_id' );
$f_user_id		= \Flickerbox\GPC::get_int_array( 'user_id', array() );
$f_access_level	= \Flickerbox\GPC::get_int( 'access_level' );

# We should check both since we are in the project section and an
#  admin might raise the first threshold and not realize they need
#  to raise the second
\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'manage_project_threshold' ), $f_project_id );
\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'project_user_threshold' ), $f_project_id );

# Add user(s) to the current project
foreach( $f_user_id as $t_user_id ) {
	\Flickerbox\Project::add_user( $f_project_id, $t_user_id, $f_access_level );
}

\Flickerbox\Form::security_purge( 'manage_proj_user_add' );

\Flickerbox\Print_Util::header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
