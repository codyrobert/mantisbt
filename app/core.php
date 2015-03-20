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
 * MantisBT Core
 *
 * Initialises the MantisBT core, connects to the database, starts plugins and
 * performs other global operations that either help initialise MantisBT or
 * are required to be executed on every page load.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses collapse_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses config_defaults_inc.php
 * @uses config_inc.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses custom_constants_inc.php
 * @uses custom_functions_inc.php
 * @uses database_api.php
 * @uses event_api.php
 * @uses http_api.php
 * @uses lang_api.php
 * @uses mantis_offline.php
 * @uses plugin_api.php
 * @uses php_api.php
 * @uses user_pref_api.php
 * @uses wiki_api.php
 * @uses utf8/utf8.php
 * @uses utf8/str_pad.php
 */

/**
 * Define an autoload function to automatically load classes when referenced
 *
 * @param string $p_class Class name being autoloaded.
 * @return void
 */
function __autoload( $p_class ) {
	global $g_class_path;
	global $g_library_path;

	$t_require_path = $g_class_path . $p_class . '.class.php';

	if( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}

	$t_require_path = $g_library_path . 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.' . $p_class . '.inc.php';

	if( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}
}

# Register the autoload function to make it effective immediately
spl_autoload_register( '__autoload' );

# Load UTF8-capable string functions
define( 'UTF8', $g_library_path . 'utf8' );
require_lib( 'utf8/utf8.php' );
require_lib( 'utf8/str_pad.php' );

# Include PHP compatibility file

# Enforce our minimum PHP requirements
if( !\Core\PHP::version_at_least( PHP_MIN_VERSION ) ) {
	@ob_end_clean();
	echo '<strong>FATAL ERROR: Your version of PHP is too old. MantisBT requires PHP version ' . PHP_MIN_VERSION . ' or newer</strong><br />Your version of PHP is version ' . phpversion();
	die();
}

# Ensure that output is blank so far (output at this stage generally denotes
# that an error has occurred)
if( ( $t_output = ob_get_contents() ) != '' ) {
	echo 'Possible Whitespace/Error in Configuration File - Aborting. Output so far follows:<br />';
	echo var_dump( $t_output );
	die;
}
unset( $t_output );

# Start HTML compression handler (if enabled)
\Core\Compress::start_handler();

# If no configuration file exists, redirect the user to the admin page so
# they can complete installation and configuration of MantisBT
if( false === $t_config_inc_found ) {
	if( php_sapi_name() == 'cli' ) {
		echo 'Error: ' . $g_config_path . "config_inc.php file not found; ensure MantisBT is properly setup.\n";
		exit(1);
	}

	/*if( !( isset( $_SERVER['SCRIPT_NAME'] ) && ( 0 < strpos( $_SERVER['SCRIPT_NAME'], 'admin' ) ) ) ) {
		header( 'Content-Type: text/html' );
		# Temporary redirect (307) instead of Found (302) default
		header( 'Location: admin/install.php', true, 307 );
		# Make sure it's not cached
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		exit;
	}*/
}

# Initialise cryptographic keys
\Core\Crypto::init();

# Connect to the database

if( !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
	if( OFF == $g_use_persistent_connections ) {
		\Core\Database::connect( \Core\Config::get_global( 'dsn', false ), $g_hostname, $g_db_username, $g_db_password, $g_database_name, \Core\Config::get_global( 'db_schema' ) );
	} else {
		\Core\Database::connect( \Core\Config::get_global( 'dsn', false ), $g_hostname, $g_db_username, $g_db_password, $g_database_name, \Core\Config::get_global( 'db_schema' ), true );
	}
}

# Initialise plugins
if( !defined( 'PLUGINS_DISABLED' ) && !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
		\Core\Plugin::init_installed();
}

# Initialise Wiki integration
if( \Core\Config::get_global( 'wiki_enable' ) == ON ) {
		\Core\Wiki::init();
}

if( !isset( $g_login_anonymous ) ) {
	$g_login_anonymous = true;
}

# Set the current timezone
if( !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
	
	# To reduce overhead, we assume that the timezone configuration is valid,
	# i.e. it exists in timezone_identifiers_list(). If not, a PHP NOTICE will
	# be raised. Use admin checks to validate configuration.
	@date_default_timezone_set( \Core\Config::get_global( 'default_timezone' ) );
	$t_tz = @date_default_timezone_get();
	\Core\Config::set_global( 'default_timezone', $t_tz, true );

	if( \Core\Auth::is_user_authenticated() ) {
		# Determine the current timezone according to user's preferences
				$t_tz = \Core\User\Pref::get_pref( \Core\Auth::get_current_user_id(), 'timezone' );
		@date_default_timezone_set( $t_tz );
	}
	unset( $t_tz );
}

# Cache current user's collapse API data
if( !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
		\Core\Collapse::cache_token();
}

# Load custom functions
require_api( 'custom_function_api.php' );

if( file_exists( $g_config_path . 'custom_functions_inc.php' ) ) {
	require_once( $g_config_path . 'custom_functions_inc.php' );
}

# Set HTTP response headers
\Core\HTTP::all_headers();

# Push default language to speed calls to lang_get
if( !defined( 'LANG_LOAD_DISABLED' ) ) {
	\Core\Lang::push( \Core\Lang::get_default() );
}

# Signal plugins that the core system is loaded
if( !defined( 'PLUGINS_DISABLED' ) && !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
		\Core\Event::signal( 'EVENT_CORE_READY' );
}
