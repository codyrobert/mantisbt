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
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */




\Core\Form::security_validate( 'manage_custom_field_proj_add' );

\Core\Auth::reauthenticate();

$f_field_id = \Core\GPC::get_int( 'field_id' );
$f_project_id = \Core\GPC::get_int_array( 'project_id', array() );
$f_sequence	= \Core\GPC::get_int( 'sequence' );

$t_manage_project_threshold = \Core\Config::mantis_get( 'manage_project_threshold' );

foreach ( $f_project_id as $t_proj_id ) {
	if( \Core\Access::has_project_level( $t_manage_project_threshold, $t_proj_id ) ) {
		if( !custom_field_is_linked( $f_field_id, $t_proj_id ) ) {
			custom_field_link( $f_field_id, $t_proj_id );
		}

		custom_field_set_sequence( $f_field_id, $t_proj_id, $f_sequence );
	}
}

\Core\Form::security_purge( 'manage_custom_field_proj_add' );

\Core\Print_Util::header_redirect( 'manage_custom_field_edit_page.php?field_id=' . $f_field_id );
