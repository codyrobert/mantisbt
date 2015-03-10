<?php
namespace Core;

/**
 * Preference Structure Definition
 */
class UserPreferences {
	/**
	 * Default Profile
	 */
	protected $default_profile = null;

	/**
	 * Default Project for user
	 */
	protected $default_project = null;

	/**
	 * Automatic Refresh delay
	 */
	protected $refresh_delay = null;

	/**
	 * Automatic Redirect delay
	 */
	protected $redirect_delay = null;

	/**
	 * Bugnote order - oldest/newest first
	 */
	protected $bugnote_order = null;

	/**
	 * Receive email on new bugs
	 */
	protected $email_on_new = null;

	/**
	 * Receive email on assigned bugs
	 */
	protected $email_on_assigned = null;

	/**
	 * Receive email on feedback
	 */
	protected $email_on_feedback = null;

	/**
	 * Receive email on resolved bugs
	 */
	protected $email_on_resolved = null;

	/**
	 * Receive email on closed bugs
	 */
	protected $email_on_closed = null;

	/**
	 * Receive email on reopened bugs
	 */
	protected $email_on_reopened = null;

	/**
	 * Receive email on new bugnote
	 */
	protected $email_on_bugnote = null;

	/**
	 * Receive email on bug status change
	 */
	protected $email_on_status = null;

	/**
	 * Receive email on bug priority change
	 */
	protected $email_on_priority = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_new_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_assigned_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_feedback_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_resolved_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_closed_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_reopened_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_bugnote_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_status_min_severity = null;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */
	protected $email_on_priority_min_severity = null;

	/**
	 * Number of bug notes to include in generated emails
	 */
	protected $email_bugnote_limit = null;

	/**
	 * Users language preference
	 */
	protected $language = null;

	/**
	 * User Timezone
	 */
	protected $timezone = null;

	/**
	 * User id
	 */
	private $pref_user_id;

	/**
	 * Project ID
	 */
	private $pref_project_id;

	/**
	 * Default Values - Config Field Mappings
	 */
	private static $_default_mapping = array(
	'default_profile' => array( 'default_profile', 'int' ),
	'default_project' => array( 'default_project', 'int' ),
	'refresh_delay' => array( 'default_refresh_delay', 'int' ),
	'redirect_delay' => array( 'default_redirect_delay', 'int' ),
	'bugnote_order' => array( 'default_bugnote_order', 'string' ),
	'email_on_new' => array( 'default_email_on_new', 'int' ),
	'email_on_assigned' => array(  'default_email_on_assigned', 'int' ),
	'email_on_feedback' => array(  'default_email_on_feedback', 'int' ),
	'email_on_resolved' => array(  'default_email_on_resolved', 'int' ),
	'email_on_closed' => array(  'default_email_on_closed', 'int' ),
	'email_on_reopened' => array(  'default_email_on_reopened', 'int' ),
	'email_on_bugnote' => array(  'default_email_on_bugnote', 'int' ),
	'email_on_status' => array(  'default_email_on_status', 'int' ),
	'email_on_priority' => array(  'default_email_on_priority', 'int' ),
	'email_on_new_min_severity' => array(  'default_email_on_new_minimum_severity', 'int' ),
	'email_on_assigned_min_severity' => array(  'default_email_on_assigned_minimum_severity', 'int' ),
	'email_on_feedback_min_severity' => array(  'default_email_on_feedback_minimum_severity', 'int' ),
	'email_on_resolved_min_severity' => array(  'default_email_on_resolved_minimum_severity', 'int' ),
	'email_on_closed_min_severity' => array(  'default_email_on_closed_minimum_severity', 'int' ),
	'email_on_reopened_min_severity' => array(  'default_email_on_reopened_minimum_severity', 'int' ),
	'email_on_bugnote_min_severity' => array(  'default_email_on_bugnote_minimum_severity', 'int' ),
	'email_on_status_min_severity' => array(  'default_email_on_status_minimum_severity', 'int' ),
	'email_on_priority_min_severity' => array(  'default_email_on_priority_minimum_severity', 'int' ),
	'email_bugnote_limit' => array(  'default_email_bugnote_limit', 'int' ),
	'language' => array(  'default_language', 'string' ),
	'timezone' => array( 'default_timezone', 'string' ),
	);

	/**
	 * Constructor
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 */
	function UserPreferences( $p_user_id, $p_project_id ) {
		$this->default_profile = 0;
		$this->default_project = ALL_PROJECTS;

		$this->pref_user_id = (int)$p_user_id;
		$this->pref_project_id = (int)$p_project_id;
	}

	/**
	 * Overloaded function
	 * @param string $p_name  The Property name to set.
	 * @param string $p_value A value to set the property to.
	 * @return void
	 * @access private
	 */
	public function __set( $p_name, $p_value ) {
		switch( $p_name ) {
			case 'timezone':
				if( $p_value == '' ) {
					$p_value = null;
				}
		}
		$this->$p_name = $p_value;
	}

	/**
	 * Overloaded function
	 * @param string $p_string Property name.
	 * @access private
	 * @return mixed
	 */
	public function __get( $p_string ) {
		if( is_null( $this->$p_string ) ) {
			$this->$p_string = \Core\Config::mantis_get( self::$_default_mapping[$p_string][0], null, $this->pref_user_id, $this->pref_project_id );
		}
		switch( self::$_default_mapping[$p_string][1] ) {
			case 'int':
				return (int)($this->$p_string);
			default:
				return $this->$p_string;
		}
	}

	/**
	 * Public Get() function
	 * @param string $p_string Property to get.
	 * @return mixed
	 */
	function Get( $p_string ) {
		if( is_null( $this->$p_string ) ) {
			$this->$p_string = \Core\Config::mantis_get( self::$_default_mapping[$p_string][0], null, $this->pref_user_id, $this->pref_project_id );
		}
		return $this->$p_string;
	}
}