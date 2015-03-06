<?php
/**
 * Version Data Structure Definition
 */
class VersionData {
	/**
	 * Version id
	 */
	protected $id = 0;

	/**
	 * Project ID
	 */
	protected $project_id = 0;

	/**
	 * Version name
	 */
	protected $version = '';

	/**
	 * Version Description
	 */
	protected $description = '';

	/**
	 * Version Release Status e.g. VERSION_FUTURE
	 */
	protected $released = VERSION_FUTURE;

	/**
	 * Date Order
	 */
	protected $date_order = 1;

	/**
	 * Obsolete
	 */
	protected $obsolete = 0;

	/**
	 * Overloaded function
	 * @param string         $p_name  A valid property name.
	 * @param integer|string $p_value The property value to set.
	 * @return void
	 * @private
	 */
	public function __set( $p_name, $p_value ) {
		switch( $p_name ) {
			case 'date_order':
				if( !is_numeric( $p_value ) ) {
					if( $p_value == '' ) {
						$p_value = \Flickerbox\Date::get_null();
					} else {
						$p_value = strtotime( $p_value );
						if( $p_value === false ) {
							trigger_error( ERROR_INVALID_DATE_FORMAT, ERROR );
						}
					}
				}
		}
		$this->$p_name = $p_value;
	}

	/**
	 * Overloaded function
	 * @param string $p_name A valid property name.
	 * @return integer|string
	 * @private
	 */
	public function __get( $p_name ) {
		return $this->{$p_name};
	}
}