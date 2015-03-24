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
 * Update Custom Field Configuration
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
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */




\Core\Form::security_validate( 'manage_custom_field_update' );

\Core\Auth::reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_custom_fields_threshold' ) );

$f_field_id						= \Core\GPC::get_int( 'field_id' );
$f_return						= strip_tags( \Core\GPC::get_string( 'return', 'manage_custom_field_page.php' ) );
$t_values['name']				= \Core\GPC::get_string( 'name' );
$t_values['type']				= \Core\GPC::get_int( 'type' );
$t_values['possible_values']	= \Core\GPC::get_string( 'possible_values' );
$t_values['default_value']		= \Core\GPC::get_string( 'default_value' );
$t_values['valid_regexp']		= \Core\GPC::get_string( 'valid_regexp' );
$t_values['access_level_r']		= \Core\GPC::get_int( 'access_level_r' );
$t_values['access_level_rw']	= \Core\GPC::get_int( 'access_level_rw' );
$t_values['length_min']			= \Core\GPC::get_int( 'length_min' );
$t_values['length_max']			= \Core\GPC::get_int( 'length_max' );
$t_values['display_report']		= \Core\GPC::get_bool( 'display_report' );
$t_values['display_update']		= \Core\GPC::get_bool( 'display_update' );
$t_values['display_resolved']	= \Core\GPC::get_bool( 'display_resolved' );
$t_values['display_closed']		= \Core\GPC::get_bool( 'display_closed' );
$t_values['require_report']		= \Core\GPC::get_bool( 'require_report' );
$t_values['require_update']		= \Core\GPC::get_bool( 'require_update' );
$t_values['require_resolved']	= \Core\GPC::get_bool( 'require_resolved' );
$t_values['require_closed']		= \Core\GPC::get_bool( 'require_closed' );
$t_values['filter_by']			= \Core\GPC::get_bool( 'filter_by' );

custom_field_update( $f_field_id, $t_values );

\Core\Form::security_purge( 'manage_custom_field_update' );

\Core\HTML::page_top( null, $f_return );

\Core\HTML::operation_successful( $f_return );

\Core\HTML::page_bottom();
