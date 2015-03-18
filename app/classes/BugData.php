<?php
namespace Core;


/**
 * Bug Data Structure Definition
 */
class BugData {
	/**
	 * Bug ID
	 */
	protected $id;

	/**
	 * Project ID
	 */
	protected $project_id = null;

	/**
	 * Reporter ID
	 */
	protected $reporter_id = 0;

	/**
	 * Bug Handler ID
	 */
	protected $handler_id = 0;

	/**
	 * Duplicate ID
	 */
	protected $duplicate_id = 0;

	/**
	 * Priority
	 */
	protected $priority = NORMAL;

	/**
	 * Severity
	 */
	protected $severity = MINOR;

	/**
	 * Reproducibility
	 */
	protected $reproducibility = 10;

	/**
	 * Status
	 */
	protected $status = NEW_;

	/**
	 * Resolution
	 */
	protected $resolution = OPEN;

	/**
	 * Projection
	 */
	protected $projection = 10;

	/**
	 * Category ID
	 */
	protected $category_id = 1;

	/**
	 * Date Submitted
	 */
	protected $date_submitted = '';

	/**
	 * Last Updated
	 */
	protected $last_updated = '';

	/**
	 * ETA
	 */
	protected $eta = 10;

	/**
	 * OS
	 */
	protected $os = '';

	/**
	 * OS Build
	 */
	protected $os_build = '';

	/**
	 * Platform
	 */
	protected $platform = '';

	/**
	 * Version
	 */
	protected $version = '';

	/**
	 * Fixed in version
	 */
	protected $fixed_in_version = '';

	/**
	 * Target Version
	 */
	protected $target_version = '';

	/**
	 * Build
	 */
	protected $build = '';

	/**
	 * View State
	 */
	protected $view_state = VS_PUBLIC;

	/**
	 * Summary
	 */
	protected $summary = '';

	/**
	 * Sponsorship Total
	 */
	protected $sponsorship_total = 0;

	/**
	 * Sticky
	 */
	protected $sticky = 0;

	/**
	 * Due Date
	 */
	protected $due_date = '';

	/**
	 * Profile ID
	 */
	protected $profile_id = 0;

	/**
	 * Description
	 */
	protected $description = '';

	/**
	 * Steps to reproduce
	 */
	protected $steps_to_reproduce = '';

	/**
	 * Additional Information
	 */
	protected $additional_information = '';

	/**
	 * Stats
	 */
	private $_stats = null;

	/**
	 * Attachment Count
	 */
	public $attachment_count = null;

	/**
	 * Bugnotes count
	 */
	public $bugnotes_count = null;

	/**
	 * Indicates if bug is currently being loaded from database
	 */
	private $loading = false;

	/**
	 * return number of file attachment's linked to current bug
	 * @return integer
	 */
	public function get_attachment_count() {
		if( $this->attachment_count === null ) {
			$this->attachment_count = \Core\File::bug_attachment_count( $this->id );
			return $this->attachment_count;
		} else {
			return $this->attachment_count;
		}
	}

	/**
	 * return number of bugnotes's linked to current bug
	 * @return integer
	 */
	public function get_bugnotes_count() {
		if( $this->bugnotes_count === null ) {
			$this->bugnotes_count = self::bug_get_bugnote_count();
			return $this->bugnotes_count;
		} else {
			return $this->bugnotes_count;
		}
	}

	/**
	 * Overloaded Function handling property sets
	 *
	 * @param string $p_name  Property name.
	 * @param string $p_value Value to set.
	 * @private
	 * @return void
	 */
	public function __set( $p_name, $p_value ) {
		switch( $p_name ) {
			# integer types
			case 'id':
			case 'project_id':
			case 'reporter_id':
			case 'handler_id':
			case 'duplicate_id':
			case 'priority':
			case 'severity':
			case 'reproducibility':
			case 'status':
			case 'resolution':
			case 'projection':
			case 'category_id':
				$p_value = (int)$p_value;
				break;
			case 'target_version':
				if( !$this->loading && $this->$p_name != $p_value ) {
					# Only set target_version if user has access to do so
					if( !\Core\Access::has_project_level( \Core\Config::mantis_get( 'roadmap_update_threshold' ) ) ) {
						trigger_error( ERROR_ACCESS_DENIED, ERROR );
					}
				}
				break;
			case 'due_date':
				if( !is_numeric( $p_value ) ) {
					$p_value = strtotime( $p_value );
				}
				break;
			case 'summary':
			case 'build':
				if ( !$this->loading ) {
					$p_value = trim( $p_value );
				}
				break;
		}
		$this->$p_name = $p_value;
	}

	/**
	 * Overloaded Function handling property get
	 *
	 * @param string $p_name Property name.
	 * @private
	 * @return string|integer|boolean
	 */
	public function __get( $p_name ) {
		if( $this->is_extended_field( $p_name ) ) {
			$this->fetch_extended_info();
		}
		return $this->{$p_name};
	}

	/**
	 * Overloaded Function handling property isset
	 *
	 * @param string $p_name Property name.
	 * @private
	 * @return boolean
	 */
	public function __isset( $p_name ) {
		return isset( $this->{$p_name} );
	}

	/**
	 * fast-load database row into bugobject
	 * @param array $p_row Database result to load into a bug object.
	 * @return void
	 */
	public function loadrow( array $p_row ) {
		$this->loading = true;

		foreach( $p_row as $t_var => $t_val ) {
			$this->__set( $t_var, $p_row[$t_var] );
		}
		$this->loading = false;
	}

	/**
	 * Retrieves extended information for bug (e.g. bug description)
	 * @return void
	 */
	private function fetch_extended_info() {
		if( $this->description == '' ) {
			$t_text = \Core\Bug::text_cache_row( $this->id );

			$this->description = $t_text['description'];
			$this->steps_to_reproduce = $t_text['steps_to_reproduce'];
			$this->additional_information = $t_text['additional_information'];
		}
	}

	/**
	 * Returns if the field is an extended field which needs fetch_extended_info()
	 *
	 * @param string $p_field_name Field Name.
	 * @return boolean
	 */
	private function is_extended_field( $p_field_name ) {
		switch( $p_field_name ) {
			case 'description':
			case 'steps_to_reproduce':
			case 'additional_information':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns the number of bugnotes for the given bug_id
	 * @return integer number of bugnotes
	 * @access private
	 * @uses database_api.php
	 */
	private function bug_get_bugnote_count() {
		if( !\Core\Access::has_project_level( \Core\Config::mantis_get( 'private_bugnote_threshold' ), $this->project_id ) ) {
			$t_restriction = 'AND view_state=' . VS_PUBLIC;
		} else {
			$t_restriction = '';
		}

		$t_query = 'SELECT COUNT(*) FROM {bugnote}
					  WHERE bug_id =' . \Core\Database::param() . ' ' . $t_restriction;
		$t_result = \Core\Database::query( $t_query, array( $this->id ) );

		return \Core\Database::result( $t_result );
	}

	/**
	 * validate current bug object for database insert/update
	 * triggers error on failure
	 * @param boolean $p_update_extended Whether to validate extended fields.
	 * @return void
	 */
	function validate( $p_update_extended = true ) {
		# Summary cannot be blank
		if( \Core\Utility::is_blank( $this->summary ) ) {
			\Core\Error::parameters( \Core\Lang::get( 'summary' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		if( $p_update_extended ) {
			# Description field cannot be empty
			if( \Core\Utility::is_blank( $this->description ) ) {
				\Core\Error::parameters( \Core\Lang::get( 'description' ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
		}

		# Make sure a category is set
		if( 0 == $this->category_id && !\Core\Config::mantis_get( 'allow_no_category' ) ) {
			\Core\Error::parameters( \Core\Lang::get( 'category' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		# Ensure that category id is a valid category
		if( $this->category_id > 0 ) {
			\Core\Category::ensure_exists( $this->category_id );
		}

		if( !\Core\Utility::is_blank( $this->duplicate_id ) && ( $this->duplicate_id != 0 ) && ( $this->id == $this->duplicate_id ) ) {
			trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
			# never returns
		}
	}

	/**
	 * Insert a new bug into the database
	 * @return integer integer representing the bug identifier that was created
	 * @access public
	 * @uses database_api.php
	 * @uses lang_api.php
	 */
	function create() {
		self::validate( true );

		# check due_date format
		if( \Core\Utility::is_blank( $this->due_date ) ) {
			$this->due_date = \Core\Date::get_null();
		}
		# check date submitted and last modified
		if( \Core\Utility::is_blank( $this->date_submitted ) ) {
			$this->date_submitted = \Core\Database::now();
		}
		if( \Core\Utility::is_blank( $this->last_updated ) ) {
			$this->last_updated = \Core\Database::now();
		}

		# Insert text information
		$t_query = 'INSERT INTO {bug_text}
					    ( description, steps_to_reproduce, additional_information )
					  VALUES
					    ( ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ')';
		\Core\Database::query( $t_query, array( $this->description, $this->steps_to_reproduce, $this->additional_information ) );

		# Get the id of the text information we just inserted
		# NOTE: this is guaranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_text_id = \Core\Database::insert_id( \Core\Database::get_table( 'bug_text' ) );

		# check to see if we want to assign this right off
		$t_starting_status  = \Core\Config::mantis_get( 'bug_submit_status' );
		$t_original_status = $this->status;

		# if not assigned, check if it should auto-assigned.
		if( 0 == $this->handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$t_query = 'SELECT user_id FROM {category} WHERE id=' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( $this->category_id ) );
			$t_handler = \Core\Database::result( $t_result );

			if( $t_handler !== false ) {
				$this->handler_id = $t_handler;
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if( ( $this->handler_id != 0 ) && ( $this->status == $t_starting_status ) && ( ON == \Core\Config::mantis_get( 'auto_set_status_to_assigned' ) ) ) {
			$t_status = \Core\Config::mantis_get( 'bug_assigned_status' );
		} else {
			$t_status = $this->status;
		}

		# Insert the rest of the data
		$t_query = 'INSERT INTO {bug}
					    ( project_id,reporter_id, handler_id,duplicate_id,
					      priority,severity, reproducibility,status,
					      resolution,projection, category_id,date_submitted,
					      last_updated,eta, bug_text_id,
					      os, os_build,platform, version,build,
					      profile_id, summary, view_state, sponsorship_total, sticky, fixed_in_version,
					      target_version, due_date
					    )
					  VALUES
					    ( ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ')';

		\Core\Database::query( $t_query, array( $this->project_id, $this->reporter_id, $this->handler_id, $this->duplicate_id, $this->priority, $this->severity, $this->reproducibility, $t_status, $this->resolution, $this->projection, $this->category_id, $this->date_submitted, $this->last_updated, $this->eta, $t_text_id, $this->os, $this->os_build, $this->platform, $this->version, $this->build, $this->profile_id, $this->summary, $this->view_state, $this->sponsorship_total, $this->sticky, $this->fixed_in_version, $this->target_version, $this->due_date ) );

		$this->id = \Core\Database::insert_id( \Core\Database::get_table( 'bug' ) );

		# log new bug
		\Core\History::log_event_special( $this->id, NEW_BUG );

		# log changes, if any (compare happens in history_log_event_direct)
		\Core\History::log_event_direct( $this->id, 'status', $t_original_status, $t_status );
		\Core\History::log_event_direct( $this->id, 'handler_id', 0, $this->handler_id );

		return $this->id;
	}

	/**
     * Update a bug from the given data structure
     *  If the third parameter is true, also update the longer strings table
     * @param boolean $p_update_extended Whether to update extended fields.
     * @param boolean $p_bypass_mail     Whether to bypass sending email notifications.
     * @internal param boolean $p_bypass_email Default false, set to true to avoid generating emails (if sending elsewhere)
     * @return boolean (always true)
     * @access public
	 */
	function update( $p_update_extended = false, $p_bypass_mail = false ) {
		self::validate( $p_update_extended );

		$c_bug_id = $this->id;

		if( \Core\Utility::is_blank( $this->due_date ) ) {
			$this->due_date = \Core\Date::get_null();
		}

		$t_old_data = \Core\Bug::get( $this->id, true );

		# Update all fields
		# Ignore date_submitted and last_updated since they are pulled out
		#  as unix timestamps which could confuse the history log and they
		#  shouldn't get updated like this anyway.  If you really need to change
		#  them use \Core\Bug::set_field()
		$t_query = 'UPDATE {bug}
					SET project_id=' . \Core\Database::param() . ', reporter_id=' . \Core\Database::param() . ',
						handler_id=' . \Core\Database::param() . ', duplicate_id=' . \Core\Database::param() . ',
						priority=' . \Core\Database::param() . ', severity=' . \Core\Database::param() . ',
						reproducibility=' . \Core\Database::param() . ', status=' . \Core\Database::param() . ',
						resolution=' . \Core\Database::param() . ', projection=' . \Core\Database::param() . ',
						category_id=' . \Core\Database::param() . ', eta=' . \Core\Database::param() . ',
						os=' . \Core\Database::param() . ', os_build=' . \Core\Database::param() . ',
						platform=' . \Core\Database::param() . ', version=' . \Core\Database::param() . ',
						build=' . \Core\Database::param() . ', fixed_in_version=' . \Core\Database::param() . ',';

		$t_fields = array(
			$this->project_id, $this->reporter_id,
			$this->handler_id, $this->duplicate_id,
			$this->priority, $this->severity,
			$this->reproducibility, $this->status,
			$this->resolution, $this->projection,
			$this->category_id, $this->eta,
			$this->os, $this->os_build,
			$this->platform, $this->version,
			$this->build, $this->fixed_in_version,
		);
		$t_roadmap_updated = false;
		if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'roadmap_update_threshold' ) ) ) {
			$t_query .= '
						target_version=' . \Core\Database::param() . ',';
			$t_fields[] = $this->target_version;
			$t_roadmap_updated = true;
		}

		$t_query .= '
						view_state=' . \Core\Database::param() . ',
						summary=' . \Core\Database::param() . ',
						sponsorship_total=' . \Core\Database::param() . ',
						sticky=' . \Core\Database::param() . ',
						due_date=' . \Core\Database::param() . '
					WHERE id=' . \Core\Database::param();
		$t_fields[] = $this->view_state;
		$t_fields[] = $this->summary;
		$t_fields[] = $this->sponsorship_total;
		$t_fields[] = (bool)$this->sticky;
		$t_fields[] = $this->due_date;
		$t_fields[] = $this->id;

		\Core\Database::query( $t_query, $t_fields );

		\Core\Bug::clear_cache( $this->id );

		# log changes
		\Core\History::log_event_direct( $c_bug_id, 'project_id', $t_old_data->project_id, $this->project_id );
		\Core\History::log_event_direct( $c_bug_id, 'reporter_id', $t_old_data->reporter_id, $this->reporter_id );
		\Core\History::log_event_direct( $c_bug_id, 'handler_id', $t_old_data->handler_id, $this->handler_id );
		\Core\History::log_event_direct( $c_bug_id, 'priority', $t_old_data->priority, $this->priority );
		\Core\History::log_event_direct( $c_bug_id, 'severity', $t_old_data->severity, $this->severity );
		\Core\History::log_event_direct( $c_bug_id, 'reproducibility', $t_old_data->reproducibility, $this->reproducibility );
		\Core\History::log_event_direct( $c_bug_id, 'status', $t_old_data->status, $this->status );
		\Core\History::log_event_direct( $c_bug_id, 'resolution', $t_old_data->resolution, $this->resolution );
		\Core\History::log_event_direct( $c_bug_id, 'projection', $t_old_data->projection, $this->projection );
		\Core\History::log_event_direct( $c_bug_id, 'category', \Core\Category::full_name( $t_old_data->category_id, false ), \Core\Category::full_name( $this->category_id, false ) );
		\Core\History::log_event_direct( $c_bug_id, 'eta', $t_old_data->eta, $this->eta );
		\Core\History::log_event_direct( $c_bug_id, 'os', $t_old_data->os, $this->os );
		\Core\History::log_event_direct( $c_bug_id, 'os_build', $t_old_data->os_build, $this->os_build );
		\Core\History::log_event_direct( $c_bug_id, 'platform', $t_old_data->platform, $this->platform );
		\Core\History::log_event_direct( $c_bug_id, 'version', $t_old_data->version, $this->version );
		\Core\History::log_event_direct( $c_bug_id, 'build', $t_old_data->build, $this->build );
		\Core\History::log_event_direct( $c_bug_id, 'fixed_in_version', $t_old_data->fixed_in_version, $this->fixed_in_version );
		if( $t_roadmap_updated ) {
			\Core\History::log_event_direct( $c_bug_id, 'target_version', $t_old_data->target_version, $this->target_version );
		}
		\Core\History::log_event_direct( $c_bug_id, 'view_state', $t_old_data->view_state, $this->view_state );
		\Core\History::log_event_direct( $c_bug_id, 'summary', $t_old_data->summary, $this->summary );
		\Core\History::log_event_direct( $c_bug_id, 'sponsorship_total', $t_old_data->sponsorship_total, $this->sponsorship_total );
		\Core\History::log_event_direct( $c_bug_id, 'sticky', $t_old_data->sticky, $this->sticky );

		\Core\History::log_event_direct( $c_bug_id, 'due_date', ( $t_old_data->due_date != \Core\Date::get_null() ) ? $t_old_data->due_date : null, ( $this->due_date != \Core\Date::get_null() ) ? $this->due_date : null );

		# Update extended info if requested
		if( $p_update_extended ) {
			$t_bug_text_id = \Core\Bug::get_field( $c_bug_id, 'bug_text_id' );

			$t_query = 'UPDATE {bug_text}
							SET description=' . \Core\Database::param() . ',
								steps_to_reproduce=' . \Core\Database::param() . ',
								additional_information=' . \Core\Database::param() . '
							WHERE id=' . \Core\Database::param();
			\Core\Database::query( $t_query, array( $this->description, $this->steps_to_reproduce, $this->additional_information, $t_bug_text_id ) );

			\Core\Bug::text_clear_cache( $c_bug_id );

			$t_current_user = \Core\Auth::get_current_user_id();

			if( $t_old_data->description != $this->description ) {
				if( \Core\Bug\Revision::count( $c_bug_id, REV_DESCRIPTION ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_DESCRIPTION, $t_old_data->description, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_DESCRIPTION, $this->description );
				\Core\History::log_event_special( $c_bug_id, DESCRIPTION_UPDATED, $t_revision_id );
			}

			if( $t_old_data->steps_to_reproduce != $this->steps_to_reproduce ) {
				if( \Core\Bug\Revision::count( $c_bug_id, REV_STEPS_TO_REPRODUCE ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_STEPS_TO_REPRODUCE, $t_old_data->steps_to_reproduce, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_STEPS_TO_REPRODUCE, $this->steps_to_reproduce );
				\Core\History::log_event_special( $c_bug_id, STEP_TO_REPRODUCE_UPDATED, $t_revision_id );
			}

			if( $t_old_data->additional_information != $this->additional_information ) {
				if( \Core\Bug\Revision::count( $c_bug_id, REV_ADDITIONAL_INFO ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_ADDITIONAL_INFO, $t_old_data->additional_information, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_ADDITIONAL_INFO, $this->additional_information );
				\Core\History::log_event_special( $c_bug_id, ADDITIONAL_INFO_UPDATED, $t_revision_id );
			}
		}

		# Update the last update date
		\Core\Bug::update_date( $c_bug_id );

		# allow bypass if user is sending mail separately
		if( false == $p_bypass_mail ) {
			# bug assigned
			if( $t_old_data->handler_id != $this->handler_id ) {
				\Core\Email::generic( $c_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
				return true;
			}

			# status changed
			if( $t_old_data->status != $this->status ) {
				$t_status = \Core\Enum::getLabel( \Core\Config::mantis_get( 'status_enum_string' ), $this->status );
				$t_status = str_replace( ' ', '_', $t_status );
				\Core\Email::generic( $c_bug_id, $t_status, 'email_notification_title_for_status_bug_' . $t_status );
				return true;
			}

			# @todo handle priority change if it requires special handling
			# generic update notification
			\Core\Email::generic( $c_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
		}

		return true;
	}
}