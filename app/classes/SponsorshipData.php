<?php
/**
 * Sponsorship Data Structure Definition
 */
class SponsorshipData {
	/**
	 * Sponsorship id
	 */
	public $id = 0;

	/**
	 * Bug ID
	 */
	public $bug_id = 0;

	/**
	 * User ID
	 */
	public $user_id = 0;

	/**
	 * Sponsorship amount
	 */
	public $amount = 0;

	/**
	 * Logo
	 */
	public $logo = '';

	/**
	 * URL
	 */
	public $url = '';

	/**
	 * Sponsorship paid
	 */
	public $paid = 0;

	/**
	 * date submitted timestamp
	 */
	public $date_submitted = '';

	/**
	 * Last updated timestamp
	 */
	public $last_updated = '';
}