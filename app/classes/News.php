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
 * News API
 *
 * @package CoreAPI
 * @subpackage NewsAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */



class News
{
	
	/**
	 * Add a news item
	 *
	 * @param integer $p_project_id   A project identifier.
	 * @param integer $p_poster_id    The user id of poster.
	 * @param integer $p_view_state   View state.
	 * @param boolean $p_announcement Whether article is an announcement.
	 * @param string  $p_headline     News Headline.
	 * @param string  $p_body         News Body.
	 * @return integer news article id
	 */
	static function create( $p_project_id, $p_poster_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
		if( \Core\Utility::is_blank( $p_headline ) ) {
			\Core\Error::parameters( \Core\Lang::get( 'headline' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
	
		if( \Core\Utility::is_blank( $p_body ) ) {
			\Core\Error::parameters( \Core\Lang::get( 'body' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
	
		$t_query = 'INSERT INTO {news}
		    		  ( project_id, poster_id, date_posted, last_modified,
		    		    view_state, announcement, headline, body )
					VALUES
					    ( ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . ',
					      ' . \Core\Database::param() . '
						)';
		\Core\Database::query( $t_query, array( (int)$p_project_id, (int)$p_poster_id, \Core\Database::now(), \Core\Database::now(), (int)$p_view_state, $p_announcement, $p_headline, $p_body ) );
	
		$t_news_id = \Core\Database::insert_id( \Core\Database::get_table( 'news' ) );
	
		return $t_news_id;
	}
	
	/**
	 * Delete the news entry
	 *
	 * @param integer $p_news_id A news article identifier.
	 * @return void
	 */
	static function delete( $p_news_id ) {
		$t_query = 'DELETE FROM {news} WHERE id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( $p_news_id ) );
	}
	
	/**
	 * Delete the news entry
	 *
	 * @param integer $p_project_id A project identifier.
	 * @return void
	 */
	static function delete_all( $p_project_id ) {
		$t_query = 'DELETE FROM {news} WHERE project_id=' . \Core\Database::param();
		\Core\Database::query( $t_query, array( (int)$p_project_id ) );
	}
	
	/**
	 * Update news item
	 *
	 * @param integer $p_news_id      A news article identifier.
	 * @param integer $p_project_id   A project identifier.
	 * @param integer $p_view_state   View state.
	 * @param boolean $p_announcement Whether article is an announcement.
	 * @param string  $p_headline     News headline.
	 * @param string  $p_body         News body.
	 * @return void
	 */
	static function update( $p_news_id, $p_project_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
		if( \Core\Utility::is_blank( $p_headline ) ) {
			\Core\Error::parameters( \Core\Lang::get( 'headline' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
	
		if( \Core\Utility::is_blank( $p_body ) ) {
			\Core\Error::parameters( \Core\Lang::get( 'body' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
	
		# Update entry
		$t_query = 'UPDATE {news}
					  SET view_state=' . \Core\Database::param() . ',
						announcement=' . \Core\Database::param() . ',
						headline=' . \Core\Database::param() . ',
						body=' . \Core\Database::param() . ',
						project_id=' . \Core\Database::param() . ',
						last_modified= ' . \Core\Database::param() . '
					  WHERE id=' . \Core\Database::param();
	
		\Core\Database::query( $t_query, array( $p_view_state, $p_announcement, $p_headline, $p_body, $p_project_id, \Core\Database::now(), $p_news_id ) );
	}
	
	/**
	 * Selects the news item associated with the specified id
	 *
	 * @param integer $p_news_id A news article identifier.
	 * @return array news article
	 */
	static function get_row( $p_news_id ) {
		$t_query = 'SELECT * FROM {news} WHERE id=' . \Core\Database::param();
		$t_result = \Core\Database::query( $t_query, array( $p_news_id ) );
	
		$t_row = \Core\Database::fetch_array( $t_result );
	
		if( !$t_row ) {
			trigger_error( ERROR_NEWS_NOT_FOUND, ERROR );
		} else {
			return $t_row;
		}
	}
	
	/**
	 * get news count (selected project plus site wide posts)
	 *
	 * @param integer $p_project_id A project identifier.
	 * @param boolean $p_global     Whether this is site wide news i.e. ALL_PROJECTS.
	 * @return int news count
	 */
	static function get_count( $p_project_id, $p_global = true ) {
		$t_project_where = \Core\Helper::project_specific_where( $p_project_id );
	
		$t_query = 'SELECT COUNT(*) FROM {news} WHERE ' . $t_project_where;
	
		if( $p_global ) {
			$t_query .= ' OR project_id=' . ALL_PROJECTS;
		}
	
		$t_result = \Core\Database::query( $t_query );
	
		return \Core\Database::result( $t_result, 0 );
	}
	
	/**
	 * get news items (selected project plus site wide posts)
	 *
	 * @param integer $p_project_id A project identifier.
	 * @param boolean $p_global     Whether this is site wide news i.e. ALL_PROJECTS.
	 * @return array Array of news articles
	 */
	static function get_rows( $p_project_id, $p_global = true ) {
		$t_projects = \Core\Current_User::get_all_accessible_subprojects( $p_project_id );
		$t_projects[] = (int)$p_project_id;
	
		if( $p_global && ALL_PROJECTS != $p_project_id ) {
			$t_projects[] = ALL_PROJECTS;
		}
	
		$t_query = 'SELECT * FROM {news}';
	
		if( 1 == count( $t_projects ) ) {
			$c_project_id = $t_projects[0];
			$t_query .= ' WHERE project_id=\'$c_project_id\'';
		} else {
			$t_query .= ' WHERE project_id IN (' . join( $t_projects, ',' ) . ')';
		}
	
		$t_query .= ' ORDER BY date_posted DESC';
	
		$t_result = \Core\Database::query( $t_query, array() );
	
		$t_rows = array();
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			array_push( $t_rows, $t_row );
		}
	
		return $t_rows;
	}
	
	/**
	 * Get field from news item
	 *
	 * @param integer $p_news_id    A news article identifier.
	 * @param string  $p_field_name The field name to retrieve.
	 * @return mixed
	 */
	static function get_field( $p_news_id, $p_field_name ) {
		$t_row = \Core\News::get_row( $p_news_id );
		return( $t_row[$p_field_name] );
	}
	
	/**
	 * Check if the specified news item is private
	 *
	 * @param integer $p_news_id A news article identifier.
	 * @return boolean
	 */
	static function is_private( $p_news_id ) {
		return( \Core\News::get_field( $p_news_id, 'view_state' ) == VS_PRIVATE );
	}
	
	/**
	 * Gets a limited set of news rows to be viewed on one page based on the criteria
	 * defined in the configuration file.
	 *
	 * @param integer $p_offset     Offset.
	 * @param integer $p_project_id A project identifier.
	 * @return array
	 */
	static function get_limited_rows( $p_offset, $p_project_id = null ) {
		if( $p_project_id === null ) {
			$p_project_id = \Core\Helper::get_current_project();
		}
	
		$c_offset = (int)$p_offset;
	
		$t_projects = \Core\Current_User::get_all_accessible_subprojects( $p_project_id );
		$t_projects[] = (int)$p_project_id;
		if( ALL_PROJECTS != $p_project_id ) {
			$t_projects[] = ALL_PROJECTS;
		}
	
		$t_news_view_limit = \Core\Config::mantis_get( 'news_view_limit' );
		$t_news_view_limit_days = \Core\Config::mantis_get( 'news_view_limit_days' ) * SECONDS_PER_DAY;
	
		switch( \Core\Config::mantis_get( 'news_limit_method' ) ) {
			case 0:
				# BY_LIMIT - Select the news posts
				$t_query = 'SELECT * FROM {news}';
	
				if( 1 == count( $t_projects ) ) {
					$c_project_id = $t_projects[0];
					$t_query .= ' WHERE project_id=' . \Core\Database::param();
					$t_params = array( $c_project_id );
				} else {
					$t_query .= ' WHERE project_id IN (' . join( $t_projects, ',' ) . ')';
					$t_params = null;
				}
	
				$t_query .= ' ORDER BY announcement DESC, id DESC';
				$t_result = \Core\Database::query( $t_query, $t_params, $t_news_view_limit, $c_offset );
				break;
			case 1:
				# BY_DATE - Select the news posts
				$t_query = 'SELECT * FROM {news} WHERE
							( ' . \Core\Database::helper_compare_time( \Core\Database::param(), '<', 'date_posted', $t_news_view_limit_days ) . '
							 OR announcement = ' . \Core\Database::param() . ' ) ';
				$t_params = array(
					\Core\Database::now(),
					1,
				);
				if( 1 == count( $t_projects ) ) {
					$c_project_id = $t_projects[0];
					$t_query .= ' AND project_id=' . \Core\Database::param();
					$t_params[] = $c_project_id;
				} else {
					$t_query .= ' AND project_id IN (' . join( $t_projects, ',' ) . ')';
				}
				$t_query .= ' ORDER BY announcement DESC, id DESC';
				$t_result = \Core\Database::query( $t_query, $t_params, $t_news_view_limit, $c_offset );
				break;
		}
	
		$t_rows = array();
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			array_push( $t_rows, $t_row );
		}
	
		return $t_rows;
	}
	
	/**
	 * Checks if the news feature is enabled or not.
	 * true: enabled, otherwise false.
	 * @return boolean
	 */
	static function is_enabled() {
		return \Core\Config::mantis_get( 'news_enabled' ) == ON;
	}
	
	/**
	 * Ensures that the news feature is enabled, otherwise generates an access denied error.
	 * @return void
	 */
	static function ensure_enabled() {
		if( !\Core\News::is_enabled() ) {
			\Core\Access::denied();
		}
	}

}