<?php
namespace Core;


/**
 * Abstract interface for a MantisBT session handler.
 */
abstract class MantisSession {
	/**
	 * Session ID
	 */
	protected $id;

	/**
	 * Constructor
	 */
	abstract function __construct();

	/**
	 * get session data
	 * @param string $p_name    The name of the value to set.
	 * @param mixed  $p_default The value to set.
	 * @return string
	 */
	abstract function get( $p_name, $p_default = null );

	/**
	 * set session data
	 * @param string $p_name  The name of the value to set.
	 * @param mixed  $p_value The value to set.
	 * @return void
	 */
	abstract function set( $p_name, $p_value );

	/**
	 * delete session data
	 * @param string $p_name The name of the value to set.
	 * @return void
	 */
	abstract function delete( $p_name );

	/**
	 * destroy session
	 * @return void
	 */
	abstract function destroy();
}