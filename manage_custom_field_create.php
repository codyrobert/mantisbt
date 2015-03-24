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
 * Custom Field Configuration
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
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */




\Core\Form::security_validate( 'manage_custom_field_create' );

\Core\Auth::reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_custom_fields_threshold' ) );

$f_name	= \Core\GPC::get_string( 'name' );

$t_field_id = custom_field_create( $f_name );

if( ON == \Core\Config::mantis_get( 'custom_field_edit_after_create' ) ) {
	$t_redirect_url = 'manage_custom_field_edit_page.php?field_id=' . $t_field_id;
} else {
	$t_redirect_url = 'manage_custom_field_page.php';
}

\Core\Form::security_purge( 'manage_custom_field_create' );

\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
