<?php
namespace Core\Email;


/**
 * EmailData Structure Definition
 */
class Data {
	/**
	 * Email address
	 */
	public $email = '';

	/**
	 * Subject text
	 */
	public $subject = '';

	/**
	 * Body text
	 */
	public $body = '';

	/**
	 * Meta Data array
	 */
	public $metadata = array(
		'headers' => array(),
	);

	/**
	 * Email ID
	 */
	public $email_id = 0;

	/**
	 * Submitted
	 */
	public $submitted = '';
};