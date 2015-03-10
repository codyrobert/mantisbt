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
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'custom_field_api.php' );

\Flickerbox\Form::security_validate( 'manage_custom_field_delete' );

auth_reauthenticate();
\Flickerbox\Access::ensure_global_level( \Flickerbox\Config::mantis_get( 'manage_custom_fields_threshold' ) );

$f_field_id	= \Flickerbox\GPC::get_int( 'field_id' );
$f_return = strip_tags( \Flickerbox\GPC::get_string( 'return', 'manage_custom_field_page.php' ) );

$t_definition = custom_field_get_definition( $f_field_id );

if( 0 < count( custom_field_get_project_ids( $f_field_id ) ) ) {
	\Flickerbox\Helper::ensure_confirmed( \Flickerbox\Lang::get( 'confirm_used_custom_field_deletion' ) .
		'<br/>' . \Flickerbox\Lang::get( 'custom_field_label' ) . \Flickerbox\Lang::get( 'word_separator' ) . \Flickerbox\String::attribute( $t_definition['name'] ),
		\Flickerbox\Lang::get( 'field_delete_button' ) );
} else {
	\Flickerbox\Helper::ensure_confirmed( \Flickerbox\Lang::get( 'confirm_custom_field_deletion' ) .
		'<br/>' . \Flickerbox\Lang::get( 'custom_field_label' ) . \Flickerbox\Lang::get( 'word_separator' ) . \Flickerbox\String::attribute( $t_definition['name'] ),
		\Flickerbox\Lang::get( 'field_delete_button' ) );
}

custom_field_destroy( $f_field_id );

\Flickerbox\Form::security_purge( 'manage_custom_field_delete' );

\Flickerbox\HTML::page_top( null, $f_return );

\Flickerbox\HTML::operation_successful( $f_return );

\Flickerbox\HTML::page_bottom();
