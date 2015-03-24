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
 * Delete Project Documentation
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */



\Core\Form::security_validate( 'proj_doc_delete' );

# Check if project documentation feature is enabled.
if( OFF == \Core\Config::mantis_get( 'enable_project_documentation' ) ) {
	\Core\Access::denied();
}

$f_file_id = \Core\GPC::get_int( 'file_id' );

$t_project_id = \Core\File::get_field( $f_file_id, 'project_id', 'project' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'upload_project_file_threshold' ), $t_project_id );

$t_query = 'SELECT title FROM {project_file} WHERE id=' . \Core\Database::param();
$t_result = \Core\Database::query( $t_query, array( $f_file_id ) );
$t_title = \Core\Database::result( $t_result );

# Confirm with the user
\Core\Helper::ensure_confirmed( \Core\Lang::get( 'confirm_file_delete_msg' ) .
	'<br/>' . \Core\Lang::get( 'filename_label' ) . \Core\Lang::get( 'word_separator' ) . \Core\String::display( $t_title ),
	\Core\Lang::get( 'file_delete_button' ) );

\Core\File::delete( $f_file_id, 'project' );

\Core\Form::security_purge( 'proj_doc_delete' );

$t_redirect_url = 'proj_doc_page.php';

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
