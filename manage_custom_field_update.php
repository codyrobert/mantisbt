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

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'print_api.php' );

\Flickerbox\Form::security_validate( 'manage_custom_field_update' );

auth_reauthenticate();
\Flickerbox\Access::ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

$f_field_id						= \Flickerbox\GPC::get_int( 'field_id' );
$f_return						= strip_tags( \Flickerbox\GPC::get_string( 'return', 'manage_custom_field_page.php' ) );
$t_values['name']				= \Flickerbox\GPC::get_string( 'name' );
$t_values['type']				= \Flickerbox\GPC::get_int( 'type' );
$t_values['possible_values']	= \Flickerbox\GPC::get_string( 'possible_values' );
$t_values['default_value']		= \Flickerbox\GPC::get_string( 'default_value' );
$t_values['valid_regexp']		= \Flickerbox\GPC::get_string( 'valid_regexp' );
$t_values['access_level_r']		= \Flickerbox\GPC::get_int( 'access_level_r' );
$t_values['access_level_rw']	= \Flickerbox\GPC::get_int( 'access_level_rw' );
$t_values['length_min']			= \Flickerbox\GPC::get_int( 'length_min' );
$t_values['length_max']			= \Flickerbox\GPC::get_int( 'length_max' );
$t_values['display_report']		= \Flickerbox\GPC::get_bool( 'display_report' );
$t_values['display_update']		= \Flickerbox\GPC::get_bool( 'display_update' );
$t_values['display_resolved']	= \Flickerbox\GPC::get_bool( 'display_resolved' );
$t_values['display_closed']		= \Flickerbox\GPC::get_bool( 'display_closed' );
$t_values['require_report']		= \Flickerbox\GPC::get_bool( 'require_report' );
$t_values['require_update']		= \Flickerbox\GPC::get_bool( 'require_update' );
$t_values['require_resolved']	= \Flickerbox\GPC::get_bool( 'require_resolved' );
$t_values['require_closed']		= \Flickerbox\GPC::get_bool( 'require_closed' );
$t_values['filter_by']			= \Flickerbox\GPC::get_bool( 'filter_by' );

custom_field_update( $f_field_id, $t_values );

\Flickerbox\Form::security_purge( 'manage_custom_field_update' );

\Flickerbox\HTML::page_top( null, $f_return );

\Flickerbox\HTML::operation_successful( $f_return );

\Flickerbox\HTML::page_bottom();
