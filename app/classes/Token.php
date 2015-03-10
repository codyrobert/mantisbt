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
 * Tokens API
 *
 * This implements temporary storage of strings.
 * DB schema: id, type, owner, timestamp, value
 *
 * @package CoreAPI
 * @subpackage TokensAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 */



class Token
{

	/**
	 * Check if a token exists.
	 * @param integer $p_token_id A token identifier.
	 * @return boolean True if token exists
	 */
	static function exists( $p_token_id ) {
		$t_query = 'SELECT id FROM {tokens} WHERE id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_token_id ), 1 );
	
		$t_row = \Core\Database::fetch_array( $t_result );
		if( $t_row ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Make sure a token exists.
	 * @param integer $p_token_id A token identifier.
	 * @return boolean True if token exists
	 */
	static function ensure_exists( $p_token_id ) {
		if( !\Core\Token::exists( $p_token_id ) ) {
			trigger_error( ERROR_TOKEN_NOT_FOUND, ERROR );
		}
	
		return true;
	}
	
	/**
	 * Get a token's information
	 * @param integer $p_type    The token type to retrieve.
	 * @param integer $p_user_id A valid user identifier.
	 * @return array Token row
	 */
	static function get( $p_type, $p_user_id = null ) {
		\Core\Token::purge_expired_once();
	
		$c_type = (int)$p_type;
		$c_user_id = (int)( $p_user_id == null ? \Core\Auth::get_current_user_id() : $p_user_id );
	
		$t_query = 'SELECT * FROM {tokens} WHERE type=' . \Core\Database::param() . ' AND owner=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $c_type, $c_user_id ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
		if( $t_row ) {
			return $t_row;
		}
	
		return null;
	}
	
	/**
	 * Get a token's value or null if not found
	 * @param integer $p_type    The token type to retrieve.
	 * @param integer $p_user_id The user identifier (null for current user).
	 * @return array Token row
	 */
	static function get_value( $p_type, $p_user_id = null ) {
		$t_token = \Core\Token::get( $p_type, $p_user_id );
	
		if( null !== $t_token ) {
			return $t_token['value'];
		}
	
		return null;
	}
	
	/**
	 * Create or update a token's value and expiration
	 * @param integer $p_type    The token type.
	 * @param string  $p_value   The token value.
	 * @param integer $p_expiry  Token expiration in seconds.
	 * @param integer $p_user_id An user identifier.
	 * @return int Token ID
	 */
	static function set( $p_type, $p_value, $p_expiry = TOKEN_EXPIRY, $p_user_id = null ) {
		$t_token = \Core\Token::get( $p_type, $p_user_id );
		if( $t_token === null ) {
			return \Core\Token::create( $p_type, $p_value, $p_expiry, $p_user_id );
		}
	
		\Core\Token::update( $t_token['id'], $p_value, $p_expiry );
		return $t_token['id'];
	}
	
	/**
	 * Touch a token to update its expiration time.
	 * @param integer $p_token_id A token identifier.
	 * @param integer $p_expiry   Token expiration in seconds.
	 * @return void
	 */
	static function touch( $p_token_id, $p_expiry = TOKEN_EXPIRY ) {
		\Core\Token::ensure_exists( $p_token_id );
	
		$c_token_expiry = time() + $p_expiry;
		$t_query = 'UPDATE {tokens} SET expiry=' . \Core\Database::param() . ' WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_token_expiry, $p_token_id ) );
	}
	
	/**
	 * Delete a token.
	 * @param integer $p_type    The token type.
	 * @param integer $p_user_id An user identifier or null for current logged in user.
	 * @return void
	 */
	static function delete( $p_type, $p_user_id = null ) {
		if( $p_user_id == null ) {
			$c_user_id = \Core\Auth::get_current_user_id();
		} else {
			$c_user_id = (int)$p_user_id;
		}
	
		$t_query = 'DELETE FROM {tokens} WHERE type=' . \Core\Database::param() . ' AND owner=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_type, $c_user_id ) );
	}
	
	/**
	 * Delete all tokens owned by a specified user.
	 * @param integer $p_user_id An user identifier or null for current logged in user.
	 * @return void
	 */
	static function delete_by_owner( $p_user_id = null ) {
		if( $p_user_id == null ) {
			$c_user_id = \Core\Auth::get_current_user_id();
		} else {
			$c_user_id = (int)$p_user_id;
		}
	
		$t_query = 'DELETE FROM {tokens} WHERE owner=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $c_user_id ) );
	}
	
	/**
	 * Create a token.
	 * @param integer $p_type    The token type.
	 * @param string  $p_value   The token value.
	 * @param integer $p_expiry  Token expiration in seconds.
	 * @param integer $p_user_id The user identifier to link the token to.
	 * @return int Token ID
	 */
	static function create( $p_type, $p_value, $p_expiry = TOKEN_EXPIRY, $p_user_id = null ) {
		if( $p_user_id == null ) {
			$c_user_id = \Core\Auth::get_current_user_id();
		} else {
			$c_user_id = (int)$p_user_id;
		}
	
		$c_type = (int)$p_type;
		$c_timestamp = \Core\Database::now();
		$c_expiry = time() + $p_expiry;
	
		$t_query = 'INSERT INTO {tokens}
						( type, value, timestamp, expiry, owner )
						VALUES ( ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ', ' . \Core\Database::param() . ' )';
		\Core\Database::query( $t_query, array( $c_type, (string)$p_value, $c_timestamp, $c_expiry, $c_user_id ) );
		return \Core\Database::insert_id( \Core\Database::get_table( 'tokens' ) );
	}
	
	/**
	 * Update a token
	 * @param integer $p_token_id A token identifier.
	 * @param string  $p_value    The new token value.
	 * @param integer $p_expiry   Token expiration in seconds.
	 * @return boolean always true.
	 */
	static function update( $p_token_id, $p_value, $p_expiry = TOKEN_EXPIRY ) {
		\Core\Token::ensure_exists( $p_token_id );
		$c_token_id = (int)$p_token_id;
		$c_expiry = time() + $p_expiry;
	
		$t_query = 'UPDATE {tokens}
						SET value=' . \Core\Database::param() . ', expiry=' . \Core\Database::param() . '
						WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (string)$p_value, $c_expiry, $c_token_id ) );
	
		return true;
	}
	
	/**
	 * Delete all tokens of a specified type.
	 * @param integer $p_token_type The token type.
	 * @return boolean always true.
	 */
	static function delete_by_type( $p_token_type ) {
		$t_query = 'DELETE FROM {tokens} WHERE type=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_token_type ) );
	
		return true;
	}
	
	/**
	 * Purge all expired tokens.
	 * @param integer $p_token_type The token type.
	 * @return boolean always true.
	 */
	static function purge_expired( $p_token_type = null ) {
		global $g_tokens_purged;
	
		$t_query = 'DELETE FROM {tokens} WHERE ' . \Core\Database::param() . ' > expiry';
		if( !is_null( $p_token_type ) ) {
			$t_query .= ' AND type=' . \Core\Database::param();
			\Core\Database::query( $t_query, array( \Core\Database::now(), (int)$p_token_type ) );
		} else {
			\Core\Database::query( $t_query, array( \Core\Database::now() ) );
		}
	
		$g_tokens_purged = true;
	
		return true;
	}
	
	/**
	 * Purge all expired tokens only once per session.
	 * @return void
	 */
	static function purge_expired_once() {
		global $g_tokens_purged;
		if( !$g_tokens_purged ) {
			\Core\Token::purge_expired();
		}
	}

}
