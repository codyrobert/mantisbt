<?php
namespace Controller;


class Report extends \Core\Controller\Page
{
	function action_post()
	{
		

		\Core\Form::security_validate( 'bug_report' );
		
		$t_project_id = null;
		
		$f_master_bug_id = \Core\GPC::get_int( 'm_id', 0 );
		if( $f_master_bug_id > 0 ) {
			\Core\Bug::ensure_exists( $f_master_bug_id );
			if( \Core\Bug::is_readonly( $f_master_bug_id ) ) {
				\Core\Error::parameters( $f_master_bug_id );
				trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
			}
			$t_master_bug = \Core\Bug::get( $f_master_bug_id, true );
			$t_project_id = $t_master_bug->project_id;
		} else {
			$f_project_id = \Core\GPC::get_int( 'project_id' );
			$t_project_id = $f_project_id;
		}
		\Core\Project::ensure_exists( $t_project_id );
		
		if( $t_project_id != \Core\Helper::get_current_project() ) {
			$g_project_override = $t_project_id;
		}
		
		\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'report_bug_threshold' ) );
		
		if( isset( $_GET['posted'] ) && empty( $_FILE ) && empty( $_POST ) ) {
			trigger_error( ERROR_FILE_TOO_BIG, ERROR );
		}
		
		$t_bug_data = new \Core\BugData;
		$t_bug_data->project_id             = $t_project_id;
		$t_bug_data->reporter_id            = \Core\Auth::get_current_user_id();
		$t_bug_data->build                  = \Core\GPC::get_string( 'build', '' );
		$t_bug_data->platform               = \Core\GPC::get_string( 'platform', '' );
		$t_bug_data->os                     = \Core\GPC::get_string( 'os', '' );
		$t_bug_data->os_build               = \Core\GPC::get_string( 'os_build', '' );
		$t_bug_data->version                = \Core\GPC::get_string( 'product_version', '' );
		$t_bug_data->profile_id             = \Core\GPC::get_int( 'profile_id', 0 );
		$t_bug_data->handler_id             = \Core\GPC::get_int( 'handler_id', 0 );
		$t_bug_data->view_state             = \Core\GPC::get_int( 'view_state', \Core\Config::mantis_get( 'default_bug_view_status' ) );
		$t_bug_data->category_id            = \Core\GPC::get_int( 'category_id', 0 );
		$t_bug_data->reproducibility        = \Core\GPC::get_int( 'reproducibility', \Core\Config::mantis_get( 'default_bug_reproducibility' ) );
		$t_bug_data->severity               = \Core\GPC::get_int( 'severity', \Core\Config::mantis_get( 'default_bug_severity' ) );
		$t_bug_data->priority               = \Core\GPC::get_int( 'priority', \Core\Config::mantis_get( 'default_bug_priority' ) );
		$t_bug_data->projection             = \Core\GPC::get_int( 'projection', \Core\Config::mantis_get( 'default_bug_projection' ) );
		$t_bug_data->eta                    = \Core\GPC::get_int( 'eta', \Core\Config::mantis_get( 'default_bug_eta' ) );
		$t_bug_data->resolution             = \Core\GPC::get_string( 'resolution', \Core\Config::mantis_get( 'default_bug_resolution' ) );
		$t_bug_data->status                 = \Core\GPC::get_string( 'status', \Core\Config::mantis_get( 'bug_submit_status' ) );
		$t_bug_data->summary                = \Core\GPC::get_string( 'summary' );
		$t_bug_data->description            = \Core\GPC::get_string( 'description' );
		$t_bug_data->steps_to_reproduce     = \Core\GPC::get_string( 'steps_to_reproduce', \Core\Config::mantis_get( 'default_bug_steps_to_reproduce' ) );
		$t_bug_data->additional_information = \Core\GPC::get_string( 'additional_info', \Core\Config::mantis_get( 'default_bug_additional_info' ) );
		$t_bug_data->due_date               = \Core\GPC::get_string( 'due_date', '' );
		if( \Core\Utility::is_blank( $t_bug_data->due_date ) ) {
			$t_bug_data->due_date = \Core\Date::get_null();
		}
		
		$f_rel_type                         = \Core\GPC::get_int( 'rel_type', BUG_REL_NONE );
		$f_files                            = gpc_get_file( 'ufile', null );
		$f_report_stay                      = \Core\GPC::get_bool( 'report_stay', false );
		$f_copy_notes_from_parent           = \Core\GPC::get_bool( 'copy_notes_from_parent', false );
		$f_copy_attachments_from_parent     = \Core\GPC::get_bool( 'copy_attachments_from_parent', false );
		
		if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'roadmap_update_threshold' ), $t_bug_data->project_id ) ) {
			$t_bug_data->target_version = \Core\GPC::get_string( 'target_version', '' );
		}
		
		# Prevent unauthorized users setting handler when reporting issue
		if( $t_bug_data->handler_id > 0 ) {
			\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'update_bug_assign_threshold' ) );
		}
		
		# if a profile was selected then let's use that information
		if( 0 != $t_bug_data->profile_id ) {
			if( \Core\Profile::is_global( $t_bug_data->profile_id ) ) {
				$t_row = \Core\User::get_profile_row( ALL_USERS, $t_bug_data->profile_id );
			} else {
				$t_row = \Core\User::get_profile_row( $t_bug_data->reporter_id, $t_bug_data->profile_id );
			}
		
			if( \Core\Utility::is_blank( $t_bug_data->platform ) ) {
				$t_bug_data->platform = $t_row['platform'];
			}
			if( \Core\Utility::is_blank( $t_bug_data->os ) ) {
				$t_bug_data->os = $t_row['os'];
			}
			if( \Core\Utility::is_blank( $t_bug_data->os_build ) ) {
				$t_bug_data->os_build = $t_row['os_build'];
			}
		}
		\Core\Helper::call_custom_function( 'issue_create_validate', array( $t_bug_data ) );
		
		# Validate the custom fields before adding the bug.
		$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
		foreach( $t_related_custom_field_ids as $t_id ) {
			$t_def = custom_field_get_definition( $t_id );
		
			# Produce an error if the field is required but wasn't posted
			if( !\Core\GPC::isset_custom_field( $t_id, $t_def['type'] )
			   && $t_def['require_report']
			) {
				\Core\Error::parameters( \Core\Lang::get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
		
			if( !custom_field_validate( $t_id, \Core\GPC::get_custom_field( 'custom_field_' . $t_id, $t_def['type'], null ) ) ) {
				\Core\Error::parameters( \Core\Lang::get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
				trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
			}
		}
		
		# Allow plugins to pre-process bug data
		$t_bug_data = \Core\Event::signal( 'EVENT_REPORT_BUG_DATA', $t_bug_data );
		
		# Ensure that resolved bugs have a handler
		if( $t_bug_data->handler_id == NO_USER && $t_bug_data->status >= \Core\Config::mantis_get( 'bug_resolved_status_threshold' ) ) {
			$t_bug_data->handler_id = \Core\Auth::get_current_user_id();
		}
		
		
		# Create the bug
		$t_bug_id = $t_bug_data->create();
		
		# Mark the added issue as visited so that it appears on the last visited list.
		\Core\Last_Visited::issue( $t_bug_id );
		
		# Handle the file upload
		if( !is_null( $f_files ) ) {
			$t_files = \Core\Helper::array_transpose( $f_files );
			foreach( $t_files as $t_file ) {
				if( !empty( $t_file['name'] ) ) {
					\Core\File::add( $t_bug_id, $t_file, 'bug' );
				}
			}
		}
		
		# Handle custom field submission
		foreach( $t_related_custom_field_ids as $t_id ) {
			# Do not set custom field value if user has no write access
			if( !custom_field_has_write_access( $t_id, $t_bug_id ) ) {
				continue;
			}
		
			$t_def = custom_field_get_definition( $t_id );
			if( !custom_field_set_value( $t_id, $t_bug_id, \Core\GPC::get_custom_field( 'custom_field_' . $t_id, $t_def['type'], $t_def['default_value'] ), false ) ) {
				\Core\Error::parameters( \Core\Lang::get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
				trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
			}
		}
		
		if( $f_master_bug_id > 0 ) {
			# it's a child generation... let's create the relationship and add some lines in the history
		
			# update master bug last updated
			\Core\Bug::update_date( $f_master_bug_id );
		
			# Add log line to record the cloning action
			\Core\History::log_event_special( $t_bug_id, BUG_CREATED_FROM, '', $f_master_bug_id );
			\Core\History::log_event_special( $f_master_bug_id, BUG_CLONED_TO, '', $t_bug_id );
		
			if( $f_rel_type > BUG_REL_ANY ) {
				# Add the relationship
				\Core\Relationship::add( $t_bug_id, $f_master_bug_id, $f_rel_type );
		
				# Add log line to the history (both issues)
				\Core\History::log_event_special( $f_master_bug_id, BUG_ADD_RELATIONSHIP, \Core\Relationship::get_complementary_type( $f_rel_type ), $t_bug_id );
				\Core\History::log_event_special( $t_bug_id, BUG_ADD_RELATIONSHIP, $f_rel_type, $f_master_bug_id );
		
				# Send the email notification
				\Core\Email::relationship_added( $f_master_bug_id, $t_bug_id, \Core\Relationship::get_complementary_type( $f_rel_type ) );
		
				# update relationship target bug last updated
				\Core\Bug::update_date( $t_bug_id );
			}
		
			# copy notes from parent
			if( $f_copy_notes_from_parent ) {
		
				$t_parent_bugnotes = \Core\Bug\Note::get_all_bugnotes( $f_master_bug_id );
		
				foreach ( $t_parent_bugnotes as $t_parent_bugnote ) {
					$t_private = $t_parent_bugnote->view_state == VS_PRIVATE;
		
					\Core\Bug\Note::add(
						$t_bug_id,
						$t_parent_bugnote->note,
						$t_parent_bugnote->time_tracking,
						$t_private,
						$t_parent_bugnote->note_type,
						$t_parent_bugnote->note_attr,
						$t_parent_bugnote->reporter_id,
						false,
						0,
						0,
						false );
				}
			}
		
			# copy attachments from parent
			if( $f_copy_attachments_from_parent ) {
				\Core\File::copy_attachments( $f_master_bug_id, $t_bug_id );
			}
		}
		
		\Core\Helper::call_custom_function( 'issue_create_notify', array( $t_bug_id ) );
		
		# Allow plugins to post-process bug data with the new bug ID
		\Core\Event::signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );
		
		\Core\Email::generic( $t_bug_id, 'new', 'email_notification_title_for_action_bug_submitted' );
		
		# log status and resolution changes if they differ from the default
		if( $t_bug_data->status != \Core\Config::mantis_get( 'bug_submit_status' ) ) {
			\Core\History::log_event( $t_bug_id, 'status', \Core\Config::mantis_get( 'bug_submit_status' ) );
		}
		
		if( $t_bug_data->resolution != \Core\Config::mantis_get( 'default_bug_resolution' ) ) {
			\Core\History::log_event( $t_bug_id, 'resolution', \Core\Config::mantis_get( 'default_bug_resolution' ) );
		}

		self::action_get();
	}
	
	function action_get()
	{
		$this->set([
			'page_title'	=> \Core\Lang::get('report_bug_link'),
			'view'			=> 'Pages/Report_A_Bug',
		]);
		
		$f_master_bug_id = \Core\GPC::get_int( 'm_id', 0 );
		
		if( $f_master_bug_id > 0 ) {
			# master bug exists...
			\Core\Bug::ensure_exists( $f_master_bug_id );
		
			# master bug is not read-only...
			if( \Core\Bug::is_readonly( $f_master_bug_id ) ) {
				\Core\Error::parameters( $f_master_bug_id );
				trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
			}
		
			$t_bug = \Core\Bug::get( $f_master_bug_id, true );
		
			#@@@ (thraxisp) Note that the master bug is cloned into the same project as the master, independent of
			#       what the current project is set to.
			if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
				# in case the current project is not the same project of the bug we are viewing...
				# ... override the current project. This to avoid problems with categories and handlers lists etc.
				$g_project_override = $t_bug->project_id;
				$t_changed_project = true;
			} else {
				$t_changed_project = false;
			}
		
			\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'report_bug_threshold' ) );
		
			$f_build				= $t_bug->build;
			$f_platform				= $t_bug->platform;
			$f_os					= $t_bug->os;
			$f_os_build				= $t_bug->os_build;
			$f_product_version		= $t_bug->version;
			$f_target_version		= $t_bug->target_version;
			$f_profile_id			= 0;
			$f_handler_id			= $t_bug->handler_id;
		
			$f_category_id			= $t_bug->category_id;
			$f_reproducibility		= $t_bug->reproducibility;
			$f_eta					= $t_bug->eta;
			$f_severity				= $t_bug->severity;
			$f_priority				= $t_bug->priority;
			$f_summary				= $t_bug->summary;
			$f_description			= $t_bug->description;
			$f_steps_to_reproduce	= $t_bug->steps_to_reproduce;
			$f_additional_info		= $t_bug->additional_information;
			$f_view_state			= (int)$t_bug->view_state;
			$f_due_date				= $t_bug->due_date;
		
			$t_project_id			= $t_bug->project_id;
		} else {
			# Get Project Id and set it as current
			$t_current_project = \Core\Helper::get_current_project();
			$t_project_id = \Core\GPC::get_int( 'project_id', $t_current_project );
		
			# If all projects, use default project if set
			$t_default_project = \Core\User\Pref::get_pref( \Core\Auth::get_current_user_id(), 'default_project' );
			if( ALL_PROJECTS == $t_project_id && ALL_PROJECTS != $t_default_project ) {
				$t_project_id = $t_default_project;
			}
		
			if( ( ALL_PROJECTS == $t_project_id || \Core\Project::exists( $t_project_id ) )
			 && $t_project_id != $t_current_project
			) {
				\Core\Helper::set_current_project( $t_project_id );
				# Reloading the page is required so that the project browser
				# reflects the new current project
				\Core\Print_Util::header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
			}
		
			# New issues cannot be reported for the 'All Project' selection
			/*if( ALL_PROJECTS == $t_current_project ) {
				\Core\Print_Util::header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
			}*/
		
			\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'report_bug_threshold' ) );
		
			$f_build				= \Core\GPC::get_string( 'build', '' );
			$f_platform				= \Core\GPC::get_string( 'platform', '' );
			$f_os					= \Core\GPC::get_string( 'os', '' );
			$f_os_build				= \Core\GPC::get_string( 'os_build', '' );
			$f_product_version		= \Core\GPC::get_string( 'product_version', '' );
			$f_target_version		= \Core\GPC::get_string( 'target_version', '' );
			$f_profile_id			= \Core\GPC::get_int( 'profile_id', 0 );
			$f_handler_id			= \Core\GPC::get_int( 'handler_id', 0 );
		
			$f_category_id			= \Core\GPC::get_int( 'category_id', 0 );
			$f_reproducibility		= \Core\GPC::get_int( 'reproducibility', (int)\Core\Config::mantis_get( 'default_bug_reproducibility' ) );
			$f_eta					= \Core\GPC::get_int( 'eta', (int)\Core\Config::mantis_get( 'default_bug_eta' ) );
			$f_severity				= \Core\GPC::get_int( 'severity', (int)\Core\Config::mantis_get( 'default_bug_severity' ) );
			$f_priority				= \Core\GPC::get_int( 'priority', (int)\Core\Config::mantis_get( 'default_bug_priority' ) );
			$f_summary				= \Core\GPC::get_string( 'summary', '' );
			$f_description			= \Core\GPC::get_string( 'description', '' );
			$f_steps_to_reproduce	= \Core\GPC::get_string( 'steps_to_reproduce', \Core\Config::mantis_get( 'default_bug_steps_to_reproduce' ) );
			$f_additional_info		= \Core\GPC::get_string( 'additional_info', \Core\Config::mantis_get( 'default_bug_additional_info' ) );
			$f_view_state			= \Core\GPC::get_int( 'view_state', (int)\Core\Config::mantis_get( 'default_bug_view_status' ) );
			$f_due_date				= \Core\GPC::get_string( 'due_date', '' );
		
			if( $f_due_date == '' ) {
				$f_due_date = \Core\Date::get_null();
			}
		
			$t_changed_project		= false;
		}
		
		$f_report_stay			= \Core\GPC::get_bool( 'report_stay', false );
		$f_copy_notes_from_parent         = \Core\GPC::get_bool( 'copy_notes_from_parent', false );
		$f_copy_attachments_from_parent   = \Core\GPC::get_bool( 'copy_attachments_from_parent', false );
		
		$t_fields = \Core\Config::mantis_get( 'bug_report_page_fields' );
		$t_fields = \Core\Columns::filter_disabled( $t_fields );
		
		$t_show_category = in_array( 'category_id', $t_fields );
		$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
		$t_show_eta = in_array( 'eta', $t_fields );
		$t_show_severity = in_array( 'severity', $t_fields );
		$t_show_priority = in_array( 'priority', $t_fields );
		$t_show_steps_to_reproduce = in_array( 'steps_to_reproduce', $t_fields );
		$t_show_handler = in_array( 'handler', $t_fields ) && \Core\Access::has_project_level( \Core\Config::mantis_get( 'update_bug_assign_threshold' ) );
		$t_show_profiles = \Core\Config::mantis_get( 'enable_profiles' );
		$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
		$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
		$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
		$t_show_resolution = in_array( 'resolution', $t_fields );
		$t_show_status = in_array( 'status', $t_fields );
		
		$t_show_versions = \Core\Version::should_show_product_version( $t_project_id );
		$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
		$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && \Core\Config::mantis_get( 'enable_product_build' ) == ON;
		$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && \Core\Access::has_project_level( \Core\Config::mantis_get( 'roadmap_update_threshold' ) );
		$t_show_additional_info = in_array( 'additional_info', $t_fields );
		$t_show_due_date = in_array( 'due_date', $t_fields ) && \Core\Access::has_project_level( \Core\Config::mantis_get( 'due_date_update_threshold' ), \Core\Helper::get_current_project(), \Core\Auth::get_current_user_id() );
		$t_show_attachments = in_array( 'attachments', $t_fields ) && \Core\File::allow_bug_upload();
		$t_show_view_state = in_array( 'view_state', $t_fields ) && \Core\Access::has_project_level( \Core\Config::mantis_get( 'set_view_status_threshold' ) );
		
		if( $t_show_due_date ) {
			\Core\HTML::require_js( 'jscalendar/calendar.js' );
			\Core\HTML::require_js( 'jscalendar/lang/calendar-en.js' );
			\Core\HTML::require_js( 'jscalendar/calendar-setup.js' );
			\Core\HTML::require_css( 'calendar-blue.css' );
		}
		
		$t_form_encoding = '';
		if( $t_show_attachments ) {
			$t_form_encoding = 'enctype="multipart/form-data"';
		}
		
		$this->set(get_defined_vars());
	}
}