<?php
namespace Flickerbox;

/**
 * Mantis Database Parameters Count class
 * Stores the current parameter count, provides method to generate parameters
 * and a simple stack mechanism to enable the caller to build multiple queries
 * concurrently on RDBMS using positional parameters (e.g. PostgreSQL)
 */
class MantisDbParam {
	/**
	 * Current parameter count
	 */
	public $count = 0;

	/**
	 * Parameter count stack
	 */
	private $stack = array();

	/**
	 * Generate a string to insert a parameter into a database query string
	 * @return string 'wildcard' matching a parameter in correct ordered format for the current database.
	 */
	public function assign() {
		global $g_db;
		return $g_db->Param( $this->count++ );
	}

	/**
	 * Pushes current parameter count onto stack and resets its value to 0
	 * @return void
	 */
	public function push() {
		$this->stack[] = $this->count;
		$this->count = 0;
	}

	/**
	 * Pops the previous value of param count from the stack
	 * This function is called by {@see \Flickerbox\Database::query()} and should not need
	 * to be executed directly
	 * @return void
	 */
	public function pop() {
		global $g_db;

		$this->count = (int)array_pop( $this->stack );
		if( \Flickerbox\Database::is_pgsql() ) {
			# Manually reset the ADOdb param number to the value we just popped
			$g_db->_pnum = $this->count;
		}
	}
}