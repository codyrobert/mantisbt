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
 * Remove Custom Fields
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
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */




\Core\Form::security_validate( 'manage_proj_custom_field_remove' );

\Core\Auth::reauthenticate();

$f_field_id = \Core\GPC::get_int( 'field_id' );
$f_project_id = \Core\GPC::get_int( 'project_id' );
$f_return = \Core\GPC::get_string( 'return', '' );

# We should check both since we are in the project section and an
# admin might raise the first threshold and not realize they need
# to raise the second
\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'manage_project_threshold' ), $f_project_id );
\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'custom_field_link_threshold' ), $f_project_id );

$t_definition = custom_field_get_definition( $f_field_id );

# Confirm with the user
\Core\Helper::ensure_confirmed( \Core\Lang::get( 'confirm_custom_field_unlinking' ) .
	'<br/>' . \Core\Lang::get( 'custom_field_label' ) . \Core\Lang::get( 'word_separator' ) . \Core\String::attribute( $t_definition['name'] ),
	\Core\Lang::get( 'field_remove_button' ) );

if( $f_return == 'custom_field' ) {
	$t_redirect_url = 'manage_custom_field_edit_page.php?field_id=' . $f_field_id;
} else {
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
}

custom_field_unlink( $f_field_id, $f_project_id );

\Core\Form::security_purge( 'manage_proj_custom_field_remove' );

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
