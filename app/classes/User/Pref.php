<?php
namespace Core\User;


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
 * User Preferences API
 *
 * @package CoreAPI
 * @subpackage UserPreferencesAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses lang_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */



class Pref
{

	/**
	 * Cache a user preferences row if necessary and return the cached copy
	 *  If the third parameter is true (default), trigger an error
	 *  if the preferences can't be found.  If the second parameter is
	 *  false, return false if the preferences can't be found.
	 *
	 * @param integer $p_user_id        A valid user identifier.
	 * @param integer $p_project_id     A valid project identifier.
	 * @param boolean $p_trigger_errors Whether to trigger error on failure.
	 * @return boolean|array
	 */
	static function cache_row( $p_user_id, $p_project_id = ALL_PROJECTS, $p_trigger_errors = true ) {
		global $g_cache_user_pref;
	
		if( isset( $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] ) ) {
			return $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id];
		}
	
		$t_query = 'SELECT * FROM {user_pref} WHERE user_id=' . \Core\Database::param() . ' AND project_id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( (int)$p_user_id, (int)$p_project_id ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			if( $p_trigger_errors ) {
				trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
			} else {
				$g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] = false;
				return false;
			}
		}
	
		if( !isset( $g_cache_user_pref[(int)$p_user_id] ) ) {
			$g_cache_user_pref[(int)$p_user_id] = array();
		}
	
		$g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] = $t_row;
	
		return $t_row;
	}
	
	/**
	 * Cache user preferences for a set of users
	 * @param array   $p_user_id_array An array of valid user identifiers.
	 * @param integer $p_project_id    A valid project identifier.
	 * @return void
	 */
	static function cache_array_rows( array $p_user_id_array, $p_project_id = ALL_PROJECTS ) {
		global $g_cache_user_pref;
		$c_user_id_array = array();
	
		# identify the user ids that are not cached already.
		foreach( $p_user_id_array as $t_user_id ) {
			if( !isset( $g_cache_user_pref[(int)$t_user_id][(int)$p_project_id] ) ) {
				$c_user_id_array[(int)$t_user_id] = (int)$t_user_id;
			}
		}
	
		# if all users are already cached, then return
		if( empty( $c_user_id_array ) ) {
			return;
		}
	
		$t_query = 'SELECT * FROM {user_pref} WHERE user_id IN (' . implode( ',', $c_user_id_array ) . ') AND project_id=' . \Core\Database::param();
	
		$t_result = \Core\Database::query( $t_query, array( (int)$p_project_id ) );
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			if( !isset( $g_cache_user_pref[(int)$t_row['user_id']] ) ) {
				$g_cache_user_pref[(int)$t_row['user_id']] = array();
			}
	
			$g_cache_user_pref[(int)$t_row['user_id']][(int)$p_project_id] = $t_row;
	
			# remove found users from required set.
			unset( $c_user_id_array[(int)$t_row['user_id']] );
		}
	
		# cache users that are not found as false (i.e. negative cache)
		foreach( $c_user_id_array as $t_user_id ) {
			$g_cache_user_pref[(int)$t_user_id][(int)$p_project_id] = false;
		}
	}
	
	/**
	 * Clear the user preferences cache (or just the given id if specified)
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return boolean
	 */
	static function clear_cache( $p_user_id = null, $p_project_id = null ) {
		global $g_cache_user_pref;
	
		if( null === $p_user_id ) {
			$g_cache_user_pref = array();
		} else if( null === $p_project_id ) {
			unset( $g_cache_user_pref[(int)$p_user_id] );
		} else {
			unset( $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] );
		}
	
		return true;
	}
	
	/**
	 * return true if the user has preferences assigned for the given project,
	 * false otherwise
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return boolean
	 */
	static function exists( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		if( false === \Core\User\Pref::cache_row( $p_user_id, $p_project_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * perform an insert of a preference object into the DB
	 * @param integer         $p_user_id    A valid user identifier.
	 * @param integer         $p_project_id A valid project identifier.
	 * @param UserPreferences $p_prefs      An UserPrefences Object.
	 * @return boolean
	 */
	static function insert( $p_user_id, $p_project_id, \Core\UserPreferences $p_prefs ) {
		static $s_vars;
		$c_user_id = (int)$p_user_id;
		$c_project_id = (int)$p_project_id;
	
		\Core\User::ensure_unprotected( $p_user_id );
	
		if( $s_vars == null ) {
			$s_vars = \Core\Utility::getClassProperties( '\\Core\\UserPreferences', 'protected' );
		}
	
		$t_values = array();
	
		$t_params[] = \Core\Database::param(); # user_id
		$t_values[] = $c_user_id;
		$t_params[] = \Core\Database::param(); # project_id
		$t_values[] = $c_project_id;
		foreach( $s_vars as $t_var => $t_val ) {
			array_push( $t_params, \Core\Database::param() );
			array_push( $t_values, $p_prefs->Get( $t_var ) );
		}
	
		$t_vars_string = implode( ', ', array_keys( $s_vars ) );
		$t_params_string = implode( ',', $t_params );
	
		$t_query = 'INSERT INTO {user_pref}
				  (user_id, project_id, ' . $t_vars_string . ') VALUES ( ' . $t_params_string . ')';
		\Core\Database::query( $t_query, $t_values );
	
		return true;
	}
	
	/**
	 * perform an update of a preference object into the DB
	 * @param integer         $p_user_id    A valid user identifier.
	 * @param integer         $p_project_id A valid project identifier.
	 * @param UserPreferences $p_prefs      An UserPrefences Object.
	 * @return void
	 */
	static function update( $p_user_id, $p_project_id, \Core\UserPreferences $p_prefs ) {
		static $s_vars;
	
		\Core\User::ensure_unprotected( $p_user_id );
	
		if( $s_vars == null ) {
			$s_vars = \Core\Utility::getClassProperties( '\\Core\\UserPreferences', 'protected' );
		}
	
		$t_pairs = array();
		$t_values = array();
	
		foreach( $s_vars as $t_var => $t_val ) {
			array_push( $t_pairs, $t_var . ' = ' . \Core\Database::param() ) ;
			array_push( $t_values, $p_prefs->$t_var );
		}
	
		$t_pairs_string = implode( ', ', $t_pairs );
		$t_values[] = $p_user_id;
		$t_values[] = $p_project_id;
	
		$t_query = 'UPDATE {user_pref} SET ' . $t_pairs_string . '
					  WHERE user_id=' . \Core\Database::param() . ' AND project_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, $t_values );
	
		user_pref_clear_cache( $p_user_id, $p_project_id );
	}
	
	/**
	 * delete a preferences row
	 * returns true if the preferences were successfully deleted
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return void
	 */
	static function delete( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		\Core\User::ensure_unprotected( $p_user_id );
	
		$t_query = 'DELETE FROM {user_pref}
					  WHERE user_id=' . \Core\Database::param() . ' AND
					  		project_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_user_id, $p_project_id ) );
	
		user_pref_clear_cache( $p_user_id, $p_project_id );
	}
	
	/**
	 * delete all preferences for a user in all projects
	 * returns true if the prefs were successfully deleted
	 *
	 * It is far more efficient to delete them all in one query than to
	 *  call \Core\User\Pref::delete() for each one and the code is short so that's
	 *  what we do
	 * @param integer $p_user_id A valid user identifier.
	 * @return void
	 */
	static function delete_all( $p_user_id ) {
		\Core\User::ensure_unprotected( $p_user_id );
	
		$t_query = 'DELETE FROM {user_pref} WHERE user_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_user_id ) );
	
		user_pref_clear_cache( $p_user_id );
	}
	
	/**
	 * delete all preferences for a project for all users (part of deleting the project)
	 * returns true if the prefs were successfully deleted
	 *
	 * It is far more efficient to delete them all in one query than to
	 * call \Core\User\Pref::delete() for each one and the code is short so that's what we do
	 * @param integer $p_project_id A valid project identifier.
	 * @return void
	 */
	static function delete_project( $p_project_id ) {
		$t_query = 'DELETE FROM {user_pref} WHERE project_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_project_id ) );
	}
	
	/**
	 * return the user's preferences in a UserPreferences object
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return UserPreferences
	 */
	static function get( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		static $s_vars;
		global $g_cache_current_user_pref;
	
		if( isset( $g_cache_current_user_pref[(int)$p_project_id] ) &&
			\Core\Auth::is_user_authenticated() &&
			\Core\Auth::get_current_user_id() == $p_user_id ) {
			return $g_cache_current_user_pref[(int)$p_project_id];
		}
	
		$t_prefs = new \Core\UserPreferences( $p_user_id, $p_project_id );
	
		$t_row = \Core\User\Pref::cache_row( $p_user_id, $p_project_id, false );
	
		# If the user has no preferences for the given project
		if( false === $t_row ) {
			if( ALL_PROJECTS != $p_project_id ) {
				# Try to get the prefs for ALL_PROJECTS (the defaults)
				$t_row = \Core\User\Pref::cache_row( $p_user_id, ALL_PROJECTS, false );
			}
	
			# If $t_row is still false (the user doesn't have default preferences)
			if( false === $t_row ) {
				# We use an empty array
				$t_row = array();
			}
		}
	
		if( $s_vars == null ) {
			$s_vars = \Core\Utility::getClassProperties( '\\Core\\UserPreferences', 'protected' );
		}
	
		$t_row_keys = array_keys( $t_row );
	
		# Check each variable in the class
		foreach( $s_vars as $t_var => $t_val ) {
			# If we got a field from the DB with the same name
			if( in_array( $t_var, $t_row_keys, true ) ) {
				# Store that value in the object
				$t_prefs->$t_var = $t_row[$t_var];
			}
		}
		if( \Core\Auth::is_user_authenticated() && \Core\Auth::get_current_user_id() == $p_user_id ) {
			$g_cache_current_user_pref[(int)$p_project_id] = $t_prefs;
		}
		return $t_prefs;
	}
	
	/**
	 * Return the specified preference field for the user id
	 * If the preference can't be found try to return a defined default
	 * If that fails, trigger a WARNING and return ''
	 * @param integer $p_user_id    A valid user identifier.
	 * @param string  $p_pref_name  A valid user preference name.
	 * @param integer $p_project_id A valid project identifier.
	 * @return string
	 */
	static function get_pref( $p_user_id, $p_pref_name, $p_project_id = ALL_PROJECTS ) {
		static $s_vars;
	
		$t_prefs = \Core\User\Pref::get( $p_user_id, $p_project_id );
	
		if( $s_vars == null ) {
			$t_reflection = new \ReflectionClass( '\\Core\\UserPreferences' );
			$s_vars = $t_reflection->getDefaultProperties();
		}
	
		if( in_array( $p_pref_name, array_keys( $s_vars ), true ) ) {
			return $t_prefs->Get( $p_pref_name );
		} else {
			\Core\Error::parameters( $p_pref_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
	
	/**
	 * returns user language
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return string language name or null if invalid language specified
	 */
	static function get_language( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_prefs = \Core\User\Pref::get( $p_user_id, $p_project_id );
	
		# ensure the language is a valid one
		$t_lang = $t_prefs->language;
		if( !\Core\Lang::language_exists( $t_lang ) ) {
			$t_lang = null;
		}
		return $t_lang;
	}
	
	/**
	 * Set a user preference
	 *
	 * By getting the preferences for the project first we deal fairly well with defaults. If there are currently no
	 * preferences for that project, the ALL_PROJECTS preferences will be returned so we end up storing a new set of
	 * preferences for the given project based on the preferences for ALL_PROJECTS.  If there isn't even an entry for
	 * ALL_PROJECTS, we'd get returned a default UserPreferences object to modify.
	 * @param integer $p_user_id    A valid user identifier.
	 * @param string  $p_pref_name  The name of the preference value to set.
	 * @param string  $p_pref_value A preference value to set.
	 * @param integer $p_project_id A valid project identifier.
	 * @return boolean
	 */
	static function set_pref( $p_user_id, $p_pref_name, $p_pref_value, $p_project_id = ALL_PROJECTS ) {
		$t_prefs = \Core\User\Pref::get( $p_user_id, $p_project_id );
	
		if( $t_prefs->$p_pref_name != $p_pref_value ) {
			$t_prefs->$p_pref_name = $p_pref_value;
			\Core\User\Pref::set( $p_user_id, $t_prefs, $p_project_id );
		}
	
		return true;
	}
	
	/**
	 * set the user's preferences for the project from the given preferences object
	 * Do the work by calling \Core\User\Pref::update() or \Core\User\Pref::insert() as appropriate
	 * @param integer         $p_user_id    A valid user identifier.
	 * @param UserPreferences $p_prefs      A UserPreferences object containing settings to set.
	 * @param integer         $p_project_id A valid project identifier.
	 * @return null
	 */
	static function set( $p_user_id, \Core\UserPreferences $p_prefs, $p_project_id = ALL_PROJECTS ) {
		if( \Core\User\Pref::exists( $p_user_id, $p_project_id ) ) {
			return \Core\User\Pref::update( $p_user_id, $p_project_id, $p_prefs );
		} else {
			return \Core\User\Pref::insert( $p_user_id, $p_project_id, $p_prefs );
		}
	}


}