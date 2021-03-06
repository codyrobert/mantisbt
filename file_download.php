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
 * Add file and redirect to the referring page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses gpc_api.php
 * @uses http_api.php
 * @uses utility_api.php
 */

$g_bypass_headers = true; # suppress headers as we will send our own later
define( 'COMPRESSION_DISABLED', true );



\Core\Auth::ensure_user_authenticated();

$f_show_inline = \Core\GPC::get_bool( 'show_inline', false );

# To prevent cross-domain inline hotlinking to attachments we require a CSRF
# token from the user to show any attachment inline within the browser.
# Without this security in place a malicious user could upload a HTML file
# attachment and direct a user to file_download.php?file_id=X&type=bug&show_inline=1
# and the malicious HTML content would be rendered in the user's browser,
# violating cross-domain security.
if( $f_show_inline ) {
	# Disable errors for form_security_validate as we need to send HTTP
	# headers prior to raising an error (the error handler
	# doesn't check that headers have been sent, it just
	# makes the assumption that they've been sent already).
	if( !@\Core\Form::security_validate( 'file_show_inline' ) ) {
		\Core\HTTP::all_headers();
		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
	}
}

$f_file_id = \Core\GPC::get_int( 'file_id' );
$f_type	= \Core\GPC::get_string( 'type' );

$c_file_id = (integer)$f_file_id;

# we handle the case where the file is attached to a bug
# or attached to a project as a project doc.
$t_query = '';
switch( $f_type ) {
	case 'bug':
		$t_query = 'SELECT * FROM {bug_file} WHERE id=' . \Core\Database::param();
		break;
	case 'doc':
		$t_query = 'SELECT * FROM {project_file} WHERE id=' . \Core\Database::param();
		break;
	default:
		\Core\Access::denied();
}
$t_result = \Core\Database::query( $t_query, array( $c_file_id ) );
$t_row = \Core\Database::fetch_array( $t_result );
extract( $t_row, EXTR_PREFIX_ALL, 'v' );

if( $f_type == 'bug' ) {
	$t_project_id = \Core\Bug::get_field( $v_bug_id, 'project_id' );
} else {
	$t_project_id = $v_project_id;
}

# Check access rights
switch( $f_type ) {
	case 'bug':
		if( !\Core\File::can_download_bug_attachments( $v_bug_id, (int)$v_user_id ) ) {
			\Core\Access::denied();
		}
		break;
	case 'doc':
		# Check if project documentation feature is enabled.
		if( OFF == \Core\Config::mantis_get( 'enable_project_documentation' ) ) {
			\Core\Access::denied();
		}

		\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_proj_doc_threshold' ), $v_project_id );
		break;
}

# throw away output buffer contents (and disable it) to protect download
while( @ob_end_clean() ) {
}

if( ini_get( 'zlib.output_compression' ) && function_exists( 'ini_set' ) ) {
	ini_set( 'zlib.output_compression', false );
}

\Core\HTTP::security_headers();

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );

# To fix an IE bug which causes problems when downloading
# attached files via HTTPS, we disable the "Pragma: no-cache"
# command when IE is used over HTTPS.
global $g_allow_file_cache;
if( \Core\HTTP::is_protocol_https() && \Core\HTTP::is_browser_internet_explorer() ) {
	# Suppress "Pragma: no-cache" header.
} else {
	if( !isset( $g_allow_file_cache ) ) {
		header( 'Pragma: no-cache' );
	}
}
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $v_date_added ) );

$t_upload_method = \Core\Config::mantis_get( 'file_upload_method' );
$t_filename = \Core\File::get_display_name( $v_filename );

# Content headers

# If finfo is available (always true for PHP >= 5.3.0) we can use it to determine the MIME type of files
$t_finfo = \Core\Utility::finfo_get_if_available();

$t_content_type = $v_file_type;

$t_content_type_override = \Core\File::get_content_type_override( $t_filename );
$t_file_info_type = false;

switch( $t_upload_method ) {
	case DISK:
		$t_local_disk_file = \Core\File::normalize_attachment_path( $v_diskfile, $t_project_id );
		if( file_exists( $t_local_disk_file ) && $t_finfo ) {
			$t_file_info_type = $t_finfo->file( $t_local_disk_file );
		}
		break;
	case DATABASE:
		if ( $t_finfo ) {
			$t_file_info_type = $t_finfo->buffer( $v_content );
		}
		break;
	default:
		trigger_error( ERROR_GENERIC, ERROR );

}

if( $t_file_info_type !== false ) {
	$t_content_type = $t_file_info_type;
}

if( $t_content_type_override ) {
	$t_content_type = $t_content_type_override;
}

# Don't allow inline flash
if( false !== strpos( $t_content_type, 'application/x-shockwave-flash' ) ) {
	\Core\HTTP::content_disposition_header( $t_filename );
} else {
	\Core\HTTP::content_disposition_header( $t_filename, $f_show_inline );
}

header( 'Content-Type: ' . $t_content_type );
header( 'Content-Length: ' . $v_filesize );

# Don't let Internet Explorer second-guess our content-type [1]
# Also disable Flash content-type sniffing [2]
# [1] http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
# [2] http://50.56.33.56/blog/?p=242
header( 'X-Content-Type-Options: nosniff' );

# dump file content to the connection.
switch( $t_upload_method ) {
	case DISK:
		readfile( $t_local_disk_file );
		break;
	case DATABASE:
		echo $v_content;
}
