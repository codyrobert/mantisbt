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
 * Delete a user from a project
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
 * @uses print_api.php
 * @uses project_api.php
 */



\Core\Form::security_validate( 'manage_user_proj_delete' );

\Core\Auth::reauthenticate();

$f_project_id = \Core\GPC::get_int( 'project_id' );
$f_user_id = \Core\GPC::get_int( 'user_id' );

\Core\User::ensure_exists( $f_user_id );

$t_user = \Core\User::get_row( $f_user_id );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'project_user_threshold' ), $f_project_id );
\Core\Access::ensure_project_level( $t_user['access_level'], $f_project_id );

$t_project_name = \Core\Project::get_name( $f_project_id );

# Confirm with the user
\Core\Helper::ensure_confirmed( \Core\Lang::get( 'remove_user_sure_msg' ) .
	'<br/>' . \Core\Lang::get( 'project_name_label' ) . \Core\Lang::get( 'word_separator' ) . $t_project_name,
	\Core\Lang::get( 'remove_user_button' ) );

\Core\Project::remove_user( $f_project_id, $f_user_id );

\Core\Form::security_purge( 'manage_user_proj_delete' );

$t_redirect_url = 'manage_user_edit_page.php?user_id=' .$f_user_id;

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
