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
 * Version API
 *
 * @package CoreAPI
 * @subpackage VersionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 */



class Version
{
	
	/**
	 * Cache a version row if necessary and return the cached copy
	 * If the second parameter is true (default), trigger an error
	 * if the version can't be found.  If the second parameter is
	 * false, return false if the version can't be found.
	 * @param integer $p_version_id     A version identifier to look up.
	 * @param boolean $p_trigger_errors Whether to generate errors if not found.
	 * @return array
	 */
	static function cache_row( $p_version_id, $p_trigger_errors = true ) {
		global $g_cache_versions;
	
		$c_version_id = (int)$p_version_id;
	
		if( isset( $g_cache_versions[$c_version_id] ) ) {
			return $g_cache_versions[$c_version_id];
		}
	
		$t_query = 'SELECT * FROM {project_version} WHERE id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $c_version_id ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			$g_cache_versions[$c_version_id] = false;
	
			if( $p_trigger_errors ) {
				\Core\Error::parameters( $p_version_id );
				trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}
	
		$g_cache_versions[$c_version_id] = $t_row;
	
		return $t_row;
	}
	
	/**
	 * Check whether the version exists
	 * $p_project_id : null will use the current project, otherwise the specified project
	 * Returns true if the version exists, false otherwise
	 * @param integer $p_version_id A version identifier.
	 * @return boolean
	 */
	static function exists( $p_version_id ) {
		return \Core\Version::cache_row( $p_version_id, false ) !== false;
	}
	
	/**
	 * Check whether the version name is unique
	 * Returns true if the name is unique, false otherwise
	 * @param string  $p_version    A version string to check.
	 * @param integer $p_project_id A valid Project identifier.
	 * @return boolean
	 */
	static function is_unique( $p_version, $p_project_id = null ) {
		return \Core\Version::get_id( $p_version, $p_project_id ) === false;
	}
	
	/**
	 * Check whether the version exists
	 * Trigger an error if it does not
	 * @param integer $p_version_id A version identifier.
	 * @return void
	 */
	static function ensure_exists( $p_version_id ) {
		if( !\Core\Version::exists( $p_version_id ) ) {
			\Core\Error::parameters( $p_version_id );
			trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
		}
	}
	
	/**
	 * Check whether the version is unique within a project
	 * Trigger an error if it is not
	 * @param string  $p_version    A version string.
	 * @param integer $p_project_id A valid Project identifier.
	 * @return void
	 */
	static function ensure_unique( $p_version, $p_project_id = null ) {
		if( !\Core\Version::is_unique( $p_version, $p_project_id ) ) {
			trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
		}
	}
	
	/**
	 * Add a version to the project
	 * @param integer $p_project_id  A valid project id.
	 * @param string  $p_version     Name of a version to add.
	 * @param integer $p_released    Release status of the version.
	 * @param string  $p_description Description of the version.
	 * @param integer $p_date_order  Date Order.
	 * @param boolean $p_obsolete    Obsolete status of the version.
	 * @return integer
	 */
	static function add( $p_project_id, $p_version, $p_released = VERSION_FUTURE, $p_description = '', $p_date_order = null, $p_obsolete = false ) {
		$c_project_id = (int)$p_project_id ;
		$c_released = (bool)$p_released;
		$c_obsolete = (bool)$p_obsolete;
	
		if( null === $p_date_order ) {
			$c_date_order = \Core\Database::now();
		} else {
			$c_date_order = $p_date_order;
		}
	
		\Core\Version::ensure_unique( $p_version, $p_project_id );
	
		$t_query = 'INSERT INTO {project_version}
						( project_id, version, date_order, description, released, obsolete )
					  VALUES
						(' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ' )';
		\Core\Database::query( $t_query, array( $c_project_id, $p_version, $c_date_order, $p_description, $c_released, $c_obsolete ) );
	
		$t_version_id = \Core\Database::insert_id( \Core\Database::get_table( 'project_version' ) );
	
		\Core\Event::signal( 'EVENT_MANAGE_VERSION_CREATE', array( $t_version_id ) );
	
		return $t_version_id;
	}
	
	/**
	 * Update the definition of a version
	 * @param VersionData $p_version_info A version structure to update.
	 * @return void
	 */
	static function update( \Core\VersionData $p_version_info ) {
		\Core\Version::ensure_exists( $p_version_info->id );
	
		$t_old_version_name = \Core\Version::get_field( $p_version_info->id, 'version' );
	
		# check for duplicates
		if( ( utf8_strtolower( $t_old_version_name ) != utf8_strtolower( $p_version_info->version ) ) && !\Core\Version::is_unique( $p_version_info->version, $p_version_info->project_id ) ) {
			trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
		}
	
		$c_version_id = (int)$p_version_info->id;
		$c_version_name = $p_version_info->version;
		$c_old_version_name = $t_old_version_name;
		$c_description = $p_version_info->description;
		$c_released = (bool)$p_version_info->released;
		$c_obsolete = (bool)$p_version_info->obsolete;
		$c_date_order = $p_version_info->date_order;
		$c_project_id = (int)$p_version_info->project_id;
	
		$t_query = 'UPDATE {project_version}
					  SET version=' . \Core\Database::param() . ',
						description=' . \Core\Database::param() . ',
						released=' . \Core\Database::param() . ',
						date_order=' . \Core\Database::param() . ',
						obsolete=' . \Core\Database::param() . '
					  WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_version_name, $c_description, $c_released, $c_date_order, $c_obsolete, $c_version_id ) );
	
		if( $c_version_name != $c_old_version_name ) {
			$t_project_list = array( $c_project_id );
			if( \Core\Config::mantis_get( 'subprojects_inherit_versions' ) ) {
				$t_project_list = array_merge( $t_project_list, \Core\Project\Hierarchy::get_all_subprojects( $c_project_id, true ) );
			}
			$t_project_list = implode( ',', $t_project_list );
	
			$t_query = 'UPDATE {bug} SET version=' . \Core\Database::param() .
					 ' WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( version=' . \Core\Database::param() . ')';
			\Core\Database::query( $t_query, array( $c_version_name, $c_old_version_name ) );
	
			$t_query = 'UPDATE {bug} SET fixed_in_version=' . \Core\Database::param() . '
						  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( fixed_in_version=' . \Core\Database::param() . ')';
			\Core\Database::query( $t_query, array( $c_version_name, $c_old_version_name ) );
	
			$t_query = 'UPDATE {bug} SET target_version=' . \Core\Database::param() . '
						  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( target_version=' . \Core\Database::param() . ')';
			\Core\Database::query( $t_query, array( $c_version_name, $c_old_version_name ) );
	
			$t_query = 'UPDATE {bug_history}
				SET old_value='.\Core\Database::param().'
				WHERE field_name IN (\'version\',\'fixed_in_version\',\'target_version\')
					AND old_value='.\Core\Database::param().'
					AND bug_id IN (SELECT id FROM {bug} WHERE project_id IN ( ' . $t_project_list . ' ))';
			\Core\Database::query( $t_query, array( $c_version_name, $c_old_version_name ) );
	
			$t_query = 'UPDATE {bug_history}
				SET new_value='.\Core\Database::param().'
				WHERE field_name IN (\'version\',\'fixed_in_version\',\'target_version\')
					AND new_value='.\Core\Database::param().'
					AND bug_id IN (SELECT id FROM {bug} WHERE project_id IN ( ' . $t_project_list . ' ))';
			\Core\Database::query( $t_query, array( $c_version_name, $c_old_version_name ) );
	
			# @todo We should consider using ids instead of names for foreign keys.  The main advantage of using the names are:
			#		- for history the version history entries will still be valid even if the version is deleted in the future. --  we can ban deleting referenced versions.
			# 		- when an issue is copied or moved from one project to another, we can keep the last version with the issue even if it doesn't exist in the new project.  Also previous history entries remain valid.
			# @todo We probably need to update the saved filters too?
		}
	}
	
	/**
	 * Remove a version from the project
	 * @param integer $p_version_id  A valid version identifier.
	 * @param string  $p_new_version A version string to update issues using the old version with.
	 * @return void
	 */
	static function remove( $p_version_id, $p_new_version = '' ) {
		\Core\Version::ensure_exists( $p_version_id );
	
		\Core\Event::signal( 'EVENT_MANAGE_VERSION_DELETE', array( $p_version_id, $p_new_version ) );
	
		$t_old_version = \Core\Version::get_field( $p_version_id, 'version' );
		$t_project_id = \Core\Version::get_field( $p_version_id, 'project_id' );
	
		$t_query = 'DELETE FROM {project_version} WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_version_id ) );
	
		$t_project_list = array( $t_project_id );
		if( \Core\Config::mantis_get( 'subprojects_inherit_versions' ) ) {
			$t_project_list = array_merge( $t_project_list, \Core\Project\Hierarchy::get_all_subprojects( $t_project_id, true ) );
		}
		$t_project_list = implode( ',', $t_project_list );
	
		$t_query = 'UPDATE {bug} SET version=' . \Core\Database::param() . '
					  WHERE project_id IN ( ' . $t_project_list . ' ) AND version=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_new_version, $t_old_version ) );
	
		$t_query = 'UPDATE {bug} SET fixed_in_version=' . \Core\Database::param() . '
					  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( fixed_in_version=' . \Core\Database::param() . ')';
		\Core\Database::query( $t_query, array( $p_new_version, $t_old_version ) );
	
		$t_query = 'UPDATE {bug} SET target_version=' . \Core\Database::param() . '
					  WHERE ( project_id IN ( ' . $t_project_list . ' ) ) AND ( target_version=' . \Core\Database::param() . ')';
		\Core\Database::query( $t_query, array( $p_new_version, $t_old_version ) );
	}
	
	/**
	 * Remove all versions associated with a project
	 * @param integer $p_project_id A project identifier.
	 * @return boolean
	 */
	static function remove_all( $p_project_id ) {
		$c_project_id = (int)$p_project_id;
	
		# remove all references to versions from version, fixed in version and target version.
		$t_query = 'UPDATE {bug}
					  SET version=\'\', fixed_in_version=\'\', target_version=\'\'
					  WHERE project_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_project_id ) );
	
		# remove the actual versions associated with the project.
		$t_query = 'DELETE FROM {project_version} WHERE project_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_project_id ) );
	
		return true;
	}
	
	/**
	 * Cache version information for an array of project id's
	 * @param array $p_project_id_array An array of project identifiers.
	 * @return void
	 */
	static function cache_array_rows( array $p_project_id_array ) {
		global $g_cache_versions, $g_cache_versions_project;
	
		$c_project_id_array = array();
	
		foreach( $p_project_id_array as $t_project_id ) {
			if( !isset( $g_cache_versions_project[(int)$t_project_id] ) ) {
				$c_project_id_array[] = (int)$t_project_id;
				$g_cache_versions_project[(int)$t_project_id] = array();
			}
		}
	
		if( empty( $c_project_id_array ) ) {
			return;
		}
	
		$t_query = 'SELECT * FROM {project_version}
					  WHERE project_id IN (' . implode( ',', $c_project_id_array ) . ')
					  ORDER BY date_order DESC';
		$t_result = \Core\Database::query( $t_query );
	
		$t_rows = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$g_cache_versions[(int)$t_row['id']] = $t_row;
	
			$t_rows[(int)$t_row['project_id']][] = $t_row['id'];
		}
	
		foreach( $t_rows as $t_project_id => $t_row ) {
			$g_cache_versions_project[(int)$t_project_id] = $t_row;
		}
	}
	
	/**
	 * Return all versions for the specified project
	 * @param integer $p_project_id A valid project id.
	 * @param integer $p_released   Whether to include released versions.
	 * @param boolean $p_obsolete   Whether to include obsolete versions.
	 * @param boolean $p_inherit    Whether to inherit versions from other projects.
	 * @return array Array of version rows (in array format)
	 */
	static function get_all_rows( $p_project_id, $p_released = null, $p_obsolete = false, $p_inherit = null ) {
		global $g_cache_versions, $g_cache_versions_project;
	
		if( $p_inherit === null ) {
			$t_inherit = ( ON == \Core\Config::mantis_get( 'subprojects_inherit_versions' ) );
		} else {
			$t_inherit = $p_inherit;
		}
	
		if( $t_inherit ) {
			$t_project_ids = \Core\Project\Hierarchy::inheritance( $p_project_id );
		} else {
			$t_project_ids[] = $p_project_id;
		}
	
		$t_is_cached = true;
		foreach( $t_project_ids as $t_project_id ) {
			if( !isset( $g_cache_versions_project[$t_project_id] ) ) {
				$t_is_cached = false;
				break;
			}
		}
		if( $t_is_cached ) {
			$t_versions = array();
			foreach( $t_project_ids as $t_project_id ) {
				if( !empty( $g_cache_versions_project[$t_project_id]) ) {
					foreach( $g_cache_versions_project[$t_project_id] as $t_id ) {
						$t_versions[] = \Core\Version::cache_row( $t_id );
					}
				}
			}
			return $t_versions;
		}
	
		$t_project_where = \Core\Version::get_project_where_clause( $p_project_id, $p_inherit );
	
		$t_query = 'SELECT * FROM {project_version} WHERE ' . $t_project_where;
	
		$t_query_params = array();
	
		if( $p_released !== null ) {
			$t_query .= ' AND released = ' . \Core\Database::param();
			$t_query_params[] = (bool)$p_released;
		}
	
		if( $p_obsolete !== null ) {
			$t_query .= ' AND obsolete = ' . \Core\Database::param();
			$t_query_params[] = (bool)$p_obsolete;
		}
	
		$t_query .= ' ORDER BY date_order DESC';
	
		$t_result = \Core\Database::query( $t_query, $t_query_params );
		$t_rows = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$g_cache_versions[(int)$t_row['id']] = $t_row;
	
			$t_rows[] = $t_row;
		}
		return $t_rows;
	}
	
	/**
	 * Return all versions for the specified project, including sub-projects
	 * @param integer $p_project_id A valid project identifier.
	 * @param integer $p_released   Released status.
	 * @param boolean $p_obsolete   Obsolete status.
	 * @return array
	 */
	static function get_all_rows_with_subs( $p_project_id, $p_released = null, $p_obsolete = false ) {
		$t_project_where = \Core\Helper::project_specific_where( $p_project_id );
	
		$t_query_params = array();
	
		if( $p_released === null ) {
			$t_released_where = '';
		} else {
			$c_released = (bool)$p_released;
			$t_released_where = 'AND ( released = ' . \Core\Database::param() . ' )';
			$t_query_params[] = $c_released;
		}
	
		if( $p_obsolete === null ) {
			$t_obsolete_where = '';
		} else {
			$t_obsolete_where = 'AND ( obsolete = ' . \Core\Database::param() . ' )';
			$t_query_params[] = (bool)$p_obsolete;
		}
	
		$t_query = 'SELECT * FROM {project_version}
					  WHERE ' . $t_project_where . ' ' . $t_released_where . ' ' . $t_obsolete_where . '
					  ORDER BY date_order DESC';
		$t_result = \Core\Database::query( $t_query, $t_query_params );
		$t_rows = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}
		return $t_rows;
	}
	
	/**
	 * Get the version_id, given the project_id and $p_version_id
	 * returns false if not found, otherwise returns the id.
	 * @param string  $p_version    A version string to look up.
	 * @param integer $p_project_id A valid project identifier.
	 * @param mixed   $p_inherit    True to look for version in parent projects, false not to, null to use default configuration.
	 * @return integer
	 */
	static function get_id( $p_version, $p_project_id = null, $p_inherit = null ) {
		global $g_cache_versions;
	
		if( $p_project_id === null ) {
			$c_project_id = \Core\Helper::get_current_project();
		} else {
			$c_project_id = (int)$p_project_id;
		}
	
		foreach( $g_cache_versions as $t_version ) {
			if( ( $t_version['version'] === $p_version ) && ( $t_version['project_id'] == $c_project_id ) ) {
				return $t_version['id'];
			}
		}
	
		$t_project_where = \Core\Version::get_project_where_clause( $c_project_id, $p_inherit );
	
		$t_query = 'SELECT id FROM {project_version} WHERE ' . $t_project_where . ' AND version=' . \Core\Database::param();
	
		$t_result = \Core\Database::query( $t_query, array( $p_version ) );
	
		if( $t_row = \Core\Database::result( $t_result ) ) {
			return $t_row;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the specified field name for the specified version id.
	 * triggers an error if version not found, otherwise returns the field value.
	 * @param integer $p_version_id A valid version identifier.
	 * @param string  $p_field_name A valid field name to lookup.
	 * @return string
	 */
	static function get_field( $p_version_id, $p_field_name ) {
		$t_row = \Core\Version::cache_row( $p_version_id );
	
		if( isset( $t_row[$p_field_name] ) ) {
			switch( $p_field_name ) {
				case 'project_id':
					return (int)$t_row[$p_field_name];
				default:
					return $t_row[$p_field_name];
			}
		} else {
			\Core\Error::parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	
	/**
	 * Gets the full name of a version.  This may include the project name as a prefix (e.g. '[MantisBT] 2.0.0')
	 *
	 * @param integer $p_version_id         The version id.
	 * @param boolean $p_show_project       Whether to include the project or not, null means include the project if different from current.
	 * @param integer $p_current_project_id The current project id or null to use the cookie.
	 * @return string The full name of the version.
	 */
	static function full_name( $p_version_id, $p_show_project = null, $p_current_project_id = null ) {
		if( 0 == $p_version_id ) {
			# No Version
			return '';
		} else {
			$t_row = \Core\Version::cache_row( $p_version_id );
			$t_project_id = $t_row['project_id'];
	
			$t_current_project_id = is_null( $p_current_project_id ) ? \Core\Helper::get_current_project() : $p_current_project_id;
	
			if( $p_show_project === null ) {
				$t_show_project = $t_project_id != $t_current_project_id;
			} else {
				$t_show_project = $p_show_project;
			}
	
			if( $t_show_project && $t_project_id != $t_current_project_id ) {
				return '[' . \Core\Project::get_name( $t_project_id ) . '] ' . $t_row['version'];
			}
	
			return $t_row['version'];
		}
	}
	
	/**
	 * get information about a version given its id
	 * @param integer $p_version_id A valid version identifier.
	 * @return VersionData
	 */
	static function get( $p_version_id ) {
		static $s_vars;
	
		$t_row = \Core\Version::cache_row( $p_version_id );
	
		if( $s_vars == null ) {
			$t_reflection = new \ReflectionClass( '\\Core\\VersionData' );
			$s_vars = $t_reflection->getDefaultProperties();
		}
	
		$t_version_data = new \Core\VersionData;
		$t_row_keys = array_keys( $t_row );
	
		# Check each variable in the class
		foreach( $s_vars as $t_var => $t_val ) {
			# If we got a field from the DB with the same name
			if( in_array( $t_var, $t_row_keys, true ) ) {
				# Store that value in the object
				$t_version_data->$t_var = $t_row[$t_var];
			}
		}
	
		return $t_version_data;
	}
	
	/**
	 * Return a copy of the version structure with all the variables prepared for database insertion
	 * @param VersionData $p_version_info A version data structure.
	 * @return VersionData
	 */
	static function prepare_db( \Core\VersionData $p_version_info ) {
		$p_version_info->id = (int)$p_version_info->id;
		$p_version_info->project_id = (int)$p_version_info->project_id;
		$p_version_info->released = (bool)$p_version_info->released;
		$p_version_info->obsolete = (bool)$p_version_info->obsolete;
	
		return $p_version_info;
	}
	
	/**
	 * Checks whether the product version should be shown
	 * (i.e. report, update, view, print).
	 * @param integer $p_project_id The project id.
	 * @return boolean true: show, false: otherwise.
	 */
	static function should_show_product_version( $p_project_id ) {
		return ( ON == \Core\Config::mantis_get( 'show_product_version', null, null, $p_project_id ) )
			|| ( ( AUTO == \Core\Config::mantis_get( 'show_product_version', null, null, $p_project_id ) )
					&& ( count( \Core\Version::get_all_rows( $p_project_id ) ) > 0 ) );
	}
	
	/**
	 * Gets the where clause to use for retrieving versions.
	 *
	 * @param integer $p_project_id The project id to use.
	 * @param boolean $p_inherit    Include versions from parent projects? true: yes, false: no, null: use default configuration.
	 * @return string The where clause not including WHERE.
	 */
	static function get_project_where_clause( $p_project_id, $p_inherit ) {
		if( $p_project_id == ALL_PROJECTS ) {
			$t_inherit = false;
		} else {
			if( $p_inherit === null ) {
				$t_inherit = ( ON == \Core\Config::mantis_get( 'subprojects_inherit_versions' ) );
			} else {
				$t_inherit = $p_inherit;
			}
		}
	
		$c_project_id = (int)$p_project_id;
	
		if( $t_inherit ) {
			$t_project_ids = \Core\Project\Hierarchy::inheritance( $p_project_id );
	
			$t_project_where = ' project_id IN ( ' . implode( ', ', $t_project_ids ) . ' ) ';
		} else {
			$t_project_where = ' project_id=' . $c_project_id . ' ';
		}
	
		return $t_project_where;
	}


}