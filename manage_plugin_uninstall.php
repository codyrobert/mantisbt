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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 */

/** @ignore */
define( 'PLUGINS_DISABLED', true );

require_once( 'core.php' );

\Flickerbox\Form::security_validate( 'manage_plugin_uninstall' );

auth_reauthenticate();
\Flickerbox\Access::ensure_global_level( \Flickerbox\Config::mantis_get( 'manage_plugin_threshold' ) );

# register plugins and metadata without initializing
\Flickerbox\Plugin::register_installed();

$f_basename = \Flickerbox\GPC::get_string( 'name' );
$t_plugin = \Flickerbox\Plugin::register( $f_basename, true );

\Flickerbox\Helper::ensure_confirmed( sprintf( \Flickerbox\Lang::get( 'plugin_uninstall_message' ), \Flickerbox\String::display_line( $t_plugin->name ) ), \Flickerbox\Lang::get( 'plugin_uninstall' ) );

if( !is_null( $t_plugin ) ) {
	\Flickerbox\Plugin::uninstall( $t_plugin );
} else {
	plugin_force_uninstall( $f_basename );
}

\Flickerbox\Form::security_purge( 'manage_plugin_uninstall' );

\Flickerbox\Print_Util::successful_redirect( 'manage_plugin_page.php' );
