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
 * Delete Project Version
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
 * @uses version_api.php
 */



\Core\Form::security_validate( 'manage_proj_ver_delete' );

\Core\Auth::reauthenticate();

$f_version_id = \Core\GPC::get_int( 'version_id' );

$t_version_info = \Core\Version::get( $f_version_id );
$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $t_version_info->project_id;

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $t_version_info->project_id );

# Confirm with the user
\Core\Helper::ensure_confirmed( \Core\Lang::get( 'version_delete_sure' ) .
	'<br/>' . \Core\Lang::get( 'version_label' ) . \Core\Lang::get( 'word_separator' ) . \Core\String::display_line( $t_version_info->version ),
	\Core\Lang::get( 'delete_version_button' ) );

\Core\Version::remove( $f_version_id );

\Core\Form::security_purge( 'manage_proj_ver_delete' );

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
