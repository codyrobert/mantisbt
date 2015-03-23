<?php
/**
 * Before doing anything... check if MantisBT is down for maintenance
 *
 *   To make MantisBT 'offline' simply create a file called
 *   'mantis_offline.php' in the MantisBT root directory.
 *   Users are redirected to that file if it exists.
 *   If you have to test MantisBT while it's offline, add the
 *   parameter 'mbadmin=1' to the URL.
 */
if( file_exists( 'mantis_offline.php' ) && !isset( $_GET['mbadmin'] ) ) {
	include( 'mantis_offline.php' );
	exit;
}

$g_request_time = microtime( true );

ob_start();

# Load supplied constants
//require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'constant_inc.php' );

# Include default configuration settings
//require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'config_defaults_inc.php' );

# Load user-defined constants (if required)
if( file_exists( $g_config_path . 'custom_constants_inc.php' ) ) {
	require_once( $g_config_path . 'custom_constants_inc.php' );
}


/**
 * Define an API inclusion function to replace require_once
 *
 * @param string $p_api_name An API file name.
 * @return void
 */
function require_api( $p_api_name ) {
	static $s_api_included;
	global $g_core_path;
	if( !isset( $s_api_included[$p_api_name] ) ) {
		require_once( $g_core_path . $p_api_name );
		$t_new_globals = array_diff_key( get_defined_vars(), $GLOBALS, array( 't_new_globals' => 0 ) );
		foreach ( $t_new_globals as $t_global_name => $t_global_value ) {
			$GLOBALS[$t_global_name] = $t_global_value;
		}
		$s_api_included[$p_api_name] = 1;
	}
}

/**
 * Define an API inclusion function to replace require_once
 *
 * @param string $p_library_name A library file name.
 * @return void
 */
function require_lib( $p_library_name ) {
	static $s_libraries_included;
	global $g_library_path;
	if( !isset( $s_libraries_included[$p_library_name] ) ) {
		$t_library_file_path = $g_library_path . $p_library_name;
		if( !file_exists( $t_library_file_path ) ) {
			echo 'External library \'' . $t_library_file_path . '\' not found.';
			exit;
		}

		require_once( $t_library_file_path );
		$t_new_globals = array_diff_key( get_defined_vars(), $GLOBALS, array( 't_new_globals' => 0 ) );
		foreach ( $t_new_globals as $t_global_name => $t_global_value ) {
			$GLOBALS[$t_global_name] = $t_global_value;
		}
		$s_libraries_included[$p_library_name] = 1;
	}
}