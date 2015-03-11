<?php
namespace Core;


# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CSV API
 *
 * @package CoreAPI
 * @subpackage CSVAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses project_api.php
 * @uses user_api.php
 */



class CSV
{

	/**
	 * get the csv file new line, can be moved to config in the future
	 * @return string containing new line character
	 * @access public
	 */
	static function get_newline() {
		return "\r\n";
	}
	
	/**
	 * get the csv file separator, can be moved to config in the future
	 * @return string
	 * @access public
	 */
	static function get_separator() {
		static $s_seperator = null;
		if( $s_seperator === null ) {
			$s_seperator = \Core\Config::mantis_get( 'csv_separator' );
		}
		return $s_seperator;
	}
	
	/**
	 * if all projects selected, default to <username>.csv, otherwise default to
	 * <projectname>.csv.
	 * @return string filename
	 * @access public
	 */
	static function get_default_filename() {
		$t_current_project_id = \Core\Helper::get_current_project();
	
		if( ALL_PROJECTS == $t_current_project_id ) {
			$t_filename = \Core\User::get_name( \Core\Auth::get_current_user_id() );
		} else {
			$t_filename = \Core\Project::get_field( $t_current_project_id, 'name' );
		}
	
		return $t_filename . '.csv';
	}
	
	/**
	 * escape a string before writing it to csv file.
	 * @param string $p_string String to escape.
	 * @return string
	 * @access public
	 */
	static function escape_string( $p_string ) {
			$t_escaped = str_split( '"' . csv_get_separator() . \Core\CSV::get_newline() );
			$t_must_escape = false;
			while( ( $t_char = current( $t_escaped ) ) !== false && !$t_must_escape ) {
				$t_must_escape = strpos( $p_string, $t_char ) !== false;
				next( $t_escaped );
			}
			if( $t_must_escape ) {
				$p_string = '"' . str_replace( '"', '""', $p_string ) . '"';
			}
	
			return $p_string;
	}
	
	/**
	 * An array of column names that are used to identify fields to include and in which order.
	 * @return array
	 * @access public
	 */
	static function get_columns() {
		$t_columns = \Core\Helper::get_columns_to_view( COLUMNS_TARGET_CSV_PAGE );
		return $t_columns;
	}
	
	/**
	 * returns the formatted bug id
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string csv formatted bug id
	 * @access public
	 */
	static function format_id( \Core\BugData $p_bug ) {
		return \Core\Bug::format_id( $p_bug->id );
	}
	
	/**
	 * returns the project name corresponding to the supplied bug
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string csv formatted project name
	 * @access public
	 */
	static function format_project_id( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Project::get_name( $p_bug->project_id ) );
	}
	
	/**
	 * returns the reporter name corresponding to the supplied bug
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted user name
	 * @access public
	 */
	static function format_reporter_id( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\User::get_name( $p_bug->reporter_id ) );
	}
	
	/**
	 * returns the handler name corresponding to the supplied bug
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted user name
	 * @access public
	 */
	static function format_handler_id( \Core\BugData $p_bug ) {
		if( $p_bug->handler_id > 0 ) {
			return \Core\CSV::escape_string( \Core\User::get_name( $p_bug->handler_id ) );
		}
		return '';
	}
	
	/**
	 * return the priority string
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted priority string
	 * @access public
	 */
	static function format_priority( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'priority', $p_bug->priority, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the severity string
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted severity string
	 * @access public
	 */
	static function format_severity( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'severity', $p_bug->severity, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the reproducibility string
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted reproducibility string
	 * @access public
	 */
	static function format_reproducibility( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'reproducibility', $p_bug->reproducibility, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the version
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted version string
	 * @access public
	 */
	static function format_version( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->version );
	}
	
	/**
	 * return the fixed_in_version
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted fixed in version string
	 * @access public
	 */
	static function format_fixed_in_version( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->fixed_in_version );
	}
	
	/**
	 * return the target_version
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted target version string
	 * @access public
	 */
	static function format_target_version( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->target_version );
	}
	
	/**
	 * return the projection
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted projection string
	 * @access public
	 */
	static function format_projection( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'projection', $p_bug->projection, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the category
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted category string
	 * @access public
	 */
	static function format_category_id( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Category::full_name( $p_bug->category_id, false ) );
	}
	
	/**
	 * return the date submitted
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted date
	 * @access public
	 */
	static function format_date_submitted( \Core\BugData $p_bug ) {
		static $s_date_format = null;
		if( $s_date_format === null ) {
			$s_date_format = \Core\Config::mantis_get( 'short_date_format' );
		}
		return date( $s_date_format, $p_bug->date_submitted );
	}
	
	/**
	 * return the eta
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted eta
	 * @access public
	 */
	static function format_eta( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'eta', $p_bug->eta, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the operating system
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted operating system
	 * @access public
	 */
	static function format_os( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->os );
	}
	
	/**
	 * return the os build (os version)
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted operating system build
	 * @access public
	 */
	static function format_os_build( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->os_build );
	}
	
	/**
	 * return the build
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted build
	 * @access public
	 */
	static function format_build( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->build );
	}
	
	/**
	 * return the platform
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted platform
	 * @access public
	 */
	static function format_platform( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->platform );
	}
	
	/**
	 * return the view state (either private or public)
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted view state
	 * @access public
	 */
	static function format_view_state( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'view_state', $p_bug->view_state, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the last updated date
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted last updated string
	 * @access public
	 */
	static function format_last_updated( \Core\BugData $p_bug ) {
		static $s_date_format = null;
		if( $s_date_format === null ) {
			$s_date_format = \Core\Config::mantis_get( 'short_date_format' );
		}
		return date( $s_date_format, $p_bug->last_updated );
	}
	
	/**
	 * return the summary
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted summary
	 * @access public
	 */
	static function format_summary( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->summary );
	}
	
	/**
	 * return the description
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted description
	 * @access public
	 */
	static function format_description( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->description );
	}
	
	/**
	 * return the steps to reproduce
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted steps to reproduce
	 * @access public
	 */
	static function format_steps_to_reproduce( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->steps_to_reproduce );
	}
	
	/**
	 * return the additional information
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted additional information
	 * @access public
	 */
	static function format_additional_information( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->additional_information );
	}
	
	/**
	 * return the status string
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted status
	 * @access public
	 */
	static function format_status( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'status', $p_bug->status, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the resolution string
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted resolution string
	 * @access public
	 */
	static function format_resolution( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( \Core\Helper::get_enum_element( 'resolution', $p_bug->resolution, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * return the duplicate bug id
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string formatted bug id
	 * @access public
	 */
	static function format_duplicate_id( \Core\BugData $p_bug ) {
		return \Core\Bug::format_id( $p_bug->duplicate_id );
	}
	
	/**
	 * return the selection
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string
	 * @access public
	 */
	static function format_selection( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( '' );
	}
	
	/**
	 * return the due date column
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string
	 * @access public
	 */
	static function format_due_date( \Core\BugData $p_bug ) {
		static $s_date_format = null;
		if( $s_date_format === null ) {
			$s_date_format = \Core\Config::mantis_get( 'short_date_format' );
		}
		return \Core\CSV::escape_string( date( $s_date_format, $p_bug->due_date ) );
	}
	
	/**
	 * return the sponsorship total for an issue
	 * @param \Core\BugData $p_bug A \Core\BugData object.
	 * @return string
	 * @access public
	 */
	static function format_sponsorship_total( \Core\BugData $p_bug ) {
		return \Core\CSV::escape_string( $p_bug->sponsorship_total );
	}

}