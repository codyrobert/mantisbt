<?php
namespace Flickerbox;


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
			$this->attachment_count = \Flickerbox\File::bug_attachment_count( $this->id );
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
					if( !\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'roadmap_update_threshold' ) ) ) {
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
			$t_text = \Flickerbox\Bug::text_cache_row( $this->id );

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
		if( !\Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'private_bugnote_threshold' ), $this->project_id ) ) {
			$t_restriction = 'AND view_state=' . VS_PUBLIC;
		} else {
			$t_restriction = '';
		}

		$t_query = 'SELECT COUNT(*) FROM {bugnote}
					  WHERE bug_id =' . \Flickerbox\Database::param() . ' ' . $t_restriction;
		$t_result = \Flickerbox\Database::query( $t_query, array( $this->id ) );

		return \Flickerbox\Database::result( $t_result );
	}

	/**
	 * validate current bug object for database insert/update
	 * triggers error on failure
	 * @param boolean $p_update_extended Whether to validate extended fields.
	 * @return void
	 */
	function validate( $p_update_extended = true ) {
		# Summary cannot be blank
		if( \Flickerbox\Utility::is_blank( $this->summary ) ) {
			\Flickerbox\Error::parameters( \Flickerbox\Lang::get( 'summary' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		if( $p_update_extended ) {
			# Description field cannot be empty
			if( \Flickerbox\Utility::is_blank( $this->description ) ) {
				\Flickerbox\Error::parameters( \Flickerbox\Lang::get( 'description' ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
		}

		# Make sure a category is set
		if( 0 == $this->category_id && !\Flickerbox\Config::mantis_get( 'allow_no_category' ) ) {
			\Flickerbox\Error::parameters( \Flickerbox\Lang::get( 'category' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		# Ensure that category id is a valid category
		if( $this->category_id > 0 ) {
			\Flickerbox\Category::ensure_exists( $this->category_id );
		}

		if( !\Flickerbox\Utility::is_blank( $this->duplicate_id ) && ( $this->duplicate_id != 0 ) && ( $this->id == $this->duplicate_id ) ) {
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
		if( \Flickerbox\Utility::is_blank( $this->due_date ) ) {
			$this->due_date = \Flickerbox\Date::get_null();
		}
		# check date submitted and last modified
		if( \Flickerbox\Utility::is_blank( $this->date_submitted ) ) {
			$this->date_submitted = \Flickerbox\Database::now();
		}
		if( \Flickerbox\Utility::is_blank( $this->last_updated ) ) {
			$this->last_updated = \Flickerbox\Database::now();
		}

		# Insert text information
		$t_query = 'INSERT INTO {bug_text}
					    ( description, steps_to_reproduce, additional_information )
					  VALUES
					    ( ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ')';
		\Flickerbox\Database::query( $t_query, array( $this->description, $this->steps_to_reproduce, $this->additional_information ) );

		# Get the id of the text information we just inserted
		# NOTE: this is guaranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_text_id = \Flickerbox\Database::insert_id( \Flickerbox\Database::get_table( 'bug_text' ) );

		# check to see if we want to assign this right off
		$t_starting_status  = \Flickerbox\Config::mantis_get( 'bug_submit_status' );
		$t_original_status = $this->status;

		# if not assigned, check if it should auto-assigned.
		if( 0 == $this->handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$t_query = 'SELECT user_id FROM {category} WHERE id=' . \Flickerbox\Database::param();
			$t_result = \Flickerbox\Database::query( $t_query, array( $this->category_id ) );
			$t_handler = \Flickerbox\Database::result( $t_result );

			if( $t_handler !== false ) {
				$this->handler_id = $t_handler;
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if( ( $this->handler_id != 0 ) && ( $this->status == $t_starting_status ) && ( ON == \Flickerbox\Config::mantis_get( 'auto_set_status_to_assigned' ) ) ) {
			$t_status = \Flickerbox\Config::mantis_get( 'bug_assigned_status' );
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
					    ( ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
					      ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
					      ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
					      ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
					      ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
					      ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',
					      ' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ',' . \Flickerbox\Database::param() . ')';

		\Flickerbox\Database::query( $t_query, array( $this->project_id, $this->reporter_id, $this->handler_id, $this->duplicate_id, $this->priority, $this->severity, $this->reproducibility, $t_status, $this->resolution, $this->projection, $this->category_id, $this->date_submitted, $this->last_updated, $this->eta, $t_text_id, $this->os, $this->os_build, $this->platform, $this->version, $this->build, $this->profile_id, $this->summary, $this->view_state, $this->sponsorship_total, $this->sticky, $this->fixed_in_version, $this->target_version, $this->due_date ) );

		$this->id = \Flickerbox\Database::insert_id( \Flickerbox\Database::get_table( 'bug' ) );

		# log new bug
		\Flickerbox\History::log_event_special( $this->id, NEW_BUG );

		# log changes, if any (compare happens in history_log_event_direct)
		\Flickerbox\History::log_event_direct( $this->id, 'status', $t_original_status, $t_status );
		\Flickerbox\History::log_event_direct( $this->id, 'handler_id', 0, $this->handler_id );

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

		if( \Flickerbox\Utility::is_blank( $this->due_date ) ) {
			$this->due_date = \Flickerbox\Date::get_null();
		}

		$t_old_data = \Flickerbox\Bug::get( $this->id, true );

		# Update all fields
		# Ignore date_submitted and last_updated since they are pulled out
		#  as unix timestamps which could confuse the history log and they
		#  shouldn't get updated like this anyway.  If you really need to change
		#  them use \Flickerbox\Bug::set_field()
		$t_query = 'UPDATE {bug}
					SET project_id=' . \Flickerbox\Database::param() . ', reporter_id=' . \Flickerbox\Database::param() . ',
						handler_id=' . \Flickerbox\Database::param() . ', duplicate_id=' . \Flickerbox\Database::param() . ',
						priority=' . \Flickerbox\Database::param() . ', severity=' . \Flickerbox\Database::param() . ',
						reproducibility=' . \Flickerbox\Database::param() . ', status=' . \Flickerbox\Database::param() . ',
						resolution=' . \Flickerbox\Database::param() . ', projection=' . \Flickerbox\Database::param() . ',
						category_id=' . \Flickerbox\Database::param() . ', eta=' . \Flickerbox\Database::param() . ',
						os=' . \Flickerbox\Database::param() . ', os_build=' . \Flickerbox\Database::param() . ',
						platform=' . \Flickerbox\Database::param() . ', version=' . \Flickerbox\Database::param() . ',
						build=' . \Flickerbox\Database::param() . ', fixed_in_version=' . \Flickerbox\Database::param() . ',';

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
		if( \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'roadmap_update_threshold' ) ) ) {
			$t_query .= '
						target_version=' . \Flickerbox\Database::param() . ',';
			$t_fields[] = $this->target_version;
			$t_roadmap_updated = true;
		}

		$t_query .= '
						view_state=' . \Flickerbox\Database::param() . ',
						summary=' . \Flickerbox\Database::param() . ',
						sponsorship_total=' . \Flickerbox\Database::param() . ',
						sticky=' . \Flickerbox\Database::param() . ',
						due_date=' . \Flickerbox\Database::param() . '
					WHERE id=' . \Flickerbox\Database::param();
		$t_fields[] = $this->view_state;
		$t_fields[] = $this->summary;
		$t_fields[] = $this->sponsorship_total;
		$t_fields[] = (bool)$this->sticky;
		$t_fields[] = $this->due_date;
		$t_fields[] = $this->id;

		\Flickerbox\Database::query( $t_query, $t_fields );

		\Flickerbox\Bug::clear_cache( $this->id );

		# log changes
		\Flickerbox\History::log_event_direct( $c_bug_id, 'project_id', $t_old_data->project_id, $this->project_id );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'reporter_id', $t_old_data->reporter_id, $this->reporter_id );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'handler_id', $t_old_data->handler_id, $this->handler_id );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'priority', $t_old_data->priority, $this->priority );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'severity', $t_old_data->severity, $this->severity );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'reproducibility', $t_old_data->reproducibility, $this->reproducibility );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'status', $t_old_data->status, $this->status );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'resolution', $t_old_data->resolution, $this->resolution );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'projection', $t_old_data->projection, $this->projection );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'category', \Flickerbox\Category::full_name( $t_old_data->category_id, false ), \Flickerbox\Category::full_name( $this->category_id, false ) );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'eta', $t_old_data->eta, $this->eta );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'os', $t_old_data->os, $this->os );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'os_build', $t_old_data->os_build, $this->os_build );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'platform', $t_old_data->platform, $this->platform );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'version', $t_old_data->version, $this->version );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'build', $t_old_data->build, $this->build );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'fixed_in_version', $t_old_data->fixed_in_version, $this->fixed_in_version );
		if( $t_roadmap_updated ) {
			\Flickerbox\History::log_event_direct( $c_bug_id, 'target_version', $t_old_data->target_version, $this->target_version );
		}
		\Flickerbox\History::log_event_direct( $c_bug_id, 'view_state', $t_old_data->view_state, $this->view_state );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'summary', $t_old_data->summary, $this->summary );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'sponsorship_total', $t_old_data->sponsorship_total, $this->sponsorship_total );
		\Flickerbox\History::log_event_direct( $c_bug_id, 'sticky', $t_old_data->sticky, $this->sticky );

		\Flickerbox\History::log_event_direct( $c_bug_id, 'due_date', ( $t_old_data->due_date != \Flickerbox\Date::get_null() ) ? $t_old_data->due_date : null, ( $this->due_date != \Flickerbox\Date::get_null() ) ? $this->due_date : null );

		# Update extended info if requested
		if( $p_update_extended ) {
			$t_bug_text_id = \Flickerbox\Bug::get_field( $c_bug_id, 'bug_text_id' );

			$t_query = 'UPDATE {bug_text}
							SET description=' . \Flickerbox\Database::param() . ',
								steps_to_reproduce=' . \Flickerbox\Database::param() . ',
								additional_information=' . \Flickerbox\Database::param() . '
							WHERE id=' . \Flickerbox\Database::param();
			\Flickerbox\Database::query( $t_query, array( $this->description, $this->steps_to_reproduce, $this->additional_information, $t_bug_text_id ) );

			\Flickerbox\Bug::text_clear_cache( $c_bug_id );

			$t_current_user = \Flickerbox\Auth::get_current_user_id();

			if( $t_old_data->description != $this->description ) {
				if( \Flickerbox\Bug\Revision::count( $c_bug_id, REV_DESCRIPTION ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_DESCRIPTION, $t_old_data->description, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_DESCRIPTION, $this->description );
				\Flickerbox\History::log_event_special( $c_bug_id, DESCRIPTION_UPDATED, $t_revision_id );
			}

			if( $t_old_data->steps_to_reproduce != $this->steps_to_reproduce ) {
				if( \Flickerbox\Bug\Revision::count( $c_bug_id, REV_STEPS_TO_REPRODUCE ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_STEPS_TO_REPRODUCE, $t_old_data->steps_to_reproduce, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_STEPS_TO_REPRODUCE, $this->steps_to_reproduce );
				\Flickerbox\History::log_event_special( $c_bug_id, STEP_TO_REPRODUCE_UPDATED, $t_revision_id );
			}

			if( $t_old_data->additional_information != $this->additional_information ) {
				if( \Flickerbox\Bug\Revision::count( $c_bug_id, REV_ADDITIONAL_INFO ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_ADDITIONAL_INFO, $t_old_data->additional_information, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_ADDITIONAL_INFO, $this->additional_information );
				\Flickerbox\History::log_event_special( $c_bug_id, ADDITIONAL_INFO_UPDATED, $t_revision_id );
			}
		}

		# Update the last update date
		\Flickerbox\Bug::update_date( $c_bug_id );

		# allow bypass if user is sending mail separately
		if( false == $p_bypass_mail ) {
			# bug assigned
			if( $t_old_data->handler_id != $this->handler_id ) {
				\Flickerbox\Email::generic( $c_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
				return true;
			}

			# status changed
			if( $t_old_data->status != $this->status ) {
				$t_status = \Flickerbox\MantisEnum::getLabel( \Flickerbox\Config::mantis_get( 'status_enum_string' ), $this->status );
				$t_status = str_replace( ' ', '_', $t_status );
				\Flickerbox\Email::generic( $c_bug_id, $t_status, 'email_notification_title_for_status_bug_' . $t_status );
				return true;
			}

			# @todo handle priority change if it requires special handling
			# generic update notification
			\Flickerbox\Email::generic( $c_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
		}

		return true;
	}
}