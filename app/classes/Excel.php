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
 * Excel API
 *
 * @package CoreAPI
 * @subpackage ExcelAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses project_api.php
 * @uses user_api.php
 */

require_api( 'custom_field_api.php' );


class Excel
{
	
	/**
	 * A method that returns the header for an Excel Xml file.
	 *
	 * @param string $p_worksheet_title The worksheet title.
	 * @param array  $p_styles          An optional array of \Core\Excel\Style entries . Parent entries must be placed before child entries.
	 * @return string the header Xml.
	 */
	function excel_get_header( $p_worksheet_title, array $p_styles = array() ) {
		$p_worksheet_title = preg_replace( '/[\/:*?"<>|]/', '', $p_worksheet_title );
		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><?mso-application progid=\"Excel.Sheet\"?>
	 <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
	 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
	 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
	 xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n ". excel_get_styles( $p_styles ). '<Worksheet ss:Name="' . urlencode( $p_worksheet_title ) . "\">\n<Table>\n<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
	}
	
	/**
	 * Returns an XML string containing the <tt>ss:Styles</tt> entry, possibly empty
	 *
	 * @param array $p_styles An array of \Core\Excel\Style entries.
	 * @return null|string
	 */
	function excel_get_styles( array $p_styles ) {
		if( count( $p_styles ) == 0 ) {
			return null;
		}
	
		$t_styles_string = '<ss:Styles>';
	
		foreach ( $p_styles as $t_style ) {
			$t_styles_string .= $t_style->asXml();
		}
		$t_styles_string .= '</ss:Styles>';
	
		return $t_styles_string;
	}
	
	/**
	 * A method that returns the footer for an Excel Xml file.
	 * @return string the footer xml.
	 */
	function excel_get_footer() {
		return "</Table>\n</Worksheet></Workbook>\n";
	}
	
	/**
	 * Generates a cell XML for a column title.
	 * @param string $p_column_title Column title.
	 * @return string The cell xml.
	 */
	function excel_format_column_title( $p_column_title ) {
		return '<Cell><Data ss:Type="String">' . $p_column_title . '</Data></Cell>';
	}
	
	/**
	 * Generates the xml for the start of an Excel row.
	 *
	 * @param string $p_style_id The optional style id.
	 * @return string The Row tag.
	 */
	function excel_get_start_row( $p_style_id = '' ) {
		if( $p_style_id != '' ) {
			return '<Row ss:StyleID="' . $p_style_id . '">';
		} else {
			return '<Row>';
		}
	}
	
	/**
	 * Generates the xml for the end of an Excel row.
	 * @return string The Row end tag.
	 */
	function excel_get_end_row() {
		return '</Row>';
	}
	
	/**
	 * Gets an Xml Row that contains all column titles
	 * @param string $p_style_id The optional style id.
	 * @return string The xml row.
	 */
	function excel_get_titles_row( $p_style_id = '' ) {
		$t_columns = excel_get_columns();
		$t_ret = excel_get_start_row( $p_style_id );
	
		foreach( $t_columns as $t_column ) {
			$t_ret .= excel_format_column_title( \Core\Columns::column_get_title( $t_column ) );
		}
	
		$t_ret .= '</Row>';
	
		return $t_ret;
	}
	
	/**
	 * Gets the download file name for the Excel export.  If 'All Projects' selected, default to <username>,
	 * otherwise default to <projectname>.
	 * @return string file name without extension
	 */
	function excel_get_default_filename() {
		$t_current_project_id = \Core\Helper::get_current_project();
	
		if( ALL_PROJECTS == $t_current_project_id ) {
			$t_filename = \Core\User::get_name( \Core\Auth::get_current_user_id() );
		} else {
			$t_filename = \Core\Project::get_field( $t_current_project_id, 'name' );
		}
	
		return $t_filename;
	}
	
	/**
	 * Escapes the specified column value and includes it in a Cell Xml.
	 * @param string $p_value The value.
	 * @return string The Cell Xml.
	 */
	function excel_prepare_string( $p_value ) {
		$t_type = is_numeric( $p_value ) ? 'Number' : 'String';
	
		$t_value = str_replace( array ( '&', "\n", '<', '>'), array ( '&amp;', '&#10;', '&lt;', '&gt;' ), $p_value );
	
		return excel_get_cell( $t_value, $t_type );
	}
	
	/**
	 * Returns an <tt>Cell</tt> as an XML string
	 *
	 * <p>All the parameters are assumed to be valid and escaped, as this function performs no
	 * escaping of its own.</p>
	 *
	 * @param string $p_value      Cell Value.
	 * @param string $p_type       Cell Type.
	 * @param array  $p_attributes An array where the keys are attribute names and values attribute
	 *                             values for the <tt>Cell</tt> object.
	 * @return string
	 */
	function excel_get_cell( $p_value, $p_type, array $p_attributes = array() ) {
		$t_ret = '<Cell ';
	
		foreach ( $p_attributes as $t_attribute_name => $t_attribute_value ) {
			$t_ret .= $t_attribute_name. '="' . $t_attribute_value . '" ';
		}
	
		$t_ret .= '>';
	
		$t_ret .= '<Data ss:Type="' . $p_type . '">' . $p_value . "</Data></Cell>\n";
	
		return $t_ret;
	}
	
	/**
	 * Gets the columns to be included in the Excel Xml export.
	 * @return array column names.
	 */
	function excel_get_columns() {
		$t_columns = \Core\Helper::get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE );
		return $t_columns;
	}
	
	#
	# Formatting Functions
	#
	# Names for formatting functions are excel_format_*, where * corresponds to the
	# field name as return get excel_get_columns() and by the filter api.
	#
	/**
	 * Gets the formatted bug id value.
	 * @param \Core\BugData $p_bug The bug object.
	 * @return string The bug id prefixed with 0s.
	 */
	function excel_format_id( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Bug::format_id( $p_bug->id ) );
	}
	
	/**
	 * Gets the formatted project id value.
	 * @param \Core\BugData $p_bug The bug object.
	 * @return string The project name.
	 */
	function excel_format_project_id( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Project::get_name( $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted reporter id value.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The reporter user name.
	 */
	function excel_format_reporter_id( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\User::get_name( $p_bug->reporter_id ) );
	}
	
	/**
	 * Gets the formatted number of bug notes.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The number of bug notes.
	 */
	function excel_format_bugnotes_count( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->bugnotes_count );
	}
	
	/**
	 * Gets the formatted handler id.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The handler user name or empty string.
	 */
	function excel_format_handler_id( \Core\BugData $p_bug ) {
		if( $p_bug->handler_id > 0 ) {
			return excel_prepare_string( \Core\User::get_name( $p_bug->handler_id ) );
		} else {
			return excel_prepare_string( '' );
		}
	}
	
	/**
	 * Gets the formatted priority.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the priority text.
	 */
	function excel_format_priority( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'priority', $p_bug->priority, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted severity.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the severity text.
	 */
	function excel_format_severity( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'severity', $p_bug->severity, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted reproducibility.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the reproducibility text.
	 */
	function excel_format_reproducibility( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'reproducibility', $p_bug->reproducibility, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted view state,
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The view state
	 */
	function excel_format_view_state( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'view_state', $p_bug->view_state, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted projection.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the projection text.
	 */
	function excel_format_projection( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'projection', $p_bug->projection, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted eta.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the eta text.
	 */
	function excel_format_eta( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'eta', $p_bug->eta, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the status field.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the formatted status.
	 */
	function excel_format_status( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'status', $p_bug->status, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the resolution field.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the formatted resolution.
	 */
	function excel_format_resolution( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Helper::get_enum_element( 'resolution', $p_bug->resolution, \Core\Auth::get_current_user_id(), $p_bug->project_id ) );
	}
	
	/**
	 * Gets the formatted version.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the product version.
	 */
	function excel_format_version( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->version );
	}
	
	/**
	 * Gets the formatted fixed in version.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the fixed in version.
	 */
	function excel_format_fixed_in_version( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->fixed_in_version );
	}
	
	/**
	 * Gets the formatted target version.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the target version.
	 */
	function excel_format_target_version( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->target_version );
	}
	
	/**
	 * Gets the formatted category.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the category.
	 */
	function excel_format_category_id( \Core\BugData $p_bug ) {
		return excel_prepare_string( \Core\Category::full_name( $p_bug->category_id, false ) );
	}
	
	/**
	 * Gets the formatted operating system.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the operating system.
	 */
	function excel_format_os( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->os );
	}
	
	/**
	 * Gets the formatted operating system build (version).
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the operating system build (version)
	 */
	function excel_format_os_build( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->os_build );
	}
	
	/**
	 * Gets the formatted product build,
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the product build.
	 */
	function excel_format_build( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->build );
	}
	
	/**
	 * Gets the formatted platform,
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the platform.
	 */
	function excel_format_platform( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->platform );
	}
	
	/**
	 * Gets the formatted date submitted.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the date submitted in short date format.
	 */
	function excel_format_date_submitted( \Core\BugData $p_bug ) {
		return excel_prepare_string( date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->date_submitted ) );
	}
	
	/**
	 * Gets the formatted date last updated.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the date last updated in short date format.
	 */
	function excel_format_last_updated( \Core\BugData $p_bug ) {
		return excel_prepare_string( date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->last_updated ) );
	}
	
	/**
	 * Gets the summary field.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string the formatted summary.
	 */
	function excel_format_summary( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->summary );
	}
	
	/**
	 * Gets the formatted selection.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string an formatted empty string.
	 */
	function excel_format_selection( \Core\BugData $p_bug ) {
		return excel_prepare_string( '' );
	}
	
	/**
	 * Gets the formatted description field.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The formatted description (multi-line).
	 */
	function excel_format_description( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->description );
	}
	
	/**
	 * Gets the formatted 'steps to reproduce' field.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The formatted steps to reproduce (multi-line).
	 */
	function excel_format_steps_to_reproduce( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->steps_to_reproduce );
	}
	
	/**
	 * Gets the formatted 'additional information' field.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The formatted additional information (multi-line).
	 */
	function excel_format_additional_information( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->additional_information );
	}
	
	/**
	 * Gets the formatted value for the specified issue id, project and custom field.
	 * @param integer $p_issue_id     The issue id.
	 * @param integer $p_project_id   The project id.
	 * @param string  $p_custom_field The custom field name (without 'custom_' prefix).
	 * @return string The custom field value.
	 */
	function excel_format_custom_field( $p_issue_id, $p_project_id, $p_custom_field ) {
		$t_field_id = custom_field_get_id_from_name( $p_custom_field );
	
		if( $t_field_id === false ) {
			return excel_prepare_string( '@' . $p_custom_field . '@' );
		}
	
		if( custom_field_is_linked( $t_field_id, $p_project_id ) ) {
			$t_def = custom_field_get_definition( $t_field_id );
			return excel_prepare_string( string_custom_field_value( $t_def, $t_field_id, $p_issue_id ) );
		}
	
		# field is not linked to project
		return excel_prepare_string( '' );
	}
	
	/**
	 * Gets the formatted value for the specified plugin column value.
	 * @param string  $p_column The plugin column name.
	 * @param \Core\BugData $p_bug    A bug object to print the column for - needed for the display function of the plugin column.
	 * @return string The plugin column value.
	 */
	function excel_format_plugin_column_value( $p_column, \Core\BugData $p_bug ) {
		$t_plugin_columns = \Core\Columns::get_plugin_columns();
	
		if( !isset( $t_plugin_columns[$p_column] ) ) {
			return excel_prepare_string( '' );
		} else {
			$t_column_object = $t_plugin_columns[$p_column];
			ob_start();
			$t_column_object->display( $p_bug, COLUMNS_TARGET_EXCEL_PAGE );
			$t_value = ob_get_clean();
			return excel_prepare_string( $t_value );
		}
	}
	
	/**
	 * Gets the formatted due date.
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string The formatted due date.
	 */
	function excel_format_due_date( \Core\BugData $p_bug ) {
		return excel_prepare_string( date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->due_date ) );
	}
	
	/**
	 * Gets the sponsorship total for an issue
	 * @param \Core\BugData $p_bug A bug object.
	 * @return string
	 * @access public
	 */
	function excel_format_sponsorship_total( \Core\BugData $p_bug ) {
		return excel_prepare_string( $p_bug->sponsorship_total );
	}
	
}