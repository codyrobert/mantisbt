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
 * Update Project Document
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */



\Core\Form::security_validate( 'proj_doc_update' );

# Check if project documentation feature is enabled.
if( OFF == \Core\Config::mantis_get( 'enable_project_documentation' ) ||
	!\Core\File::is_uploading_enabled() ||
	!\Core\File::allow_project_upload() ) {
	\Core\Access::denied();
}

$f_file_id = \Core\GPC::get_int( 'file_id' );
$f_title = \Core\GPC::get_string( 'title' );
$f_description	= \Core\GPC::get_string( 'description' );
$f_file = \Core\GPC::get_file( 'file' );

$t_project_id = \Core\File::get_field( $f_file_id, 'project_id', 'project' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'upload_project_file_threshold' ), $t_project_id );

if( \Core\Utility::is_blank( $f_title ) ) {
	\Core\Error::parameters( \Core\Lang::get( 'title' ) );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

# @todo (thraxisp) this code should probably be integrated into file_api to share methods used to store files

if( isset( $f_file['tmp_name'] ) && is_uploaded_file( $f_file['tmp_name'] ) ) {
	\Core\File::ensure_uploaded( $f_file );

	$t_project_id = \Core\Helper::get_current_project();

	# grab the original file path and name
	$t_disk_file_name = \Core\File::get_field( $f_file_id, 'diskfile', 'project' );
	$t_file_path = dirname( $t_disk_file_name );

	# prepare variables for insertion
	$t_file_size = filesize( $f_file['tmp_name'] );
	$t_max_file_size = (int)min( \Core\Utility::ini_get_number( 'upload_max_filesize' ), \Core\Utility::ini_get_number( 'post_max_size' ), \Core\Config::mantis_get( 'max_file_size' ) );
	if( $t_file_size > $t_max_file_size ) {
		trigger_error( ERROR_FILE_TOO_BIG, ERROR );
	}

	$t_method = \Core\Config::mantis_get( 'file_upload_method' );
	switch( $t_method ) {
		case DISK:
			\Core\File::ensure_valid_upload_path( $t_file_path );

			if( file_exists( $t_disk_file_name ) ) {
				\Core\File::delete_local( $t_disk_file_name );
			}
			if( !move_uploaded_file( $f_file['tmp_name'], $t_disk_file_name ) ) {
				trigger_error( ERROR_FILE_MOVE_FAILED, ERROR );
			}
			chmod( $t_disk_file_name, \Core\Config::mantis_get( 'attachments_file_permissions' ) );

			$c_content = '';
			break;
		case DATABASE:
			$c_content = \Core\Database::prepare_binary_string( fread( fopen( $f_file['tmp_name'], 'rb' ), $f_file['size'] ) );
			break;
		default:
			# @todo Such errors should be checked in the admin checks
			trigger_error( ERROR_GENERIC, ERROR );
	}
	$t_query = 'UPDATE {project_file}
		SET title=' . \Core\Database::param() . ', description=' . \Core\Database::param() . ', date_added=' . \Core\Database::param() . ',
			filename=' . \Core\Database::param() . ', filesize=' . \Core\Database::param() . ', file_type=' .\Core\Database::param() . ', content=' .\Core\Database::param() . '
			WHERE id=' . \Core\Database::param();
	$t_result = \Core\Database::query( $t_query, array( $f_title, $f_description, \Core\Database::now(), $f_file['name'], $t_file_size, $f_file['type'], $c_content, $f_file_id ) );
} else {
	$t_query = 'UPDATE {project_file}
			SET title=' . \Core\Database::param() . ', description=' . \Core\Database::param() . '
			WHERE id=' . \Core\Database::param();
	$t_result = \Core\Database::query( $t_query, array( $f_title, $f_description, $f_file_id ) );
}

if( !$t_result ) {
	trigger_error( ERROR_GENERIC, ERROR );
}

\Core\Form::security_purge( 'proj_doc_update' );

$t_redirect_url = 'proj_doc_page.php';

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
