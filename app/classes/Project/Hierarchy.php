<?php
namespace Core\Project;

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
 * Project Hierarchy API
 *
 * @package CoreAPI
 * @subpackage ProjectHierarchyAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses database_api.php
 */


class Hierarchy
{

	/**
	 * Add project to project hierarchy
	 * @param integer $p_child_id       Child project identifier.
	 * @param integer $p_parent_id      Parent project identifier.
	 * @param boolean $p_inherit_parent Whether or not the child project inherits from the parent project.
	 * @return void
	 */
	static function add( $p_child_id, $p_parent_id, $p_inherit_parent = true ) {
		if( in_array( $p_parent_id, \Core\Project\Hierarchy::get_all_subprojects( $p_child_id ) ) ) {
			trigger_error( ERROR_PROJECT_RECURSIVE_HIERARCHY, ERROR );
		}
	
		$t_query = 'INSERT INTO {project_hierarchy}
			                ( child_id, parent_id, inherit_parent )
							VALUES
							( ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ' )';
	
		\Core\Database::query( $t_query, array( $p_child_id, $p_parent_id, $p_inherit_parent ) );
	}
	
	/**
	 * Update project hierarchy
	 * @param integer $p_child_id       Child project identifier.
	 * @param integer $p_parent_id      Parent project identifier.
	 * @param boolean $p_inherit_parent Whether or not the child project inherits from the parent project.
	 * @return void
	 */
	static function update( $p_child_id, $p_parent_id, $p_inherit_parent = true ) {
		$t_query = 'UPDATE {project_hierarchy}
						SET inherit_parent=' . \Core\Database::param() . '
						WHERE child_id=' . \Core\Database::param() . '
							AND parent_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_inherit_parent, $p_child_id, $p_parent_id ) );
	}
	
	/**
	 * Remove project from project hierarchy
	 * @param integer $p_child_id  Child project identifier.
	 * @param integer $p_parent_id Parent project identifier.
	 * @return void
	 */
	static function remove( $p_child_id, $p_parent_id ) {
		$t_query = 'DELETE FROM {project_hierarchy} WHERE child_id = ' . \Core\Database::param() . '
							AND parent_id = ' . \Core\Database::param();
	
		\Core\Database::query( $t_query, array( $p_child_id, $p_parent_id ) );
	}
	
	/**
	 * Remove any project hierarchy entries relating to project_id
	 * @param integer $p_project_id Project identifier.
	 * @return void
	 */
	static function remove_all( $p_project_id ) {
		$t_query = 'DELETE FROM {project_hierarchy} WHERE child_id = ' . \Core\Database::param() . '
							  OR parent_id = ' . \Core\Database::param();
	
		\Core\Database::query( $t_query, array( $p_project_id, $p_project_id ) );
	}
	
	/**
	 * Returns true if project is at top of hierarchy
	 * @param integer $p_project_id    Project identifier.
	 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
	 * @return boolean
	 */
	static function is_toplevel( $p_project_id, $p_show_disabled = false ) {
		global $g_cache_project_hierarchy;
	
		\Core\Project\Hierarchy::cache( $p_show_disabled );
	
		if( isset( $g_cache_project_hierarchy[ALL_PROJECTS] ) ) {
			return in_array( $p_project_id, $g_cache_project_hierarchy[ALL_PROJECTS] );
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the id of the project's parent (0 if top-level or not found)
	 * @param integer $p_project_id    Project Identifier.
	 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
	 * @return integer
	 */
	static function get_parent( $p_project_id, $p_show_disabled = false ) {
		global $g_cache_project_hierarchy;
	
		\Core\Project\Hierarchy::cache( $p_show_disabled );
	
		if( ALL_PROJECTS == $p_project_id ) {
			return 0;
		}
	
		foreach( $g_cache_project_hierarchy as $t_key => $t_value ) {
			if( in_array( $p_project_id, $g_cache_project_hierarchy[$t_key] ) ) {
				return $t_key;
			}
		}
	
		return 0;
	}
	
	/**
	 * Cache project hierarchy
	 * @param boolean $p_show_disabled Whether or not to cache projects which are disabled.
	 * @return void
	 */
	static function cache( $p_show_disabled = false ) {
		global $g_cache_project_hierarchy, $g_cache_project_inheritance;
		global $g_cache_show_disabled;
	
		if( !is_null( $g_cache_project_hierarchy ) && ( $g_cache_show_disabled == $p_show_disabled ) ) {
			return;
		}
		$g_cache_show_disabled = $p_show_disabled;
	
		$t_enabled_clause = $p_show_disabled ? '1=1' : 'p.enabled = ' . \Core\Database::param();
	
		$t_query = 'SELECT DISTINCT p.id, ph.parent_id, p.name, p.inherit_global, ph.inherit_parent
					  FROM {project} p
					  LEFT JOIN {project_hierarchy} ph
					    ON ph.child_id = p.id
					  WHERE ' . $t_enabled_clause . '
					  ORDER BY p.name';
	
		$t_result = \Core\Database::query( $t_query, ( $p_show_disabled ? array() : array( true ) ) );
	
		$g_cache_project_hierarchy = array();
		$g_cache_project_inheritance = array();
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			if( null === $t_row['parent_id'] ) {
				$t_row['parent_id'] = ALL_PROJECTS;
			}
	
			if( isset( $g_cache_project_hierarchy[(int)$t_row['parent_id']] ) ) {
				$g_cache_project_hierarchy[(int)$t_row['parent_id']][] = (int)$t_row['id'];
			} else {
				$g_cache_project_hierarchy[(int)$t_row['parent_id']] = array(
					(int)$t_row['id'],
				);
			}
	
			if( !isset( $g_cache_project_inheritance[(int)$t_row['id']] ) ) {
				$g_cache_project_inheritance[(int)$t_row['id']] = array();
			}
	
			if( $t_row['inherit_global'] && !isset( $g_cache_project_inheritance[(int)$t_row['id']][ALL_PROJECTS] ) ) {
				$g_cache_project_inheritance[(int)$t_row['id']][] = ALL_PROJECTS;
			}
	
			if( $t_row['inherit_parent'] && !isset( $g_cache_project_inheritance[(int)$t_row['id']][(int)$t_row['parent_id']] ) ) {
				$g_cache_project_inheritance[(int)$t_row['id']][] = (int)$t_row['parent_id'];
			}
		}
	}
	
	/**
	 * Returns true if the child project inherits categories from the parent.
	 * @param integer $p_child_id      Child project identifier.
	 * @param integer $p_parent_id     Parent project identifier.
	 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
	 * @return boolean
	 */
	static function inherit_parent( $p_child_id, $p_parent_id, $p_show_disabled = false ) {
		global $g_cache_project_inheritance;
	
		\Core\Project\Hierarchy::cache( $p_show_disabled );
	
		return in_array( $p_parent_id, $g_cache_project_inheritance[$p_child_id] );
	}
	
	/**
	 * Generate an array of project's the given project inherits from,
	 * including the original project in the result.
	 * @param integer $p_project_id    Project identifier.
	 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
	 * @return array
	 */
	static function inheritance( $p_project_id, $p_show_disabled = false ) {
		global $g_cache_project_inheritance;
	
		\Core\Project\Hierarchy::cache( $p_show_disabled );
	
		$t_project_ids = array( (int)$p_project_id, );
		$t_lookup_ids = array( (int)$p_project_id, );
	
		while( count( $t_lookup_ids ) > 0 ) {
			$t_project_id = array_shift( $t_lookup_ids );
	
			if( !isset( $g_cache_project_inheritance[$t_project_id] ) ) {
				continue;
			}
	
			foreach( $g_cache_project_inheritance[$t_project_id] as $t_parent_id ) {
				if( !in_array( $t_parent_id, $t_project_ids ) ) {
					$t_project_ids[] = $t_parent_id;
	
					if( !in_array( $t_lookup_ids, $t_project_ids ) ) {
						$t_lookup_ids[] = $t_parent_id;
					}
				}
			}
		}
	
		return $t_project_ids;
	}
	
	/**
	 * Get subprojects for a project
	 * @param integer $p_project_id    Project identifier.
	 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
	 * @return array
	 */
	static function get_subprojects( $p_project_id, $p_show_disabled = false ) {
		global $g_cache_project_hierarchy;
	
		\Core\Project\Hierarchy::cache( $p_show_disabled );
	
		if( isset( $g_cache_project_hierarchy[$p_project_id] ) ) {
			return $g_cache_project_hierarchy[$p_project_id];
		} else {
			return array();
		}
	}
	
	/**
	 * Get complete subproject hierarchy for a project
	 * @param integer $p_project_id    Project identifier.
	 * @param boolean $p_show_disabled Whether or not to consider projects which are disabled.
	 * @return array
	 */
	static function get_all_subprojects( $p_project_id, $p_show_disabled = false ) {
		$t_todo = \Core\Project\Hierarchy::get_subprojects( $p_project_id, $p_show_disabled );
		$t_subprojects = array();
	
		while( $t_todo ) {
			$t_elem = array_shift( $t_todo );
			if( !in_array( $t_elem, $t_subprojects ) ) {
				array_push( $t_subprojects, $t_elem );
				$t_todo = array_merge( $t_todo, \Core\Project\Hierarchy::get_subprojects( $t_elem, $p_show_disabled ) );
			}
		}
	
		return $t_subprojects;
	}

}