<?php
namespace Core;


/**
 * Implementation of the abstract MantisBT session interface using
 * standard PHP sessions stored on the server's filesystem according
 * to PHP's session.* settings in 'php.ini'.
 */
class MantisPHPSession extends MantisSession {
	/**
	 * Constructor
	 * @param integer $p_session_id The session id.
	 */
	function __construct( $p_session_id = null ) {
		global $g_cookie_secure_flag_enabled;

		$this->key = hash( 'whirlpool', 'session_key' . \Core\Config::get_global( 'crypto_master_salt' ), false );

		# Save session information where specified or with PHP's default
		$t_session_save_path = \Core\Config::get_global( 'session_save_path' );
		if( $t_session_save_path ) {
			session_save_path( $t_session_save_path );
		}

		# Handle session cookie and caching
		session_cache_limiter( 'nocache' ); // private_no_expire
		session_set_cookie_params( 0, \Core\Config::mantis_get( 'cookie_path' ), \Core\Config::mantis_get( 'cookie_domain' ), $g_cookie_secure_flag_enabled, true );

		# Handle existent session ID
		if( !is_null( $p_session_id ) ) {
			session_id( $p_session_id );
		}

		# Initialize the session
		session_start();
		$this->id = session_id();

		# Initialize the keyed session store
		if( !isset( $_SESSION[$this->key] ) ) {
			$_SESSION[$this->key] = array();
		}
	}

	/**
	 * get session data
	 * @param string $p_name    The name of the value to set.
	 * @param mixed  $p_default The value to set.
	 * @return string
	 */
	function get( $p_name, $p_default = null ) {
		if( isset( $_SESSION[$this->key][$p_name] ) ) {
			return unserialize( $_SESSION[$this->key][$p_name] );
		}

		if( func_num_args() > 1 ) {
			return $p_default;
		}

		\Core\Error::parameters( $p_name );
		trigger_error( ERROR_SESSION_VAR_NOT_FOUND, ERROR );
	}

	/**
	 * set session data
	 * @param string $p_name  The name of the value to set.
	 * @param mixed  $p_value The value to set.
	 * @return void
	 */
	function set( $p_name, $p_value ) {
		$_SESSION[$this->key][$p_name] = serialize( $p_value );
	}

	/**
	 * delete session data
	 * @param string $p_name The name of the value to set.
	 * @return void
	 */
	function delete( $p_name ) {
		unset( $_SESSION[$this->key][$p_name] );
	}

	/**
	 * destroy session
	 * @return void
	 */
	function destroy() {
		if( isset( $_COOKIE[session_name()] ) && !headers_sent() ) {
			\Core\GPC::set_cookie( session_name(), '', time() - 42000 );
		}

		unset( $_SESSION[$this->key] );
	}
}
