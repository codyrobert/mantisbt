<?php
namespace Core;


/**
 * Bugnote Data Structure Definition
 */
class BugnoteData {
	/**
	 * Bugnote ID
	 */
	public $id;

	/**
	 * Bug ID
	 */
	public $bug_id;

	/**
	 * Reporter ID
	 */
	public $reporter_id;

	/**
	 * Note text
	 */
	public $note;

	/**
	 * View State
	 */
	public $view_state;

	/**
	 * Date submitted
	 */
	public $date_submitted;

	/**
	 * Last Modified
	 */
	public $last_modified;

	/**
	 * Bugnote type
	 */
	public $note_type;

	/**
	 * ???
	 */
	public $note_attr;

	/**
	 * Time tracking information
	 */
	public $time_tracking;

	/**
	 * Bugnote Text id
	 */
	public $bugnote_text_id;
}