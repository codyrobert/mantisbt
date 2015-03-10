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
 * Columns API
 *
 * @package CoreAPI
 * @subpackage ColumnsAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses sponsorship_api.php
 * @uses string_api.php
 */

require_api( 'custom_field_api.php' );


class Columns
{

	/**
	 * Filters an array of columns based on configuration options.  The filtering can remove
	 * columns whose features are disabled.
	 *
	 * @param array $p_columns The columns proposed for display.
	 * @return array The columns array after removing the disabled features.
	 */
	static function filter_disabled( array $p_columns ) {
		$t_columns = array();
		$t_enable_profiles = ( \Core\Config::mantis_get( 'enable_profiles' ) == ON );
	
		foreach ( $p_columns as $t_column ) {
			switch( $t_column ) {
				case 'os':
				case 'os_build':
				case 'platform':
					if( ! $t_enable_profiles ) {
						continue 2;
					}
					# don't filter
					break;
	
				case 'eta':
					if( \Core\Config::mantis_get( 'enable_eta' ) == OFF ) {
						continue 2;
					}
					break;
	
				case 'projection':
					if( \Core\Config::mantis_get( 'enable_projection' ) == OFF ) {
						continue 2;
					}
					break;
	
				case 'build':
					if( \Core\Config::mantis_get( 'enable_product_build' ) == OFF ) {
						continue 2;
					}
					break;
	
				default:
					# don't filter
					break;
			}
			$t_columns[] = $t_column;
		} # continued 2
	
		return $t_columns;
	}
	
	/**
	 * Get a list of standard columns.
	 * @param boolean $p_enabled_columns_only Default true, if false returns all columns regardless of configuration settings.
	 * @return array of column names
	 */
	static function get_standard( $p_enabled_columns_only = true ) {
		$t_reflection = new \ReflectionClass( '\\Core\\BugData' );
		$t_columns = $t_reflection->getDefaultProperties();
	
		$t_columns['selection'] = null;
		$t_columns['edit'] = null;
	
		# Overdue icon column (icons appears if an issue is beyond due_date)
		$t_columns['overdue'] = null;
	
		if( $p_enabled_columns_only && OFF == \Core\Config::mantis_get( 'enable_profiles' ) ) {
			unset( $t_columns['os'] );
			unset( $t_columns['os_build'] );
			unset( $t_columns['platform'] );
		}
	
		if( $p_enabled_columns_only && \Core\Config::mantis_get( 'enable_eta' ) == OFF ) {
			unset( $t_columns['eta'] );
		}
	
		if( $p_enabled_columns_only && \Core\Config::mantis_get( 'enable_projection' ) == OFF ) {
			unset( $t_columns['projection'] );
		}
	
		if( $p_enabled_columns_only && \Core\Config::mantis_get( 'enable_product_build' ) == OFF ) {
			unset( $t_columns['build'] );
		}
	
		if( $p_enabled_columns_only && \Core\Config::mantis_get( 'enable_sponsorship' ) == OFF ) {
			unset( $t_columns['sponsorship_total'] );
		}
	
		# The following fields are used internally and don't make sense as columns
		unset( $t_columns['_stats'] );
		unset( $t_columns['profile_id'] );
		unset( $t_columns['sticky'] );
		unset( $t_columns['loading'] );
	
		# legacy field
		unset( $t_columns['duplicate_id'] );
	
		return array_keys( $t_columns );
	}
	
	/**
	 * Allow plugins to define a set of class-based columns, and register/load
	 * them here to be used by columns_api.
	 * @return array Mapping of column name to column object
	 */
	static function get_plugin_columns() {
		static $s_column_array = null;
	
		if( is_null( $s_column_array ) ) {
			$s_column_array = array();
	
			$t_all_plugin_columns = \Core\Event::signal( 'EVENT_FILTER_COLUMNS' );
			foreach( $t_all_plugin_columns as $t_plugin => $t_plugin_columns ) {
				foreach( $t_plugin_columns as $t_callback => $t_plugin_column_array ) {
					if( is_array( $t_plugin_column_array ) ) {
						foreach( $t_plugin_column_array as $t_column_class ) {
							if( class_exists( $t_column_class ) && is_subclass_of( $t_column_class, '\\Core\\MantisColumn' ) ) {
								$t_column_object = new $t_column_class();
								$t_column_name = utf8_strtolower( $t_plugin . '_' . $t_column_object->column );
								$s_column_array[$t_column_name] = $t_column_object;
							}
						}
					}
				}
			}
		}
	
		return $s_column_array;
	}
	
	/**
	 * Returns true if the specified $p_column is a plugin column.
	 * @param string $p_column A column name.
	 * @return boolean
	 */
	static function column_is_plugin_column( $p_column ) {
		$t_plugin_columns = \Core\Columns::get_plugin_columns();
		return isset( $t_plugin_columns[$p_column] );
	}
	
	/**
	 * Allow plugin columns to pre-cache data for a set of issues
	 * rather than requiring repeated queries for each issue.
	 * @param array $p_bugs Array of \Core\BugData objects.
	 * @return void
	 */
	static function plugin_cache_issue_data( array $p_bugs ) {
		$t_columns = \Core\Columns::get_plugin_columns();
	
		foreach( $t_columns as $t_column_object ) {
			$t_column_object->cache( $p_bugs );
		}
	}
	
	/**
	 * Get all accessible columns for the current project / current user.
	 * @param integer $p_project_id A project identifier.
	 * @return array array of columns
	 * @access public
	 */
	static function get_all( $p_project_id = null ) {
		$t_columns = \Core\Columns::get_standard();
	
		# add plugin columns
		$t_columns = array_merge( $t_columns, array_keys( \Core\Columns::get_plugin_columns() ) );
	
		# Add project custom fields to the array.  Only add the ones for which the current user has at least read access.
		if( $p_project_id === null ) {
			$t_project_id = \Core\Helper::get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}
	
		$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
		foreach( $t_related_custom_field_ids as $t_id ) {
			if( !custom_field_has_read_access_by_project_id( $t_id, $t_project_id ) ) {
				continue;
			}
	
			$t_def = custom_field_get_definition( $t_id );
			$t_columns[] = 'custom_' . $t_def['name'];
		}
	
		return $t_columns;
	}
	
	/**
	 * Checks if the specified column is an extended column.  Extended columns are native columns that are
	 * associated with the issue but are saved in mantis_bug_text_table.
	 * @param string $p_column The column name.
	 * @return boolean true for extended; false otherwise.
	 * @access public
	 */
	static function column_is_extended( $p_column ) {
		switch( $p_column ) {
			case 'description':
			case 'steps_to_reproduce':
			case 'additional_information':
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * Given a column name from the array of columns to be included in a view, this method checks if
	 * the column is a custom column and if so returns its name.  Note that for custom fields, then
	 * provided names will have the "custom_" prefix, where the returned ones won't have the prefix.
	 *
	 * @param string $p_column Column name.
	 * @return string The custom field column name or null if the specific column is not a custom field or invalid column.
	 * @access public
	 */
	static function column_get_custom_field_name( $p_column ) {
		if( strncmp( $p_column, 'custom_', 7 ) === 0 ) {
			return utf8_substr( $p_column, 7 );
		}
	
		return null;
	}
	
	/**
	 * Converts a string of comma separate column names to an array.
	 *
	 * @param string $p_string Comma separate column name (not case sensitive).
	 * @return array The array with all column names lower case.
	 * @access public
	 */
	static function string_to_array( $p_string ) {
		$t_string = utf8_strtolower( $p_string );
	
		$t_columns = explode( ',', $t_string );
		$t_count = count( $t_columns );
	
		for( $i = 0; $i < $t_count; $i++ ) {
			$t_columns[$i] = trim( $t_columns[$i] );
		}
	
		return $t_columns;
	}
	
	/**
	 * Gets the localized title for the specified column.  The column can be native or custom.
	 * The custom fields must contain the 'custom_' prefix.
	 *
	 * @param string $p_column The column name.
	 * @return string The column localized name.
	 * @access public
	 */
	static function column_get_title( $p_column ) {
		$t_custom_field = \Core\Columns::column_get_custom_field_name( $p_column );
		if( $t_custom_field !== null ) {
			$t_field_id = custom_field_get_id_from_name( $t_custom_field );
	
			if( $t_field_id === false ) {
				$t_custom_field = '@' . $t_custom_field . '@';
			} else {
				$t_def = custom_field_get_definition( $t_field_id );
				$t_custom_field = \Core\Lang::get_defaulted( $t_def['name'] );
			}
	
			return $t_custom_field;
		}
	
		$t_plugin_columns = \Core\Columns::get_plugin_columns();
		if( isset( $t_plugin_columns[$p_column] ) ) {
			$t_column_object = $t_plugin_columns[$p_column];
			return $t_column_object->title;
		}
	
		switch( $p_column ) {
			case 'attachment_count':
				return \Core\Lang::get( 'attachments' );
			case 'bugnotes_count':
				return '#';
			case 'category_id':
				return \Core\Lang::get( 'category' );
			case 'edit':
				return '';
			case 'handler_id':
				return \Core\Lang::get( 'assigned_to' );
			case 'last_updated':
				return \Core\Lang::get( 'updated' );
			case 'os_build':
				return \Core\Lang::get( 'os_version' );
			case 'project_id':
				return \Core\Lang::get( 'email_project' );
			case 'reporter_id':
				return \Core\Lang::get( 'reporter' );
			case 'selection':
				return '';
			case 'sponsorship_total':
				return \Core\Sponsorship::get_currency();
			case 'version':
				return \Core\Lang::get( 'product_version' );
			case 'view_state':
				return \Core\Lang::get( 'view_status' );
			default:
				return \Core\Lang::get_defaulted( $p_column );
		}
	}
	
	/**
	 * Checks an array of columns for duplicate or invalid fields.
	 *
	 * @param string $p_field_name          The logic name of the array being validated.  Used when triggering errors.
	 * @param array  $p_columns_to_validate The array of columns to validate.
	 * @param array  $p_columns_all         The list of all valid columns.
	 * @return boolean
	 * @access public
	 */
	static function ensure_valid( $p_field_name, array $p_columns_to_validate, array $p_columns_all ) {
		$t_columns_all_lower = array_map( 'utf8_strtolower', $p_columns_all );
	
		# Check for invalid fields
		foreach( $p_columns_to_validate as $t_column ) {
			if( !in_array( utf8_strtolower( $t_column ), $t_columns_all_lower ) ) {
				\Core\Error::parameters( $p_field_name, $t_column );
				trigger_error( ERROR_COLUMNS_INVALID, ERROR );
				return false;
			}
		}
	
		# Check for duplicate fields
		$t_columns_no_duplicates = array();
		foreach( $p_columns_to_validate as $t_column ) {
			$t_column_lower = utf8_strtolower( $t_column );
			if( in_array( $t_column, $t_columns_no_duplicates ) ) {
				\Core\Error::parameters( $p_field_name, $t_column );
				trigger_error( ERROR_COLUMNS_DUPLICATE, ERROR );
			} else {
				$t_columns_no_duplicates[] = $t_column_lower;
			}
		}
	
		return true;
	}
	
	/**
	 * Validates an array of column names and removes ones that are not valid.  The validation
	 * is not case sensitive.
	 *
	 * @param array $p_columns     The array of column names to be validated.
	 * @param array $p_columns_all The array of all valid column names.
	 * @return array The array of valid column names found in $p_columns.
	 * @access public
	 */
	static function remove_invalid( array $p_columns, array $p_columns_all ) {
		$t_columns_all_lower = array_values( array_map( 'utf8_strtolower', $p_columns_all ) );
		$t_columns = array();
	
		foreach( $p_columns as $t_column ) {
			if( in_array( utf8_strtolower( $t_column ), $t_columns_all_lower ) ) {
				$t_columns[] = $t_column;
			}
		}
	
		return $t_columns;
	}
	
	/**
	 * Print table header for selection column
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_selection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-selection"> &#160; </th>';
	}
	
	/**
	 * Print table header for edit column
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_edit( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-edit"> &#160; </th>';
	}
	
	/**
	 * Print table header for column ID
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-id">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'id' ), 'id', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'id' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column project id
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_project_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-project-id">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'email_project' ), 'project_id', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'project_id' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column reporter id
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_reporter_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-reporter">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'reporter' ), 'reporter_id', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'reporter_id' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column handler id
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_handler_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-assigned-to">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'assigned_to' ), 'handler_id', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'handler_id' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column priority
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_priority( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-priority">';
		# Use short label only when displaying icons
		$t_label = \Core\Lang::get( \Core\Config::mantis_get( 'show_priority_text' ) ? 'priority' : 'priority_abbreviation' );
		\Core\Print_Util::view_bug_sort_link( $t_label, 'priority', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'priority' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column reproducibility
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_reproducibility( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-reproducibility">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'reproducibility' ), 'reproducibility', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'reproducibility' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column projection
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_projection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-projection">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'projection' ), 'projection', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'projection' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column ETA
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_eta( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-eta">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'eta' ), 'eta', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'eta' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column resolution
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_resolution( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-resolution">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'resolution' ), 'resolution', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'resolution' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column fixed in version
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_fixed_in_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-fixed-in-version">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'fixed_in_version' ), 'fixed_in_version', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'fixed_in_version' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column target version
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_target_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-target-version">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'target_version' ), 'target_version', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'target_version' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column view state
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_view_state( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
		echo '<th class="column-view-state">';
		$t_view_state_text = \Core\Lang::get( 'view_status' );
		$t_view_state_icon = '<img src="' . $t_icon_path . 'protected.gif" alt="' . $t_view_state_text . '" title="' . $t_view_state_text . '" />';
		\Core\Print_Util::view_bug_sort_link( $t_view_state_icon, 'view_state', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'view_state' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column OS
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_os( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-os">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'os' ), 'os', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'os' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column OS Build
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_os_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-os-build">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'os_version' ), 'os_build', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'os_build' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column Build
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
			echo '<th class="column-build">';
			\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'build' ), 'build', $p_sort, $p_dir, $p_columns_target );
			\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'build' );
			echo '</th>';
		} else {
			echo \Core\Lang::get( 'build' );
		}
	}
	
	/**
	 * Print table header for column Platform
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_platform( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-platform">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'platform' ), 'platform', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'platform' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column version
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-version">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'product_version' ), 'version', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'version' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column date submitted
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_date_submitted( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-date-submitted">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'date_submitted' ), 'date_submitted', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'date_submitted' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column attachment count
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_attachment_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
		$t_attachment_count_text = \Core\Lang::get( 'attachment_count' );
		$t_attachment_count_icon = '<img src="' . $t_icon_path . 'attachment.png" alt="' . $t_attachment_count_text . '" title="' . $t_attachment_count_text . '" />';
		echo "\t" . '<th class="column-attachments">' . $t_attachment_count_icon . '</th>' . "\n";
	}
	
	/**
	 * Print table header for column category
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function  print_column_title_category_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-category">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'category' ), 'category_id', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'category_id' );
		echo '</th>';
	}
	
	/**
	 * Prints Category column header
	 * The actual column is 'category_id', this function is just here for backwards
	 * compatibility
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_category( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		trigger_error( ERROR_GENERIC, WARNING );
		\Core\Columns::print_column_title_category_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE );
	}
	
	/**
	 * Print table header for column sponsorship total
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_sponsorship_total( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo "\t<th class=\"column-sponsorship\">";
		\Core\Print_Util::view_bug_sort_link( \Core\Sponsorship::get_currency(), 'sponsorship_total', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'sponsorship_total' );
		echo "</th>\n";
	}
	
	/**
	 * Print table header for column severity
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_severity( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-severity">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'severity' ), 'severity', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'severity' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column status
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_status( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-status">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'status' ), 'status', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'status' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column last updated
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_last_updated( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-last-modified">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'updated' ), 'last_updated', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'last_updated' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column summary
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_summary( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-summary">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'summary' ), 'summary', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'summary' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column bugnotes count
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_bugnotes_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-bugnotes-count"> # </th>';
	}
	
	/**
	 * Print table header for column description
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_description( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-description">';
		echo \Core\Lang::get( 'description' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column steps to reproduce
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_steps_to_reproduce( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-steps-to-reproduce">';
		echo \Core\Lang::get( 'steps_to_reproduce' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column additional information
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_additional_information( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-additional-information">';
		echo \Core\Lang::get( 'additional_information' );
		echo '</th>';
	}
	
	/**
	 * Prints Due Date column header
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_due_date( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-due-date">';
		\Core\Print_Util::view_bug_sort_link( \Core\Lang::get( 'due_date' ), 'due_date', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'due_date' );
		echo '</th>';
	}
	
	/**
	 * Print table header for column overdue
	 *
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_title_overdue( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
		echo '<th class="column-overdue">';
		$t_overdue_text = \Core\Lang::get( 'overdue' );
		$t_overdue_icon = '<img src="' . $t_icon_path . 'overdue.png" alt="' . $t_overdue_text . '" title="' . $t_overdue_text . '" />';
		\Core\Print_Util::view_bug_sort_link( $t_overdue_icon, 'due_date', $p_sort, $p_dir, $p_columns_target );
		\Core\Icon::print_sort_icon( $p_dir, $p_sort, 'due_date' );
		echo '</th>';
	}
	
	/**
	 * Print table data for column selection
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_selection( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $g_checkboxes_exist;
	
		echo '<td class="column-selection">';
		if( \Core\Access::has_any_project( \Core\Config::mantis_get( 'report_bug_threshold', null, null, $p_bug->project_id ) ) ||
			# !TODO: check if any other projects actually exist for the bug to be moved to
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'move_bug_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			# !TODO: factor in $g_auto_set_status_to_assigned == ON
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'update_bug_assign_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'update_bug_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'delete_bug_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			# !TODO: check to see if the bug actually has any different selectable workflow states
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'update_bug_status_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'set_bug_sticky_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'change_view_status_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'add_bugnote_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'tag_attach_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ||
			\Core\Access::has_project_level( \Core\Config::mantis_get( 'roadmap_update_threshold', null, null, $p_bug->project_id ), $p_bug->project_id ) ) {
			$g_checkboxes_exist = true;
			printf( '<input type="checkbox" name="bug_arr[]" value="%d" />', $p_bug->id );
		} else {
			echo '&#160;';
		}
		echo '</td>';
	}
	
	/**
	 * Print column title for a specific custom column.
	 *
	 * @param string  $p_column         Active column.
	 * @param object  $p_column_object  Column object.
	 * @param string  $p_sort           Sort.
	 * @param string  $p_dir            Direction.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function  print_column_title_plugin( $p_column, $p_column_object, $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<th class="column-plugin">';
		if( $p_column_object->sortable ) {
			\Core\Print_Util::view_bug_sort_link( \Core\String::display_line( $p_column_object->title ), $p_column, $p_sort, $p_dir, $p_columns_target );
			\Core\Icon::print_sort_icon( $p_dir, $p_sort, $p_column );
		} else {
			echo \Core\String::display_line( $p_column_object->title );
		}
		echo '</th>';
	}
	
	/**
	 * Print custom column content for a specific bug.
	 * @param object  $p_column_object  Column object.
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function  print_column_plugin( $p_column_object, \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
			echo '<td class="column-plugin">';
			$p_column_object->display( $p_bug, $p_columns_target );
			echo '</td>';
		} else {
			$p_column_object->display( $p_bug, $p_columns_target );
		}
	}
	
	/**
	 * Print column content for column edit
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_edit( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
	
		echo '<td class="column-edit">';
	
		if( !\Core\Bug::is_readonly( $p_bug->id ) && \Core\Access::has_bug_level( \Core\Config::mantis_get( 'update_bug_threshold' ), $p_bug->id ) ) {
			echo '<a href="' . \Core\String::get_bug_update_url( $p_bug->id ) . '">';
			echo '<img width="16" height="16" src="' . $t_icon_path . 'update.png';
			echo '" alt="' . \Core\Lang::get( 'update_bug_button' ) . '"';
			echo ' title="' . \Core\Lang::get( 'update_bug_button' ) . '" /></a>';
		} else {
			echo '&#160;';
		}
	
		echo '</td>';
	}
	
	/**
	 * Print column content for column priority
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_priority( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-priority">';
		if( ON == \Core\Config::mantis_get( 'show_priority_text' ) ) {
			\Core\Print_Util::formatted_priority_string( $p_bug );
		} else {
			\Core\Icon::print_status_icon( $p_bug->priority );
		}
		echo '</td>';
	}
	
	/**
	 * Print column content for column id
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_id( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-id">';
		\Core\Print_Util::bug_link( $p_bug->id, false );
		echo '</td>';
	}
	
	/**
	 * Print column content for column sponsorship total
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_sponsorship_total( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo "\t<td class=\"right column-sponsorship\">";
	
		if( $p_bug->sponsorship_total > 0 ) {
			$t_sponsorship_amount = \Core\Sponsorship::format_amount( $p_bug->sponsorship_total );
			echo \Core\String::no_break( $t_sponsorship_amount );
		}
	
		echo "</td>\n";
	}
	
	/**
	 * Print column content for column bugnotes count
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_bugnotes_count( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $g_filter;
	
		# grab the bugnote count
		$t_bugnote_stats = \Core\Bug::get_bugnote_stats( $p_bug->id );
		if( null !== $t_bugnote_stats ) {
			$t_bugnote_count = $t_bugnote_stats['count'];
			$v_bugnote_updated = $t_bugnote_stats['last_modified'];
		} else {
			$t_bugnote_count = 0;
		}
	
		echo '<td class="column-bugnotes-count">';
		if( $t_bugnote_count > 0 ) {
			$t_show_in_bold = $v_bugnote_updated > strtotime( '-' . $g_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' );
			if( $t_show_in_bold ) {
				echo '<span class="bold">';
			}
			\Core\Print_Util::link( \Core\String::get_bug_view_url( $p_bug->id ) . '&nbn=' . $t_bugnote_count . '#bugnotes', $t_bugnote_count );
			if( $t_show_in_bold ) {
				echo '</span>';
			}
		} else {
			echo '&#160;';
		}
	
		echo '</td>';
	}
	
	/**
	 * Print column content for column attachment count
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_attachment_count( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
	
		# Check for attachments
		$t_attachment_count = 0;
		if( \Core\File::can_view_bug_attachments( $p_bug->id, null ) ) {
			$t_attachment_count = \Core\File::bug_attachment_count( $p_bug->id );
		}
	
		echo '<td class="column-attachments">';
	
		if( $t_attachment_count > 0 ) {
			$t_href = \Core\String::get_bug_view_url( $p_bug->id ) . '#attachments';
			$t_href_title = sprintf( \Core\Lang::get( 'view_attachments_for_issue' ), $t_attachment_count, $p_bug->id );
			echo '<a href="' . $t_href . '" title="' . $t_href_title . '">' . $t_attachment_count . '</a>';
		} else {
			echo ' &#160; ';
		}
	
		echo "</td>\n";
	}
	
	/**
	 * Print column content for column category id
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_category_id( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_sort, $t_dir;
	
		# grab the project name
		$t_project_name = \Core\Project::get_field( $p_bug->project_id, 'name' );
	
		echo '<td class="column-category">';
	
		# type project name if viewing 'all projects' or if issue is in a subproject
		if( ON == \Core\Config::mantis_get( 'show_bug_project_links' ) && \Core\Helper::get_current_project() != $p_bug->project_id ) {
			echo '<small class="project">[';
			\Core\Print_Util::view_bug_sort_link( \Core\String::display_line( $t_project_name ), 'project_id', $t_sort, $t_dir, $p_columns_target );
			echo ']</small><br />';
		}
	
		echo \Core\String::display_line( \Core\Category::full_name( $p_bug->category_id, false ) );
	
		echo '</td>';
	}
	
	/**
	 * Print column content for column severity
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_severity( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-severity">';
		\Core\Print_Util::formatted_severity_string( $p_bug );
		echo '</td>';
	}
	
	/**
	 * Print column content for column eta
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_eta( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-eta">', \Core\Helper::get_enum_element( 'eta', $p_bug->eta, \Core\Auth::get_current_user_id(), $p_bug->project_id ), '</td>';
	}
	
	/**
	 * Print column content for column projection
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_projection( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-projection">', \Core\Helper::get_enum_element( 'projection', $p_bug->projection, \Core\Auth::get_current_user_id(), $p_bug->project_id ), '</td>';
	}
	
	/**
	 * Print column content for column reproducibility
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_reproducibility( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-reproducibility">', \Core\Helper::get_enum_element( 'reproducibility', $p_bug->reproducibility, \Core\Auth::get_current_user_id(), $p_bug->project_id ), '</td>';
	}
	
	/**
	 * Print column content for column resolution
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_resolution( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-resolution">',
			\Core\Helper::get_enum_element( 'resolution', $p_bug->resolution, \Core\Auth::get_current_user_id(), $p_bug->project_id ),
			'</td>';
	}
	
	/**
	 * Print column content for column status
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_status( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-status">';
		printf( '<span class="issue-status" title="%s">%s</span>',
			\Core\Helper::get_enum_element( 'resolution', $p_bug->resolution, \Core\Auth::get_current_user_id(), $p_bug->project_id ),
			\Core\Helper::get_enum_element( 'status', $p_bug->status, \Core\Auth::get_current_user_id(), $p_bug->project_id )
		);
	
		# print username instead of status
		if( ( ON == \Core\Config::mantis_get( 'show_assigned_names' ) ) && ( $p_bug->handler_id > 0 ) && ( \Core\Access::has_project_level( \Core\Config::mantis_get( 'view_handler_threshold' ), $p_bug->project_id ) ) ) {
			printf( ' (%s)', \Core\Prepare::user_name( $p_bug->handler_id ) );
		}
		echo '</td>';
	}
	
	/**
	 * Print column content for column handler id
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_handler_id( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-assigned-to">';
	
		# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
		# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
		if( $p_bug->handler_id > 0 && ( \Core\Helper::get_current_project() != ALL_PROJECTS || \Core\Access::has_project_level( \Core\Config::mantis_get( 'view_handler_threshold' ), $p_bug->project_id ) ) ) {
			echo \Core\Prepare::user_name( $p_bug->handler_id );
		}
	
		echo '</td>';
	}
	
	/**
	 * Print column content for column reporter id
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_reporter_id( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-reporter">';
		echo \Core\Prepare::user_name( $p_bug->reporter_id );
		echo '</td>';
	}
	
	/**
	 * Print column content for column project id
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_project_id( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-project-id">';
		echo \Core\String::display_line( \Core\Project::get_name( $p_bug->project_id ) );
		echo '</td>';
	}
	
	/**
	 * Print column content for column last updated
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_last_updated( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $g_filter;
	
		$t_last_updated = \Core\String::display_line( date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->last_updated ) );
	
		echo '<td class="column-last-modified">';
		if( $p_bug->last_updated > strtotime( '-' . $g_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' ) ) {
			printf( '<span class="bold">%s</span>', $t_last_updated );
		} else {
			echo $t_last_updated;
		}
		echo '</td>';
	}
	
	/**
	 * Print column content for column date submitted
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_date_submitted( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_date_submitted = \Core\String::display_line( date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->date_submitted ) );
	
		echo '<td class="column-date-submitted">', $t_date_submitted, '</td>';
	}
	
	/**
	 * Print column content for column summary
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_summary( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE ) {
			$t_summary = \Core\String::attribute( $p_bug->summary );
		} else {
			$t_summary = \Core\String::display_line_links( $p_bug->summary );
		}
	
		echo '<td class="column-summary">' . $t_summary . '</td>';
	}
	
	/**
	 * Print column content for column description
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_description( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_description = \Core\String::display_links( $p_bug->description );
	
		echo '<td class="column-description">', $t_description, '</td>';
	}
	
	/**
	 * Print column content for column steps to reproduce
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_steps_to_reproduce( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_steps_to_reproduce = \Core\String::display_links( $p_bug->steps_to_reproduce );
	
		echo '<td class="column-steps-to-reproduce">', $t_steps_to_reproduce, '</td>';
	}
	
	/**
	 * Print column content for column additional information
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_additional_information( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_additional_information = \Core\String::display_links( $p_bug->additional_information );
	
		echo '<td class="column-additional-information">', $t_additional_information, '</td>';
	}
	
	/**
	 * Print column content for column target version
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_target_version( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="column-target-version">';
	
		# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
		# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
		if( \Core\Helper::get_current_project() != ALL_PROJECTS || \Core\Access::has_project_level( \Core\Config::mantis_get( 'roadmap_view_threshold' ), $p_bug->project_id ) ) {
			echo \Core\String::display_line( $p_bug->target_version );
		}
	
		echo '</td>';
	}
	
	/**
	 * Print column content for view state column
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_view_state( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
	
		echo '<td class="column-view-state">';
	
		if( VS_PRIVATE == $p_bug->view_state ) {
			$t_view_state_text = \Core\Lang::get( 'private' );
			echo '<img src="' . $t_icon_path . 'protected.gif" alt="' . $t_view_state_text . '" title="' . $t_view_state_text . '" />';
		} else {
			echo '&#160;';
		}
	
		echo '</td>';
	}
	
	/**
	 * Print column content for column due date
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_due_date( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_overdue = '';
	
		if( !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'due_date_view_threshold' ), $p_bug->id ) ||
			\Core\Date::is_null( $p_bug->due_date )
		) {
			$t_value = '&#160;';
		} else {
			if( \Core\Bug::is_overdue( $p_bug->id ) ) {
				$t_overdue = ' overdue';
			}
			$t_value = \Core\String::display_line( date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->due_date ) );
		}
	
		printf( '<td class="column-due-date%s">%s</td>', $t_overdue, $t_value );
	}
	
	/**
	 * Print column content for column overdue
	 *
	 * @param \Core\BugData $p_bug            \Core\BugData object.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 * @access public
	 */
	static function print_column_overdue( \Core\BugData $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;
	
		echo '<td class="column-overdue">';
	
		if( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'due_date_view_threshold' ), $p_bug->id ) &&
			!\Core\Date::is_null( $p_bug->due_date ) &&
			\Core\Bug::is_overdue( $p_bug->id ) ) {
			$t_overdue_text = \Core\Lang::get( 'overdue' );
			$t_overdue_text_hover = sprintf( \Core\Lang::get( 'overdue_since' ), date( \Core\Config::mantis_get( 'short_date_format' ), $p_bug->due_date ) );
			echo '<img src="' . $t_icon_path . 'overdue.png" alt="' . $t_overdue_text . '" title="' . \Core\String::display_line( $t_overdue_text_hover ) . '" />';
		} else {
			echo '&#160;';
		}
	
		echo '</td>';
	}
	
}