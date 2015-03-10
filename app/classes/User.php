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
 * User API
 *
 * @package CoreAPI
 * @subpackage UserAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses ldap_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */


class User
{
	/**
	 * Cache a user row if necessary and return the cached copy
	 * If the second parameter is true (default), trigger an error
	 * if the user can't be found.  If the second parameter is
	 * false, return false if the user can't be found.
	 *
	 * @param integer $p_user_id        A valid user identifier.
	 * @param boolean $p_trigger_errors Trigger an error is the user does not exist.
	 * @return array|boolean array of database data or false if not found
	 */
	static function cache_row( $p_user_id, $p_trigger_errors = true ) {
		global $g_cache_user;
	
		if( isset( $g_cache_user[$p_user_id] ) ) {
			return $g_cache_user[$p_user_id];
		}
	
		$t_query = 'SELECT * FROM {user} WHERE id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_user_id ) );
	
		if( 0 == \Core\Database::num_rows( $t_result ) ) {
			$g_cache_user[$p_user_id] = false;
	
			if( $p_trigger_errors ) {
				\Core\Error::parameters( (integer)$p_user_id );
				trigger_error( ERROR_USER_BY_ID_NOT_FOUND, ERROR );
			}
	
			return false;
		}
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		$g_cache_user[$p_user_id] = $t_row;
	
		return $t_row;
	}
	
	/**
	 * Generate an array of User objects from given User ID's
	 *
	 * @param array $p_user_id_array An array of user identifiers.
	 * @return void
	 */
	static function cache_array_rows( array $p_user_id_array ) {
		global $g_cache_user;
		$c_user_id_array = array();
	
		foreach( $p_user_id_array as $t_user_id ) {
			if( !isset( $g_cache_user[(int)$t_user_id] ) ) {
				$c_user_id_array[] = (int)$t_user_id;
			}
		}
	
		if( empty( $c_user_id_array ) ) {
			return;
		}
	
		$t_query = 'SELECT * FROM {user} WHERE id IN (' . implode( ',', $c_user_id_array ) . ')';
		$t_result = \Core\Database::query( $t_query );
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$g_cache_user[(int)$t_row['id']] = $t_row;
		}
		return;
	}
	
	/**
	 * Cache an object as a bug.
	 * @param array $p_user_database_result A user row to cache.
	 * @return array|null
	 */
	static function cache_database_result( array $p_user_database_result ) {
		global $g_cache_user;
	
		if( isset( $g_cache_user[$p_user_database_result['id']] ) ) {
			return $g_cache_user[$p_user_database_result['id']];
		}
	
		$g_cache_user[$p_user_database_result['id']] = $p_user_database_result;
	}
	
	/**
	 * Clear the user cache (or just the given id if specified)
	 * @param integer $p_user_id A valid user identifier or the default of null to clear cache for all users.
	 * @return boolean
	 */
	static function clear_cache( $p_user_id = null ) {
		global $g_cache_user;
	
		if( null === $p_user_id ) {
			$g_cache_user = array();
		} else {
			unset( $g_cache_user[$p_user_id] );
		}
	
		return true;
	}
	
	/**
	 * Update Cache entry for a given user and field
	 * @param integer $p_user_id A valid user id to update.
	 * @param string  $p_field   The name of the field on the user object to update.
	 * @param mixed   $p_value   The updated value for the user object field.
	 * @return void
	 */
	static function update_cache( $p_user_id, $p_field, $p_value ) {
		global $g_cache_user;
	
		if( isset( $g_cache_user[$p_user_id] ) && isset( $g_cache_user[$p_user_id][$p_field] ) ) {
			$g_cache_user[$p_user_id][$p_field] = $p_value;
		} else {
			\Core\User::clear_cache( $p_user_id );
		}
	}
	
	/**
	 * Searches the cache for a given field and value pair against any user,
	 * and returns the first user id that matches
	 *
	 * @param string $p_field The user object field name to search the cache for.
	 * @param mixed  $p_value The field value to look for in the cache.
	 * @return integer|boolean
	 */
	static function search_cache( $p_field, $p_value ) {
		global $g_cache_user;
		if( isset( $g_cache_user ) ) {
			foreach( $g_cache_user as $t_user ) {
				if( $t_user[$p_field] == $p_value ) {
					return $t_user;
				}
			}
		}
		return false;
	}
	
	/**
	 * check to see if user exists by id
	 * return true if it does, false otherwise
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean
	 */
	static function exists( $p_user_id ) {
		$t_row = \Core\User::cache_row( $p_user_id, false );
	
		if( false === $t_row ) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * check to see if user exists by id
	 * if the user does not exist, trigger an error
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return void
	 */
	static function ensure_exists( $p_user_id ) {
		$c_user_id = (integer)$p_user_id;
	
		if( !\Core\User::exists( $c_user_id ) ) {
			\Core\Error::parameters( $c_user_id );
			trigger_error( ERROR_USER_BY_ID_NOT_FOUND, ERROR );
		}
	}
	
	/**
	 * return true if the username is unique, false if there is already a user with that username
	 * @param string $p_username The username to check.
	 * @return boolean
	 */
	static function is_name_unique( $p_username ) {
		$t_query = 'SELECT username FROM {user} WHERE username=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_username ), 1 );
	
		return !\Core\Database::result( $t_result );
	}
	
	/**
	 * Check if the username is unique and trigger an ERROR if it isn't
	 * @param string $p_username The username to check.
	 * @return void
	 */
	static function ensure_name_unique( $p_username ) {
		if( !\Core\User::is_name_unique( $p_username ) ) {
			trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
		}
	}
	
	/**
	 * Check if the realname is a valid username (does not account for uniqueness)
	 * Return 0 if it is invalid, The number of matches + 1
	 *
	 * @param string $p_username The username to check.
	 * @param string $p_realname The realname to check.
	 * @return integer
	 */
	static function is_realname_unique( $p_username, $p_realname ) {
		if( \Core\Utility::is_blank( $p_realname ) ) {
			# don't bother checking if realname is blank
			return 1;
		}
	
		$p_username = trim( $p_username );
		$p_realname = trim( $p_realname );
	
		# allow realname to match username
		$t_duplicate_count = 0;
		if( $p_realname !== $p_username ) {
			# check realname does not match an existing username
			#  but allow it to match the current user
			$t_target_user = \Core\User::get_id_by_name( $p_username );
			$t_other_user = \Core\User::get_id_by_name( $p_realname );
			if( ( false !== $t_other_user ) && ( $t_target_user !== $t_other_user ) ) {
				return 0;
			}
	
			# check to see if the realname is unique
			$t_query = 'SELECT id FROM {user} WHERE realname=' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( $p_realname ) );
	
			$t_users = array();
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$t_users[] = $t_row;
			}
			$t_duplicate_count = count( $t_users );
	
			if( $t_duplicate_count > 0 ) {
				# set flags for non-unique realnames
				if( \Core\Config::mantis_get( 'differentiate_duplicates' ) ) {
					for( $i = 0;$i < $t_duplicate_count;$i++ ) {
						$t_user_id = $t_users[$i]['id'];
						\Core\User::set_field( $t_user_id, 'duplicate_realname', ON );
					}
				}
			}
		}
		return $t_duplicate_count + 1;
	}
	
	/**
	 * Check if the realname is a unique
	 * Trigger an error if the username is not valid
	 *
	 * @param string $p_username The username to check.
	 * @param string $p_realname The realname to check.
	 * @return void
	 */
	static function ensure_realname_unique( $p_username, $p_realname ) {
		if( 1 > \Core\User::is_realname_unique( $p_username, $p_realname ) ) {
			trigger_error( ERROR_USER_REAL_MATCH_USER, ERROR );
		}
	}
	
	/**
	 * Check if the username is a valid username (does not account for uniqueness) realname can match
	 * @param string $p_username The username to check.
	 * @return boolean return true if user name is valid, false otherwise
	 */
	static function is_name_valid( $p_username ) {
		# The DB field is hard-coded. DB_FIELD_SIZE_USERNAME should not be modified.
		if( utf8_strlen( $p_username ) > DB_FIELD_SIZE_USERNAME ) {
			return false;
		}
	
		# username must consist of at least one character
		if( \Core\Utility::is_blank( $p_username ) ) {
			return false;
		}
	
		# Only allow a basic set of characters
		if( 0 == preg_match( \Core\Config::mantis_get( 'user_login_valid_regex' ), $p_username ) ) {
			return false;
		}
	
		# We have a valid username
		return true;
	}
	
	/**
	 * Check if the username is a valid username (does not account for uniqueness)
	 * Trigger an error if the username is not valid
	 * @param string $p_username The username to check.
	 * @return void
	 */
	static function ensure_name_valid( $p_username ) {
		if( !\Core\User::is_name_valid( $p_username ) ) {
			trigger_error( ERROR_USER_NAME_INVALID, ERROR );
		}
	}
	
	/**
	 * return whether user is monitoring bug for the user id and bug id
	 * @param integer $p_user_id A valid user identifier.
	 * @param integer $p_bug_id  A valid bug identifier.
	 * @return boolean
	 */
	static function is_monitoring_bug( $p_user_id, $p_bug_id ) {
		$t_query = 'SELECT COUNT(*) FROM {bug_monitor}
					  WHERE user_id=' . \Core\Database::param() . ' AND bug_id=' . \Core\Database::param();
	
		$t_result = \Core\Database::query( $t_query, array( (int)$p_user_id, (int)$p_bug_id ) );
	
		if( 0 == \Core\Database::result( $t_result ) ) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * return true if the user has access of ADMINISTRATOR or higher, false otherwise
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean
	 */
	static function is_administrator( $p_user_id ) {
		$t_access_level = \Core\User::get_field( $p_user_id, 'access_level' );
	
		if( $t_access_level >= \Core\Config::get_global( 'admin_site_threshold' ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check if a user has a protected user account.
	 * Protected user accounts cannot be updated without manage_user_threshold
	 * permission. If the user ID supplied is that of the anonymous user, this
	 * function will always return true. The anonymous user account is always
	 * considered to be protected.
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean true: user is protected; false: user is not protected.
	 * @access public
	 */
	static function is_protected( $p_user_id ) {
		if( \Core\User::is_anonymous( $p_user_id ) || ON == \Core\User::get_field( $p_user_id, 'protected' ) ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Check if a user is the anonymous user account.
	 * When anonymous logins are disabled this function will always return false.
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean true: user is the anonymous user; false: user is not the anonymous user.
	 * @access public
	 */
	static function is_anonymous( $p_user_id ) {
		if( ON == \Core\Config::mantis_get( 'allow_anonymous_login' ) && \Core\User::get_field( $p_user_id, 'username' ) == \Core\Config::mantis_get( 'anonymous_account' ) ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Trigger an ERROR if the user account is protected
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return void
	 */
	static function ensure_unprotected( $p_user_id ) {
		if( \Core\User::is_protected( $p_user_id ) ) {
			trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
		}
	}
	
	/**
	 * return true is the user account is enabled, false otherwise
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean
	 */
	static function is_enabled( $p_user_id ) {
		if( ON == \Core\User::get_field( $p_user_id, 'enabled' ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * count the number of users at or greater than a specific level
	 *
	 * @param integer $p_level Access Level to count users. The default is to include ANYBODY.
	 * @return integer
	 */
	static function count_level( $p_level = ANYBODY ) {
		$t_query = 'SELECT COUNT(id) FROM {user} WHERE access_level>=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_level ) );
	
		# Get the list of connected users
		$t_users = \Core\Database::result( $t_result );
	
		return $t_users;
	}
	
	/**
	 * Return an array of user ids that are logged in.
	 * A user is considered logged in if the last visit timestamp is within the
	 * specified session duration.
	 * If the session duration is 0, then no users will be returned.
	 * @param integer $p_session_duration_in_minutes The duration to return logged in users for.
	 * @return array
	 */
	static function get_logged_in_user_ids( $p_session_duration_in_minutes ) {
		$t_session_duration_in_minutes = (integer)$p_session_duration_in_minutes;
	
		# if session duration is 0, then there is no logged in users.
		if( $t_session_duration_in_minutes == 0 ) {
			return array();
		}
	
		# Generate timestamp
		$t_last_timestamp_threshold = mktime( date( 'H' ), date( 'i' ) - 1 * $t_session_duration_in_minutes, date( 's' ), date( 'm' ), date( 'd' ), date( 'Y' ) );
	
		# Execute query
		$t_query = 'SELECT id FROM {user} WHERE last_visit > ' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $t_last_timestamp_threshold ), 1 );
	
		# Get the list of connected users
		$t_users_connected = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_users_connected[] = $t_row['id'];
		}
	
		return $t_users_connected;
	}
	
	/**
	 * Create a user.
	 * returns false if error, the generated cookie string if valid
	 *
	 * @param string  $p_username     A valid username.
	 * @param string  $p_password     The password to set for the user.
	 * @param string  $p_email        The Email Address of the user.
	 * @param integer $p_access_level The global access level for the user.
	 * @param boolean $p_protected    Whether the account is protected from modifications (default false).
	 * @param boolean $p_enabled      Whether the account is enabled.
	 * @param string  $p_realname     The realname of the user.
	 * @param string  $p_admin_name   The name of the administrator creating the account.
	 * @return string Cookie String
	 */
	static function create( $p_username, $p_password, $p_email = '',
		$p_access_level = null, $p_protected = false, $p_enabled = true,
		$p_realname = '', $p_admin_name = '' ) {
		if( null === $p_access_level ) {
			$p_access_level = \Core\Config::mantis_get( 'default_new_account_access_level' );
		}
	
		$t_password = \Core\Auth::process_plain_password( $p_password );
	
		$c_enabled = (bool)$p_enabled;
	
		\Core\User::ensure_name_valid( $p_username );
		\Core\User::ensure_name_unique( $p_username );
		\Core\User::ensure_realname_unique( $p_username, $p_realname );
		\Core\Email::ensure_valid( $p_email );
	
		$t_cookie_string = \Core\Auth::generate_unique_cookie_string();
	
		$t_query = 'INSERT INTO {user}
					    ( username, email, password, date_created, last_visit,
					     enabled, access_level, login_count, cookie_string, realname )
					  VALUES
					    ( ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param()  . ',
					     ' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ', ' . \Core\Database::param() . ')';
		\Core\Database::query( $t_query, array( $p_username, $p_email, $t_password, \Core\Database::now(), \Core\Database::now(), $c_enabled, (int)$p_access_level, 0, $t_cookie_string, $p_realname ) );
	
		# Create preferences for the user
		$t_user_id = \Core\Database::insert_id( \Core\Database::get_table( 'user' ) );
	
		# Users are added with protected set to FALSE in order to be able to update
		# preferences.  Now set the real value of protected.
		if( $p_protected ) {
			\Core\User::set_field( $t_user_id, 'protected', (bool)$p_protected );
		}
	
		# Send notification email
		if( !\Core\Utility::is_blank( $p_email ) ) {
			$t_confirm_hash = \Core\Auth::generate_confirm_hash( $t_user_id );
			\Core\Email::signup( $t_user_id, $p_password, $t_confirm_hash, $p_admin_name );
		}
	
		return $t_cookie_string;
	}
	
	/**
	 * Signup a user.
	 * If the use_ldap_email config option is on then tries to find email using
	 * ldap. $p_email may be empty, but the user wont get any emails.
	 * returns false if error, the generated cookie string if ok
	 * @param string $p_username The username to sign up.
	 * @param string $p_email    The email address of the user signing up.
	 * @return string|boolean cookie string or false on error
	 */
	static function signup( $p_username, $p_email = null ) {
		if( null === $p_email ) {
			$p_email = '';
	
			# @@@ I think the ldap_email stuff is a bit borked
			#  Where is it being set?  When is it being used?
			#  Shouldn't we override an email that is passed in here?
			#  If the user doesn't exist in ldap, is the account created?
			#  If so, there password won't get set anywhere...  (etc)
			#  RJF: I was going to check for the existence of an LDAP email.
			#  however, since we can't create an LDAP account at the moment,
			#  and we don't know the user password in advance, we may not be able
			#  to retrieve it anyway.
			#  I'll re-enable this once a plan has been properly formulated for LDAP
			#  account management and creation.
			#			$t_email = '';
			#			if( ON == \Core\Config::mantis_get( 'use_ldap_email' ) ) {
			#				$t_email = \Core\LDAP::email_from_username( $p_username );
			#			}
			#			if( !\Core\Utility::is_blank( $t_email ) ) {
			#				$p_email = $t_email;
			#			}
		}
	
		$p_email = trim( $p_email );
	
		# Create random password
		$t_password = \Core\Auth::generate_random_password();
	
		return \Core\User::create( $p_username, $t_password, $p_email );
	}
	
	/**
	 * delete project-specific user access levels.
	 * returns true when successfully deleted
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean Always true
	 */
	static function delete_project_specific_access_levels( $p_user_id ) {
		\Core\User::ensure_unprotected( $p_user_id );
	
		$t_query = 'DELETE FROM {project_user_list} WHERE user_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * delete profiles for the specified user
	 * returns true when successfully deleted
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean
	 */
	static function delete_profiles( $p_user_id ) {
		\Core\User::ensure_unprotected( $p_user_id );
	
		# Remove associated profiles
		$t_query = 'DELETE FROM {user_profile} WHERE user_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * delete a user account (account, profiles, preferences, project-specific access levels)
	 * returns true when the account was successfully deleted
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean Always true
	 */
	static function delete( $p_user_id ) {
		$c_user_id = (int)$p_user_id;
	
		\Core\User::ensure_unprotected( $p_user_id );
	
		# Remove associated profiles
		\Core\User::delete_profiles( $p_user_id );
	
		# Remove associated preferences
		\Core\User\Pref::delete_all( $p_user_id );
	
		# Remove project specific access levels
		\Core\User::delete_project_specific_access_levels( $p_user_id );
	
		# unset non-unique realname flags if necessary
		if( \Core\Config::mantis_get( 'differentiate_duplicates' ) ) {
			$c_realname = \Core\User::get_field( $p_user_id, 'realname' );
			$t_query = 'SELECT id FROM {user} WHERE realname=' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( $c_realname ) );
	
			$t_users = array();
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$t_users[] = $t_row;
			}
	
			$t_user_count = count( $t_users );
	
			if( $t_user_count == 2 ) {
				# unset flags if there are now only 2 unique names
				for( $i = 0;$i < $t_user_count;$i++ ) {
					$t_user_id = $t_users[$i]['id'];
					\Core\User::set_field( $t_user_id, 'duplicate_realname', OFF );
				}
			}
		}
	
		\Core\User::clear_cache( $p_user_id );
	
		# Remove account
		$t_query = 'DELETE FROM {user} WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_user_id ) );
	
		return true;
	}
	
	/**
	 * get a user id from a username
	 * return false if the username does not exist
	 *
	 * @param string $p_username The username to retrieve data for.
	 * @return integer|boolean
	 */
	static function get_id_by_name( $p_username ) {
		if( $t_user = \Core\User::search_cache( 'username', $p_username ) ) {
			return $t_user['id'];
		}
	
		$t_query = 'SELECT * FROM {user} WHERE username=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_username ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
		if( $t_row ) {
			\Core\User::cache_database_result( $t_row );
			return $t_row['id'];
		}
		return false;
	}
	
	/**
	 * Get a user id from their email address
	 *
	 * @param string $p_email The email address to retrieve data for.
	 * @return array
	 */
	static function get_id_by_email( $p_email ) {
		global $g_cache_user;
		if( $t_user = \Core\User::search_cache( 'email', $p_email ) ) {
			return $t_user['id'];
		}
	
		$t_query = 'SELECT * FROM {user} WHERE email=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_email ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
		if( $t_row ) {
			\Core\User::cache_database_result( $t_row );
			return $t_row['id'];
		}
		return false;
	}
	
	
	/**
	 * Get a user id from their real name
	 *
	 * @param string $p_realname The realname to retrieve data for.
	 * @return array
	 */
	static function get_id_by_realname( $p_realname ) {
		global $g_cache_user;
		if( $t_user = \Core\User::search_cache( 'realname', $p_realname ) ) {
			return $t_user['id'];
		}
	
		$t_query = 'SELECT * FROM {user} WHERE realname=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_realname ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			return false;
		} else {
			\Core\User::cache_database_result( $t_row );
			return $t_row['id'];
		}
	}
	
	/**
	 * return all data associated with a particular user name
	 * return false if the username does not exist
	 *
	 * @param integer $p_username The username to retrieve data for.
	 * @return array
	 */
	static function get_row_by_name( $p_username ) {
		$t_user_id = \Core\User::get_id_by_name( $p_username );
	
		if( false === $t_user_id ) {
			return false;
		}
	
		$t_row = \Core\User::get_row( $t_user_id );
	
		return $t_row;
	}
	
	/**
	 * return a user row
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return array
	 */
	static function get_row( $p_user_id ) {
		return \Core\User::cache_row( $p_user_id );
	}
	
	/**
	 * return the specified user field for the user id
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param string  $p_field_name The field name to retrieve.
	 * @return string
	 */
	static function get_field( $p_user_id, $p_field_name ) {
		if( NO_USER == $p_user_id ) {
			\Core\Error::parameters( NO_USER );
			trigger_error( ERROR_USER_BY_ID_NOT_FOUND, WARNING );
			return '@null@';
		}
	
		$t_row = \Core\User::get_row( $p_user_id );
	
		if( isset( $t_row[$p_field_name] ) ) {
			switch( $p_field_name ) {
				case 'access_level':
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
	 * lookup the user's email in LDAP or the db as appropriate
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_email( $p_user_id ) {
		$t_email = '';
		if( LDAP == \Core\Config::mantis_get( 'login_method' ) && ON == \Core\Config::mantis_get( 'use_ldap_email' ) ) {
			$t_email = \Core\LDAP::email( $p_user_id );
		}
		if( \Core\Utility::is_blank( $t_email ) ) {
			$t_email = \Core\User::get_field( $p_user_id, 'email' );
		}
		return $t_email;
	}
	
	/**
	 * lookup the user's realname
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_realname( $p_user_id ) {
		$t_realname = '';
	
		if( LDAP == \Core\Config::mantis_get( 'login_method' ) && ON == \Core\Config::mantis_get( 'use_ldap_realname' ) ) {
			$t_realname = \Core\LDAP::realname( $p_user_id );
		}
	
		if( \Core\Utility::is_blank( $t_realname ) ) {
			$t_realname = \Core\User::get_field( $p_user_id, 'realname' );
		}
	
		return $t_realname;
	}
	
	/**
	 * return the username or a string "user<id>" if the user does not exist
	 * if show_user_realname_threshold is set and real name is not empty, return it instead
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_name( $p_user_id ) {
		$t_row = \Core\User::cache_row( $p_user_id, false );
	
		if( false == $t_row ) {
			return \Core\Lang::get( 'prefix_for_deleted_users' ) . (int)$p_user_id;
		} else {
			if( ON == \Core\Config::mantis_get( 'show_realname' ) ) {
				if( \Core\Utility::is_blank( $t_row['realname'] ) ) {
					return $t_row['username'];
				} else {
					if( isset( $t_row['duplicate_realname'] ) && ( ON == $t_row['duplicate_realname'] ) ) {
						return $t_row['realname'] . ' (' . $t_row['username'] . ')';
					} else {
						return $t_row['realname'];
					}
				}
			} else {
				return $t_row['username'];
			}
		}
	}
	
	/**
	* Return the user avatar image URL
	* in this first implementation, only gravatar.com avatars are supported
	*
	* This function returns an array( URL, width, height ) or an empty array when the given user has no avatar.
	*
	* @param integer $p_user_id A valid user identifier.
	* @param integer $p_size    The required number of pixel in the image to retrieve the link for.
	* @return array
	*/
	static function get_avatar( $p_user_id, $p_size = 80 ) {
		$t_default_avatar = \Core\Config::mantis_get( 'show_avatar' );
	
		if( OFF === $t_default_avatar ) {
			# Avatars are not used
			return array();
		}
		# Set default avatar for legacy configuration
		if( ON === $t_default_avatar ) {
			$t_default_avatar = 'identicon';
		}
	
		# Default avatar is either one of Gravatar's options, or
		# assumed to be an URL to a default avatar image
		$t_default_avatar = urlencode( $t_default_avatar );
		$t_rating = 'G';
	
		if ( \Core\User::exists( $p_user_id ) ) {
			$t_email_hash = md5( strtolower( trim( \Core\User::get_email( $p_user_id ) ) ) );
		} else {
			$t_email_hash = md5( 'generic-avatar-since-user-not-found' );
		}
	
		# Build Gravatar URL
		if( \Core\HTTP::is_protocol_https() ) {
			$t_avatar_url = 'https://secure.gravatar.com/';
		} else {
			$t_avatar_url = 'http://www.gravatar.com/';
		}
		$t_avatar_url .= 'avatar/' . $t_email_hash . '?d=' . $t_default_avatar . '&r=' . $t_rating . '&s=' . $p_size;
	
		return array( $t_avatar_url, $p_size, $p_size );
	}
	
	/**
	 * return the user's access level
	 * account for private project and the project user lists
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return integer
	 */
	static function get_access_level( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_access_level = \Core\User::get_field( $p_user_id, 'access_level' );
	
		if( \Core\User::is_administrator( $p_user_id ) ) {
			return $t_access_level;
		}
	
		$t_project_access_level = \Core\Project::get_local_user_access_level( $p_project_id, $p_user_id );
	
		if( false === $t_project_access_level ) {
			return $t_access_level;
		} else {
			return $t_project_access_level;
		}
	}
	
	/**
	 * retun an array of project IDs to which the user has access
	 *
	 * @param integer $p_user_id       A valid user identifier.
	 * @param boolean $p_show_disabled Whether to include disabled projects in the result array.
	 * @return array
	 */
	static function get_accessible_projects( $p_user_id, $p_show_disabled = false ) {
		global $g_user_accessible_projects_cache;
	
		if( null !== $g_user_accessible_projects_cache && \Core\Auth::get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
			return $g_user_accessible_projects_cache;
		}
	
		if( \Core\Access::has_global_level( \Core\Config::mantis_get( 'private_project_threshold' ), $p_user_id ) ) {
			$t_projects = \Core\Project\Hierarchy::get_subprojects( ALL_PROJECTS, $p_show_disabled );
		} else {
			$t_public = VS_PUBLIC;
			$t_private = VS_PRIVATE;
	
			$t_query = 'SELECT p.id, p.name, ph.parent_id
							  FROM {project} p
							  LEFT JOIN {project_user_list} u
							    ON p.id=u.project_id AND u.user_id=' . \Core\Database::param() . '
							  LEFT JOIN {project_hierarchy} ph
							    ON ph.child_id = p.id
							  WHERE ' . ( $p_show_disabled ? '' : ( 'p.enabled = ' . \Core\Database::param() . ' AND ' ) ) . '
								( p.view_state=' . \Core\Database::param() . '
								    OR (p.view_state=' . \Core\Database::param() . '
									    AND
								        u.user_id=' . \Core\Database::param() . ' )
								) ORDER BY p.name';
			$t_result = \Core\Database::query( $t_query, ( $p_show_disabled ? array( $p_user_id, $t_public, $t_private, $p_user_id ) : array( $p_user_id, true, $t_public, $t_private, $p_user_id ) ) );
	
			$t_projects = array();
	
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$t_projects[(int)$t_row['id']] = ( $t_row['parent_id'] === null ) ? 0 : (int)$t_row['parent_id'];
			}
	
			# prune out children where the parents are already listed. Make the list
			#  first, then prune to avoid pruning a parent before the child is found.
			$t_prune = array();
			foreach( $t_projects as $t_id => $t_parent ) {
				if( ( $t_parent !== 0 ) && isset( $t_projects[$t_parent] ) ) {
					$t_prune[] = $t_id;
				}
			}
			foreach( $t_prune as $t_id ) {
				unset( $t_projects[$t_id] );
			}
			$t_projects = array_keys( $t_projects );
		}
	
		if( \Core\Auth::get_current_user_id() == $p_user_id ) {
			$g_user_accessible_projects_cache = $t_projects;
		}
	
		return $t_projects;
	}
	
	/**
	 * return an array of sub-project IDs of a certain project to which the user has access
	 * @param integer $p_user_id       A valid user identifier.
	 * @param integer $p_project_id    A valid project identifier.
	 * @param boolean $p_show_disabled Include disabled projects in the resulting array.
	 * @return array
	 */
	static function get_accessible_subprojects( $p_user_id, $p_project_id, $p_show_disabled = false ) {
		global $g_user_accessible_subprojects_cache;
	
		if( null !== $g_user_accessible_subprojects_cache && \Core\Auth::get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
			if( isset( $g_user_accessible_subprojects_cache[$p_project_id] ) ) {
				return $g_user_accessible_subprojects_cache[$p_project_id];
			} else {
				return array();
			}
		}
	
		\Core\Database::param_push();
	
		if( \Core\Access::has_global_level( \Core\Config::mantis_get( 'private_project_threshold' ), $p_user_id ) ) {
			$t_enabled_clause = $p_show_disabled ? '' : 'p.enabled = ' . \Core\Database::param() . ' AND';
			$t_query = 'SELECT DISTINCT p.id, p.name, ph.parent_id
						  FROM {project} p
						  LEFT JOIN {project_hierarchy} ph
						    ON ph.child_id = p.id
						  WHERE ' . $t_enabled_clause . '
						  	 ph.parent_id IS NOT NULL
						  ORDER BY p.name';
			$t_result = \Core\Database::query( $t_query, ( $p_show_disabled ? array() : array( true ) ) );
		} else {
			$t_query = 'SELECT DISTINCT p.id, p.name, ph.parent_id
						  FROM {project} p
						  LEFT JOIN {project_user_list} u
						    ON p.id = u.project_id AND u.user_id=' . \Core\Database::param() . '
						  LEFT JOIN {project_hierarchy} ph
						    ON ph.child_id = p.id
						  WHERE ' . ( $p_show_disabled ? '' : ( 'p.enabled = ' . \Core\Database::param() . ' AND ' ) ) . '
						  	ph.parent_id IS NOT NULL AND
							( p.view_state=' . \Core\Database::param() . '
							    OR (p.view_state=' . \Core\Database::param() . '
								    AND
							        u.user_id=' . \Core\Database::param() . ' )
							)
						  ORDER BY p.name';
			$t_param = array( $p_user_id, VS_PUBLIC, VS_PRIVATE, $p_user_id );
			if( !$p_show_disabled ) {
				# Insert enabled flag value in 2nd position of parameter array
				array_splice( $t_param, 1, 0, true );
			}
			$t_result = \Core\Database::query( $t_query, $t_param );
		}
	
		$t_projects = array();
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			if( !isset( $t_projects[(int)$t_row['parent_id']] ) ) {
				$t_projects[(int)$t_row['parent_id']] = array();
			}
	
			array_push( $t_projects[(int)$t_row['parent_id']], (int)$t_row['id'] );
		}
	
		if( \Core\Auth::get_current_user_id() == $p_user_id ) {
			$g_user_accessible_subprojects_cache = $t_projects;
		}
	
		if( !isset( $t_projects[(int)$p_project_id] ) ) {
			$t_projects[(int)$p_project_id] = array();
		}
	
		return $t_projects[(int)$p_project_id];
	}
	
	/**
	 * retun an array of sub-project IDs of all sub-projects project to which the user has access
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return array
	 */
	static function get_all_accessible_subprojects( $p_user_id, $p_project_id ) {
		# @todo (thraxisp) Should all top level projects be a sub-project of ALL_PROJECTS implicitly?
		# affects how news and some summaries are generated
		$t_todo = \Core\User::get_accessible_subprojects( $p_user_id, $p_project_id );
		$t_subprojects = array();
	
		while( $t_todo ) {
			$t_elem = (int)array_shift( $t_todo );
			if( !in_array( $t_elem, $t_subprojects ) ) {
				array_push( $t_subprojects, $t_elem );
				$t_todo = array_merge( $t_todo, \Core\User::get_accessible_subprojects( $p_user_id, $t_elem ) );
			}
		}
	
		return $t_subprojects;
	}
	
	/**
	 * retun an array of sub-project IDs of all project to which the user has access
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return array
	 */
	static function get_all_accessible_projects( $p_user_id, $p_project_id ) {
		if( ALL_PROJECTS == $p_project_id ) {
			$t_topprojects = \Core\User::get_accessible_projects( $p_user_id );
	
			# Cover the case for PHP < 5.4 where array_combine() returns
			# false and triggers warning if arrays are empty (see #16187)
			if( empty( $t_topprojects ) ) {
				return array();
			}
	
			# Create a combined array where key = value
			$t_project_ids = array_combine( $t_topprojects, $t_topprojects );
	
			# Add all subprojects user has access to
			foreach( $t_topprojects as $t_project ) {
				$t_subprojects_ids = \Core\User::get_all_accessible_subprojects( $p_user_id, $t_project );
				foreach( $t_subprojects_ids as $t_id ) {
					$t_project_ids[$t_id] = $t_id;
				}
			}
		} else {
			\Core\Access::ensure_project_level( VIEWER, $p_project_id );
			$t_project_ids = \Core\User::get_all_accessible_subprojects( $p_user_id, $p_project_id );
			array_unshift( $t_project_ids, $p_project_id );
		}
	
		return $t_project_ids;
	}
	
	/**
	 * Get a list of projects the specified user is assigned to.
	 * @param integer $p_user_id A valid user identifier.
	 * @return array An array of projects by project id the specified user is assigned to.
	 *		The array contains the id, name, view state, and project access level for the user.
	 */
	static function get_assigned_projects( $p_user_id ) {
		$t_query = 'SELECT DISTINCT p.id, p.name, p.view_state, u.access_level
					FROM {project} p
					LEFT JOIN {project_user_list} u
					ON p.id=u.project_id
					WHERE p.enabled = \'1\' AND
						u.user_id=' . \Core\Database::param() . '
					ORDER BY p.name';
		$t_result = \Core\Database::query( $t_query, array( $p_user_id ) );
		$t_projects = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_project_id = $t_row['id'];
			$t_projects[$t_project_id] = $t_row;
		}
		return $t_projects;
	}
	
	/**
	 * List of users that are NOT in the specified project and that are enabled
	 * if no project is specified use the current project
	 * also exclude any administrators
	 * @param integer $p_project_id A valid project identifier.
	 * @return array List of users not assigned to the specified project
	 */
	static function get_unassigned_by_project_id( $p_project_id = null ) {
		if( null === $p_project_id ) {
			$p_project_id = \Core\Helper::get_current_project();
		}
	
		$t_adm = \Core\Config::get_global( 'admin_site_threshold' );
		$t_query = 'SELECT DISTINCT u.id, u.username, u.realname
					FROM {user} u
					LEFT JOIN {project_user_list} p
					ON p.user_id=u.id AND p.project_id=' . \Core\Database::param() . '
					WHERE u.access_level<' . \Core\Database::param() . ' AND
						u.enabled = ' . \Core\Database::param() . ' AND
						p.user_id IS NULL
					ORDER BY u.realname, u.username';
		$t_result = \Core\Database::query( $t_query, array( $p_project_id, $t_adm, true ) );
		$t_display = array();
		$t_sort = array();
		$t_users = array();
		$t_show_realname = ( ON == \Core\Config::mantis_get( 'show_realname' ) );
		$t_sort_by_last_name = ( ON == \Core\Config::mantis_get( 'sort_by_last_name' ) );
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_users[] = $t_row['id'];
			$t_user_name = \Core\String::attribute( $t_row['username'] );
			$t_sort_name = $t_user_name;
			if( ( isset( $t_row['realname'] ) ) && ( $t_row['realname'] <> '' ) && $t_show_realname ) {
				$t_user_name = \Core\String::attribute( $t_row['realname'] );
				if( $t_sort_by_last_name ) {
					$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
					$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
				} else {
					$t_sort_name = utf8_strtolower( $t_user_name );
				}
			}
			$t_display[] = $t_user_name;
			$t_sort[] = $t_sort_name;
		}
		array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
		$t_count = count( $t_sort );
		$t_user_list = array();
		for( $i = 0;$i < $t_count; $i++ ) {
			$t_user_list[$t_users[$i]] = $t_display[$i];
		}
		return $t_user_list;
	}
	
	/**
	 * return the number of open assigned bugs to a user in a project
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return integer
	 */
	static function get_assigned_open_bug_count( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_where_prj = \Core\Helper::project_specific_where( $p_project_id, $p_user_id ) . ' AND';
	
		$t_resolved = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
	
		$t_query = 'SELECT COUNT(*)
					  FROM {bug}
					  WHERE ' . $t_where_prj . '
							status<' . \Core\Database::param() . ' AND
							handler_id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $t_resolved, $p_user_id ) );
	
		return \Core\Database::result( $t_result );
	}
	
	/**
	 * return the number of open reported bugs by a user in a project
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return integer
	 */
	static function get_reported_open_bug_count( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_where_prj = \Core\Helper::project_specific_where( $p_project_id, $p_user_id ) . ' AND';
	
		$t_resolved = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
	
		$t_query = 'SELECT COUNT(*) FROM {bug}
					  WHERE ' . $t_where_prj . '
							  status<' . \Core\Database::param() . ' AND
							  reporter_id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $t_resolved, $p_user_id ) );
	
		return \Core\Database::result( $t_result );
	}
	
	/**
	 * return a profile row
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_profile_id The profile identifier to retrieve.
	 * @return array
	 */
	static function get_profile_row( $p_user_id, $p_profile_id ) {
		$t_query = 'SELECT * FROM {user_profile}
					  WHERE id=' . \Core\Database::param() . ' AND
							user_id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_profile_id, $p_user_id ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			trigger_error( ERROR_USER_PROFILE_NOT_FOUND, ERROR );
		}
	
		return $t_row;
	}
	
	/**
	 * Get failed login attempts
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean
	 */
	static function is_login_request_allowed( $p_user_id ) {
		$t_max_failed_login_count = \Core\Config::mantis_get( 'max_failed_login_count' );
		$t_failed_login_count = \Core\User::get_field( $p_user_id, 'failed_login_count' );
		return( $t_failed_login_count < $t_max_failed_login_count || OFF == $t_max_failed_login_count );
	}
	
	/**
	 * Get 'lost password' in progress attempts
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean
	 */
	static function is_lost_password_request_allowed( $p_user_id ) {
		if( OFF == \Core\Config::mantis_get( 'lost_password_feature' ) ) {
			return false;
		}
		$t_max_lost_password_in_progress_count = \Core\Config::mantis_get( 'max_lost_password_in_progress_count' );
		$t_lost_password_in_progress_count = \Core\User::get_field( $p_user_id, 'lost_password_request_count' );
		return( $t_lost_password_in_progress_count < $t_max_lost_password_in_progress_count || OFF == $t_max_lost_password_in_progress_count );
	}
	
	/**
	 * return the bug filter parameters for the specified user
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return array The user filter, or default filter if not valid.
	 */
	static function get_bug_filter( $p_user_id, $p_project_id = null ) {
		if( null === $p_project_id ) {
			$t_project_id = \Core\Helper::get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}
	
		$t_view_all_cookie_id = \Core\Filter::db_get_project_current( $t_project_id, $p_user_id );
		$t_view_all_cookie = \Core\Filter::db_get_filter( $t_view_all_cookie_id, $p_user_id );
		$t_cookie_detail = explode( '#', $t_view_all_cookie, 2 );
	
		if( !isset( $t_cookie_detail[1] ) ) {
			return \Core\Filter::get_default();
		}
	
		$t_filter = json_decode( $t_cookie_detail[1], true );
	
		$t_filter = \Core\Filter::ensure_valid_filter( $t_filter );
	
		return $t_filter;
	}
	
	/**
	 * Update the last_visited field to be now
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean always true
	 */
	static function update_last_visit( $p_user_id ) {
		$c_user_id = (int)$p_user_id;
		$c_value = \Core\Database::now();
	
		$t_query = 'UPDATE {user} SET last_visit=' . \Core\Database::param() . ' WHERE id=' . \Core\Database::param();
	
		\Core\Database::query( $t_query, array( $c_value, $c_user_id ) );
	
		\Core\User::update_cache( $c_user_id, 'last_visit', $c_value );
	
		return true;
	}
	
	/**
	 * Increment the number of times the user has logged in
	 * This function is only called from the login.php script
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean always true
	 */
	static function increment_login_count( $p_user_id ) {
		$t_query = 'UPDATE {user} SET login_count=login_count+1 WHERE id=' . \Core\Database::param();
	
		\Core\Database::query( $t_query, array( (int)$p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * Reset to zero the failed login attempts
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean always true
	 */
	static function reset_failed_login_count_to_zero( $p_user_id ) {
		$t_query = 'UPDATE {user} SET failed_login_count=0 WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * Increment the failed login count by 1
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean always true
	 */
	static function increment_failed_login_count( $p_user_id ) {
		$t_query = 'UPDATE {user} SET failed_login_count=failed_login_count+1 WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * Reset to zero the 'lost password' in progress attempts
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean always true
	 */
	static function reset_lost_password_in_progress_count_to_zero( $p_user_id ) {
		$t_query = 'UPDATE {user} SET lost_password_request_count=0 WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * Increment the failed login count by 1
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @return boolean always true
	 */
	static function increment_lost_password_in_progress_count( $p_user_id ) {
		$t_query = 'UPDATE {user}
					SET lost_password_request_count=lost_password_request_count+1
					WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_user_id ) );
	
		\Core\User::clear_cache( $p_user_id );
	
		return true;
	}
	
	/**
	 * Sets multiple fields on a user
	 *
	 * @param integer $p_user_id A valid user identifier.
	 * @param array   $p_fields  Keys are the field names and the values are the field values.
	 * @return void
	 */
	static function set_fields( $p_user_id, array $p_fields ) {
		if( !array_key_exists( 'protected', $p_fields ) ) {
			\Core\User::ensure_unprotected( $p_user_id );
		}
	
		$t_query = 'UPDATE {user}';
		$t_parameters = array();
	
		foreach ( $p_fields as $t_field_name => $t_field_value ) {
			$c_field_name = \Core\Database::prepare_string( $t_field_name );
	
			if( count( $t_parameters ) == 0 ) {
				$t_query .= ' SET '. $c_field_name. '=' . \Core\Database::param();
			} else {
				$t_query .= ' , ' . $c_field_name. '=' . \Core\Database::param();
			}
	
			array_push( $t_parameters, $t_field_value );
		}
	
		$t_query .= ' WHERE id=' . \Core\Database::param();
		array_push( $t_parameters, (int)$p_user_id );
	
		\Core\Database::query( $t_query, $t_parameters );
	
		\Core\User::clear_cache( $p_user_id );
	}
	
	/**
	 * Set a user field
	 *
	 * @param integer $p_user_id     A valid user identifier.
	 * @param string  $p_field_name  A valid field name to set.
	 * @param string  $p_field_value The field value to set.
	 * @return boolean always true
	 */
	static function set_field( $p_user_id, $p_field_name, $p_field_value ) {
		\Core\User::set_fields( $p_user_id, array ( $p_field_name => $p_field_value ) );
	
		return true;
	}
	
	/**
	 * Set Users Default project in preferences
	 * @param integer $p_user_id    A valid user identifier.
	 * @param integer $p_project_id A valid project identifier.
	 * @return void
	 */
	static function set_default_project( $p_user_id, $p_project_id ) {
		\Core\User\Pref::set_pref( $p_user_id, 'default_project', (int)$p_project_id );
	}
	
	/**
	 * Set the user's password to the given string, encoded as appropriate
	 *
	 * @param integer $p_user_id         A valid user identifier.
	 * @param string  $p_password        A password to set.
	 * @param boolean $p_allow_protected Whether Allow password change to a protected account. This defaults to false.
	 * @return boolean always true
	 */
	static function set_password( $p_user_id, $p_password, $p_allow_protected = false ) {
		if( !$p_allow_protected ) {
			\Core\User::ensure_unprotected( $p_user_id );
		}
	
		# When the password is changed, invalidate the cookie to expire sessions that
		# may be active on all browsers.
		$c_cookie_string = \Core\Auth::generate_unique_cookie_string();
	
		$c_password = \Core\Auth::process_plain_password( $p_password );
	
		$t_query = 'UPDATE {user}
					  SET password=' . \Core\Database::param() . ', cookie_string=' . \Core\Database::param() . '
					  WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_password, $c_cookie_string, (int)$p_user_id ) );
	
		return true;
	}
	
	/**
	 * Set the user's email to the given string after checking that it is a valid email
	 * @param integer $p_user_id A valid user identifier.
	 * @param string  $p_email   An email address to set.
	 * @return boolean
	 */
	static function set_email( $p_user_id, $p_email ) {
		\Core\Email::ensure_valid( $p_email );
	
		return \Core\User::set_field( $p_user_id, 'email', $p_email );
	}
	
	/**
	 * Set the user's realname to the given string after checking validity
	 * @param integer $p_user_id  A valid user identifier.
	 * @param string  $p_realname A realname to set.
	 * @return boolean
	 */
	static function set_realname( $p_user_id, $p_realname ) {
		return \Core\User::set_field( $p_user_id, 'realname', $p_realname );
	}
	
	/**
	 * Set the user's username to the given string after checking that it is valid
	 * @param integer $p_user_id  A valid user identifier.
	 * @param string  $p_username A valid username to set.
	 * @return boolean
	 */
	static function set_name( $p_user_id, $p_username ) {
		\Core\User::ensure_name_valid( $p_username );
		\Core\User::ensure_name_unique( $p_username );
	
		return \Core\User::set_field( $p_user_id, 'username', $p_username );
	}
	
	/**
	 * Reset the user's password
	 *  Take into account the 'send_reset_password' setting
	 *   - if it is ON, generate a random password and send an email
	 *      (unless the second parameter is false)
	 *   - if it is OFF, set the password to blank
	 *  Return false if the user is protected, true if the password was
	 *   successfully reset
	 *
	 * @param integer $p_user_id    A valid user identifier.
	 * @param boolean $p_send_email Whether to send confirmation email.
	 * @return boolean
	 */
	static function reset_password( $p_user_id, $p_send_email = true ) {
		$t_protected = \Core\User::get_field( $p_user_id, 'protected' );
	
		# Go with random password and email it to the user
		if( ON == $t_protected ) {
			return false;
		}
	
		# @@@ do we want to force blank password instead of random if
		#      email notifications are turned off?
		#     How would we indicate that we had done this with a return value?
		#     Should we just have two functions? (user_reset_password_random()
		#     and \Core\User::reset_password() )?
		if( ( ON == \Core\Config::mantis_get( 'send_reset_password' ) ) && ( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
			$t_email = \Core\User::get_field( $p_user_id, 'email' );
			if( \Core\Utility::is_blank( $t_email ) ) {
				trigger_error( ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED, ERROR );
			}
	
			# Create random password
			$t_password = \Core\Auth::generate_random_password();
			$t_password2 = \Core\Auth::process_plain_password( $t_password );
	
			\Core\User::set_field( $p_user_id, 'password', $t_password2 );
	
			# Send notification email
			if( $p_send_email ) {
				$t_confirm_hash = \Core\Auth::generate_confirm_hash( $p_user_id );
				\Core\Email::send_confirm_hash_url( $p_user_id, $t_confirm_hash );
			}
		} else {
			# use blank password, no emailing
			$t_password = \Core\Auth::process_plain_password( '' );
			\Core\User::set_field( $p_user_id, 'password', $t_password );
	
			# reset the failed login count because in this mode there is no emailing
			\Core\User::reset_failed_login_count_to_zero( $p_user_id );
		}
	
		return true;
	}

}