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
 * Add documentation to project
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
 * @uses error_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'proj_doc_add' );

# Check if project documentation feature is enabled.
if( OFF == \Core\Config::mantis_get( 'enable_project_documentation' ) ) {
	\Core\Access::denied();
}

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'upload_project_file_threshold' ) );

$f_title = \Core\GPC::get_string( 'title' );
$f_description = \Core\GPC::get_string( 'description' );
$f_file = gpc_get_file( 'file' );

if( \Core\Utility::is_blank( $f_title ) ) {
	\Core\Error::parameters( \Core\Lang::get( 'title' ) );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

\Core\File::add( 0, $f_file, 'project', $f_title, $f_description );

\Core\Form::security_purge( 'proj_doc_add' );

$t_redirect_url = 'proj_doc_page.php';

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
