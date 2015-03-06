<?php
namespace Flickerbox;


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
 * Session API
 *
 * Handles user/browser sessions in an extendable manner. New session handlers
 * can be added and configured without affecting how the API is used. Calls to
 * session_*() are appropriately directed at the session handler class as
 * chosen in config_inc.php.
 *
 * @package CoreAPI
 * @subpackage SessionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses php_api.php
 */

require_api( 'config_api.php' );


class Session
{

	/**
	 * Initialize the appropriate session handler.
	 * @param string $p_session_id Session ID.
	 * @return void
	 */
	static function init( $p_session_id = null ) {
		global $g_session, $g_session_handler;
	
		switch( \utf8_strtolower( $g_session_handler ) ) {
			case 'php':
				$g_session = new \Flickerbox\MantisPHPSession( $p_session_id );
				break;
			case 'memcached':
				# Not yet implemented
			default:
				trigger_error( ERROR_SESSION_HANDLER_INVALID, ERROR );
				break;
		}
	
		if( ON == config_get_global( 'session_validation' ) && \Flickerbox\Session::get( 'secure_session', false ) ) {
			\Flickerbox\Session::validate( $g_session );
		}
	}
	
	/**
	 * Validate the legitimacy of a session.
	 * Checks may include last-known IP address, or more.
	 * Triggers an error when the session is invalid.
	 * @param object $p_session Session object.
	 * @return void
	 */
	static function validate( $p_session ) {
		$t_user_ip = '';
		if( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$t_user_ip = trim( $_SERVER['REMOTE_ADDR'] );
		}
	
		if( is_null( $t_last_ip = $p_session->get( 'last_ip', null ) ) ) {
			# First session usage
			$p_session->set( 'last_ip', $t_user_ip );
	
		} else {
			# Check a continued session request
			if( $t_user_ip != $t_last_ip ) {
				\Flickerbox\Session::clean();
	
				trigger_error( ERROR_SESSION_NOT_VALID, WARNING );
	
				$t_url = config_get_global( 'path' ) . config_get_global( 'default_home_page' );
				echo "\t<meta http-equiv=\"Refresh\" content=\"4;URL=" . $t_url . "\" />\n";
	
				die();
			}
		}
	}
	
	/**
	 * Get arbitrary data from the session.
	 * @param string $p_name    Session variable name.
	 * @param mixed  $p_default Default value.
	 * @return mixed Session variable
	 */
	static function get( $p_name, $p_default = null ) {
		global $g_session;
	
		$t_args = func_get_args();
		return call_user_func_array( array( $g_session, 'get' ), $t_args );
	}
	
	/**
	 * Get an integer from the session.
	 * @param string       $p_name    Session variable name.
	 * @param integer|null $p_default Default value.
	 * @return integer Session variable
	 */
	static function get_int( $p_name, $p_default = null ) {
		$t_args = func_get_args();
		return (int)call_user_func_array( 'session_get', $t_args );
	}
	
	/**
	 * Get a boolean from the session.
	 * @param string       $p_name    Session variable name.
	 * @param boolean|null $p_default Default value.
	 * @return boolean Session variable
	 */
	static function get_bool( $p_name, $p_default = null ) {
		$t_args = func_get_args();
		return true && call_user_func_array( 'session_get', $t_args );
	}
	
	/**
	 * Get a string from the session.
	 * @param string      $p_name    Session variable name.
	 * @param string|null $p_default Default value.
	 * @return string Session variable
	 */
	static function get_string( $p_name, $p_default = null ) {
		$t_args = func_get_args();
		return '' . call_user_func_array( 'session_get', $t_args );
	}
	
	/**
	 * Set a session variable.
	 * @param string $p_name  Session variable name.
	 * @param mixed  $p_value Variable value.
	 * @return void
	 */
	static function set( $p_name, $p_value ) {
		global $g_session;
		$g_session->set( $p_name, $p_value );
	}
	
	/**
	 * Delete a session variable.
	 * @param string $p_name Session variable name.
	 * @return void
	 */
	static function delete( $p_name ) {
		global $g_session;
		$g_session->delete( $p_name );
	}
	
	/**
	 * Destroy the session entirely.
	 * @return void
	 */
	static function clean() {
		global $g_session;
		$g_session->destroy();
	}

}