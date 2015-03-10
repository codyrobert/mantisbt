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
 * Sponsorship API
 *
 * @package CoreAPI
 * @subpackage SponsorshipAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses history_api.php
 */



class Sponsorship
{

	/**
	 * Cache a sponsorship row if necessary and return the cached copy
	 * If the second parameter is true (default), trigger an error
	 * if the sponsorship can't be found.  If the second parameter is
	 * false, return false if the sponsorship can't be found.
	 * @param integer $p_sponsorship_id The sponsorship identifier to retrieve and cache.
	 * @param boolean $p_trigger_errors Whether to trigger an error if the identifier is not found.
	 * @return array|boolean
	 */
	static function cache_row( $p_sponsorship_id, $p_trigger_errors = true ) {
		global $g_cache_sponsorships;
	
		$c_sponsorship_id = (int)$p_sponsorship_id;
	
		if( isset( $g_cache_sponsorships[$c_sponsorship_id] ) ) {
			return $g_cache_sponsorships[$c_sponsorship_id];
		}
	
		$t_query = 'SELECT * FROM {sponsorship} WHERE id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $c_sponsorship_id ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			$g_cache_sponsorships[$c_sponsorship_id] = false;
	
			if( $p_trigger_errors ) {
				\Core\Error::parameters( $p_sponsorship_id );
				trigger_error( ERROR_SPONSORSHIP_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}
	
		$g_cache_sponsorships[$c_sponsorship_id] = $t_row;
	
		return $t_row;
	}
	
	/**
	 * Clear the sponsorship cache (or just the given id if specified)
	 * @param integer $p_sponsorship_id The sponsorship identifier to clear.
	 * @return void
	 */
	static function clear_cache( $p_sponsorship_id = null ) {
		global $g_cache_sponsorships;
	
		if( $p_sponsorship_id === null ) {
			$g_cache_sponsorships = array();
		} else {
			unset( $g_cache_sponsorships[(int)$p_sponsorship_id] );
		}
	}
	
	/**
	 * check to see if sponsorship exists by id
	 * return true if it does, false otherwise
	 * @param integer $p_sponsorship_id The sponsorship identifier to check.
	 * @return boolean
	 */
	static function exists( $p_sponsorship_id ) {
		return \Core\Sponsorship::cache_row( $p_sponsorship_id, false ) !== false;
	}
	
	/**
	 * return false if not found
	 * otherwise returns sponsorship id
	 * @param integer $p_bug_id  A valid bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @return integer|false
	 */
	static function get_id( $p_bug_id, $p_user_id = null ) {
		if( $p_user_id === null ) {
			$c_user_id = \Core\Auth::get_current_user_id();
		} else {
			$c_user_id = (int)$p_user_id;
		}
	
		$t_query = 'SELECT id FROM {sponsorship} WHERE bug_id=' . \Core\Database::param() . ' AND user_id = ' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( (int)$p_bug_id, $c_user_id ), 1 );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			return false;
		}
	
		return (integer)$t_row['id'];
	}
	
	/**
	 * get information about a sponsorship given its id
	 * @param integer $p_sponsorship_id The sponsorship identifier to retrieve.
	 * @return array
	 */
	static function get( $p_sponsorship_id ) {
		$t_row = \Core\Sponsorship::cache_row( $p_sponsorship_id );
	
		$t_sponsorship_data = new SponsorShipData;
		$t_row_keys = array_keys( $t_row );
		$t_vars = get_object_vars( $t_sponsorship_data );
	
		# Check each variable in the class
		foreach( $t_vars as $t_var => $t_val ) {
			# If we got a field from the DB with the same name
			if( in_array( $t_var, $t_row_keys, true ) ) {
				# Store that value in the object
				$t_sponsorship_data->$t_var = $t_row[$t_var];
			}
		}
	
		return $t_sponsorship_data;
	}
	
	/**
	 * Return an array of Sponsorships associated with the specified bug id
	 * @param integer $p_bug_id The bug identifier to retrieve.
	 * @return array
	 */
	static function get_all_ids( $p_bug_id ) {
		global $g_cache_sponsorships;
		static $s_cache_sponsorship_bug_ids = array();
	
		$c_bug_id = (int)$p_bug_id;
	
		if( isset( $s_cache_sponsorship_bug_ids[$c_bug_id] ) ) {
			return $s_cache_sponsorship_bug_ids[$c_bug_id];
		}
	
		$t_query = 'SELECT * FROM {sponsorship} WHERE bug_id = ' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $c_bug_id ) );
	
		$t_sponsorship_ids = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_sponsorship_ids[] = $t_row['id'];
			$g_cache_sponsorships[(int)$t_row['id']] = $t_row;
		}
	
		$s_cache_sponsorship_bug_ids[$c_bug_id] = $t_sponsorship_ids;
	
		return $t_sponsorship_ids;
	}
	
	/**
	 * Get the amount of sponsorships for the specified id(s)
	 * handles the case where $p_sponsorship_id is an array or an id.
	 * @param array|integer $p_sponsorship_id The sponsorship identifier(s) to check.
	 * @return integer
	 */
	static function get_amount( $p_sponsorship_id ) {
		if( is_array( $p_sponsorship_id ) ) {
			$t_total = 0;
	
			foreach( $p_sponsorship_id as $t_id ) {
				$t_total += \Core\Sponsorship::get_amount( $t_id );
			}
	
			return $t_total;
		} else {
			$t_sponsorship = \Core\Sponsorship::get( $p_sponsorship_id );
			return $t_sponsorship->amount;
		}
	}
	
	/**
	 * Return the currency used for all sponsorships
	 * @return string
	 */
	static function get_currency() {
		return \Core\Config::mantis_get( 'sponsorship_currency' );
	}
	
	/**
	 * This function should return the string in a globalized format.
	 * @param integer $p_amount A numeric value represent the amount to format.
	 * @return string
	 * @todo add some currency formatting in the future
	 */
	static function format_amount( $p_amount ) {
		$t_currency = \Core\Sponsorship::get_currency();
		return $t_currency . ' ' . $p_amount;
	}
	
	/**
	 * Update bug to reflect sponsorship change
	 * This is to be called after adding/updating/deleting sponsorships
	 * @param integer $p_bug_id The bug identifier to update.
	 * @return void
	 */
	static function update_bug( $p_bug_id ) {
		$t_total_amount = \Core\Sponsorship::get_amount( \Core\Sponsorship::get_all_ids( $p_bug_id ) );
		\Core\Bug::set_field( $p_bug_id, 'sponsorship_total', $t_total_amount );
		\Core\Bug::update_date( $p_bug_id );
	}
	
	/**
	 * if sponsorship contains a non-zero id, then update the corresponding record.
	 * if sponsorship contains a zero id, search for bug_id/user_id, if found, then update the entry
	 * otherwise add a new entry
	 * @param SponsorshipData $p_sponsorship The sponsorship data object to set.
	 * @return integer
	 */
	static function set( \Core\SponsorshipData $p_sponsorship ) {
		$t_min_sponsorship = \Core\Config::mantis_get( 'minimum_sponsorship_amount' );
		if( $p_sponsorship->amount < $t_min_sponsorship ) {
			\Core\Error::parameters( $p_sponsorship->amount, $t_min_sponsorship );
			trigger_error( ERROR_SPONSORSHIP_AMOUNT_TOO_LOW, ERROR );
		}
	
		# if id == 0, check if the specified user is already sponsoring the bug, if so, overwrite
		if( $p_sponsorship->id == 0 ) {
			$t_sponsorship_id = \Core\Sponsorship::get_id( $p_sponsorship->bug_id, $p_sponsorship->user_id );
			if( $t_sponsorship_id !== false ) {
				$p_sponsorship->id = $t_sponsorship_id;
			}
		}
	
		$c_id = (int)$p_sponsorship->id;
		$c_bug_id = (int)$p_sponsorship->bug_id;
		$c_user_id = (int)$p_sponsorship->user_id;
		$c_amount = (int)$p_sponsorship->amount;
		$c_logo = $p_sponsorship->logo;
		$c_url = $p_sponsorship->url;
		$c_now = \Core\Database::now();
	
		# if new sponsorship
		if( $c_id == 0 ) {
			# Insert
			$t_query = 'INSERT INTO {sponsorship}
					    ( bug_id, user_id, amount, logo, url, date_submitted, last_updated )
					  VALUES
					    (' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ',' . \Core\Database::param() . ')';
	
			\Core\Database::query( $t_query, array( $c_bug_id, $c_user_id, $c_amount, $c_logo, $c_url, $c_now, $c_now ) );
	
			$t_sponsorship_id = \Core\Database::insert_id( \Core\Database::get_table( 'sponsorship' ) );
	
			\Core\History::log_event_special( $c_bug_id, BUG_ADD_SPONSORSHIP, $c_user_id, $c_amount );
		} else {
			$t_old_amount = \Core\Sponsorship::get_amount( $c_id );
			$t_sponsorship_id = $c_id;
	
			if( $t_old_amount == $c_amount ) {
				return $t_sponsorship_id;
			}
	
			# Update
			$t_query = 'UPDATE {sponsorship}
						SET	bug_id = ' . \Core\Database::param() . ',
							user_id = ' . \Core\Database::param() . ',
							amount = ' . \Core\Database::param() . ',
							logo = ' . \Core\Database::param() . ',
							url = ' . \Core\Database::param() . ',
							last_updated = ' . \Core\Database::param() . '
						WHERE	id = ' . \Core\Database::param();
	
			\Core\Sponsorship::clear_cache( $c_id );
	
			\Core\Database::query( $t_query, array( $c_bug_id, $c_user_id, $c_amount, $c_logo, $c_url, $c_now, $c_id ) );
	
			\Core\History::log_event_special( $c_bug_id, BUG_UPDATE_SPONSORSHIP, $c_user_id, $c_amount );
		}
	
		\Core\Sponsorship::update_bug( $c_bug_id );
		\Core\Bug::monitor( $c_bug_id, $c_user_id );
	
		if( $c_id == 0 ) {
			\Core\Email::generic( $c_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_added' );
		} else {
			\Core\Email::generic( $c_bug_id, 'sponsor', 'email_notification_title_for_action_sponsorship_updated' );
		}
	
		return $t_sponsorship_id;
	}
	
	/**
	 * delete all sponsorships of a bug
	 * @param integer $p_bug_id The bug identifier to delete sponsorships for.
	 * @return void
	 */
	static function delete_all( $p_bug_id ) {
		$t_query = 'DELETE FROM {sponsorship} WHERE bug_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_bug_id ) );
	
		\Core\Sponsorship::clear_cache( );
	}
	
	/**
	 * delete a sponsorship given its id
	 * id can be an array of ids or just an id.
	 * @param integer $p_sponsorship_id The sponsorship identifier to delete.
	 * @return void
	 */
	static function delete( $p_sponsorship_id ) {
		# handle the case of array of ids
		if( is_array( $p_sponsorship_id ) ) {
			foreach( $p_sponsorship_id as $t_id ) {
				\Core\Sponsorship::delete( $t_id );
			}
			return;
		}
	
		$t_sponsorship = \Core\Sponsorship::get( $p_sponsorship_id );
	
		# Delete the bug entry
		$t_query = 'DELETE FROM {sponsorship} WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_sponsorship_id ) );
	
		\Core\Sponsorship::clear_cache( $p_sponsorship_id );
	
		\Core\History::log_event_special( $t_sponsorship->bug_id, BUG_DELETE_SPONSORSHIP, $t_sponsorship->user_id, $t_sponsorship->amount );
		\Core\Sponsorship::update_bug( $t_sponsorship->bug_id );
	
		\Core\Email::generic( $t_sponsorship->bug_id, 'sponsor', 'A sponsorship of the following issue was withdrawn.' );
	}
	
	/**
	 * updates the paid field
	 * @param integer $p_sponsorship_id The sponsorship identifier to update.
	 * @param integer $p_paid           The value to set to paid database field to.
	 * @return boolean
	 */
	static function update_paid( $p_sponsorship_id, $p_paid ) {
		$t_sponsorship = \Core\Sponsorship::get( $p_sponsorship_id );
	
		$t_query = 'UPDATE {sponsorship} SET last_updated=' . \Core\Database::param() . ', paid=' . \Core\Database::param() . ' WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( \Core\Database::now(), (int)$p_paid, (int)$p_sponsorship_id ) );
	
		\Core\History::log_event_special( $t_sponsorship->bug_id, BUG_PAID_SPONSORSHIP, $t_sponsorship->user_id, $p_paid );
		\Core\Sponsorship::clear_cache( $p_sponsorship_id );
	
		return true;
	}
	
	/**
	 * updates the last_updated field
	 * @param integer $p_sponsorship_id The sponsorship identifier to update.
	 * @return boolean
	 */
	static function update_date( $p_sponsorship_id ) {
		$t_query = 'UPDATE {sponsorship} SET last_updated=' . \Core\Database::param() . ' WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( \Core\Database::now(), (int)$p_sponsorship_id ) );
	
		\Core\Sponsorship::clear_cache( $p_sponsorship_id );
	
		return true;
	}


}