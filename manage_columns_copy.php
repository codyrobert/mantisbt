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
 * Copy Columns between projects
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_columns_copy' );

auth_reauthenticate();

$f_project_id		= \Core\GPC::get_int( 'project_id' );
$f_other_project_id	= \Core\GPC::get_int( 'other_project_id' );
$f_copy_from		= \Core\GPC::get_bool( 'copy_from' );
$f_copy_to			= \Core\GPC::get_bool( 'copy_to' );
$f_manage_page		= \Core\GPC::get_bool( 'manage_page' );

if( $f_copy_from ) {
	$t_src_project_id = $f_other_project_id;
	$t_dst_project_id = $f_project_id;
} else if( $f_copy_to ) {
	$t_src_project_id = $f_project_id;
	$t_dst_project_id = $f_other_project_id;
} else {
	trigger_error( ERROR_GENERIC, ERROR );
}

# only admins can set global defaults.for ALL_PROJECT
if( $f_manage_page && $t_dst_project_id == ALL_PROJECTS && !\Core\Current_User::is_administrator() ) {
	\Core\Access::denied();
}

# only MANAGERS can set global defaults.for a project
if( $f_manage_page && $t_dst_project_id != ALL_PROJECTS ) {
	\Core\Access::ensure_project_level( MANAGER, $t_dst_project_id );
}

# user should only be able to set columns for a project that is accessible.
if( $t_dst_project_id != ALL_PROJECTS ) {
	\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_bug_threshold', null, null, $t_dst_project_id ), $t_dst_project_id );
}

# Calculate the user id to set the configuration for.
if( $f_manage_page ) {
	$t_user_id = NO_USER;
} else {
	$t_user_id = \Core\Auth::get_current_user_id();
}

$t_all_columns = \Core\Columns::get_all();
$t_default = null;

$t_view_issues_page_columns = \Core\Config::mantis_get( 'view_issues_page_columns', $t_default, $t_user_id, $t_src_project_id );
$t_view_issues_page_columns = \Core\Columns::remove_invalid( $t_view_issues_page_columns, $t_all_columns );

$t_print_issues_page_columns = \Core\Config::mantis_get( 'print_issues_page_columns', $t_default, $t_user_id, $t_src_project_id );
$t_print_issues_page_columns = \Core\Columns::remove_invalid( $t_print_issues_page_columns, $t_all_columns );

$t_csv_columns = \Core\Config::mantis_get( 'csv_columns', $t_default, $t_user_id, $t_src_project_id );
$t_csv_columns = \Core\Columns::remove_invalid( $t_csv_columns, $t_all_columns );

$t_excel_columns = \Core\Config::mantis_get( 'excel_columns', $t_default, $t_user_id, $t_src_project_id );
$t_excel_columns = \Core\Columns::remove_invalid( $t_excel_columns, $t_all_columns );

\Core\Config::mantis_set( 'view_issues_page_columns', $t_view_issues_page_columns, $t_user_id, $t_dst_project_id );
\Core\Config::mantis_set( 'print_issues_page_columns', $t_print_issues_page_columns, $t_user_id, $t_dst_project_id );
\Core\Config::mantis_set( 'csv_columns', $t_csv_columns, $t_user_id, $t_dst_project_id );
\Core\Config::mantis_set( 'excel_columns', $t_excel_columns, $t_user_id, $t_dst_project_id );

\Core\Form::security_purge( 'manage_columns_copy' );

$t_redirect_url = $f_manage_page ? 'manage_config_columns_page.php' : 'account_manage_columns_page.php';
\Core\Print_Util::header_redirect( $t_redirect_url );
