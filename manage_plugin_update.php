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
 * Plugin Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

/** @ignore */
define( 'PLUGINS_DISABLED', true );



\Core\Form::security_validate( 'manage_plugin_update' );

\Core\Auth::reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_plugin_threshold' ) );

$t_query = 'SELECT basename FROM {plugin}';
$t_result = \Core\Database::query( $t_query );

while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
	$t_basename = $t_row['basename'];

	$f_change = \Core\GPC::get_bool( 'change_'.$t_basename, 0 );

	if( !$f_change ) {
		continue;
	}

	$f_priority = \Core\GPC::get_int( 'priority_'.$t_basename, 3 );
	$f_protected = \Core\GPC::get_bool( 'protected_'.$t_basename, 0 );

	$t_query = 'UPDATE {plugin} SET priority=' . \Core\Database::param() . ', protected=' . \Core\Database::param() .
		' WHERE basename=' . \Core\Database::param();

	\Core\Database::query( $t_query, array( $f_priority, $f_protected, $t_basename ) );
}

\Core\Form::security_purge( 'manage_plugin_update' );

\Core\Print_Util::successful_redirect( 'manage_plugin_page.php' );
