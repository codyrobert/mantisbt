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
 * Prepare API
 *
 * Handles preparation of strings prior to be printed or stored.
 *
 * @package CoreAPI
 * @subpackage PrepareAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses version_api.php
 */



class Prepare
{
	
	/**
	 * return the mailto: href string link
	 * @param string $p_email Email address to prepare.
	 * @param string $p_text  Display text for the hyperlink.
	 * @return string
	 */
	static function email_link( $p_email, $p_text ) {
		if( !\Core\Access::has_project_level( \Core\Config::mantis_get( 'show_user_email_threshold' ) ) ) {
			return \Core\String::display_line( $p_text );
		}
	
		# If we apply \Core\String::url() to the whole mailto: link then the @
		#  gets turned into a %40 and you can't right click in browsers to
		#  do Copy Email Address.
		$t_mailto = \Core\String::attribute( 'mailto:' . $p_email );
		$p_text = \Core\String::display_line( $p_text );
	
		return '<a href="' . $t_mailto . '">' . $p_text . '</a>';
	}
	
	/**
	 * prepares the name of the user given the id.  also makes it an email link.
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function user_name( $p_user_id ) {
		# Catch a user_id of NO_USER (like when a handler hasn't been assigned)
		if( NO_USER == $p_user_id ) {
			return '';
		}
	
		$t_username = \Core\User::get_name( $p_user_id );
		$t_username = \Core\String::display_line( $t_username );
		if( \Core\User::exists( $p_user_id ) && \Core\User::get_field( $p_user_id, 'enabled' ) ) {
			return '<a class="user" href="' . \Core\String::sanitize_url( 'view_user_page.php?id=' . $p_user_id, true ) . '">' . $t_username . '</a>';
		} else {
			return '<del class="user">' . $t_username . '</del>';
		}
	}
	
	/**
	 * A function that prepares the version string for outputting to the user on view / print issue pages.
	 * This function would add the version date, if appropriate.
	 *
	 * @param integer $p_project_id The project id.
	 * @param integer $p_version_id The version id.  If false then this method will return an empty string.
	 * @return string The formatted version string.
	 */
	static function version_string( $p_project_id, $p_version_id ) {
		if( $p_version_id === false ) {
			return '';
		}
	
		$t_version_text = \Core\Version::full_name( $p_version_id, null, $p_project_id );
	
		if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'show_version_dates_threshold' ), $p_project_id ) ) {
			$t_short_date_format = \Core\Config::mantis_get( 'short_date_format' );
	
			$t_version = \Core\Version::get( $p_version_id );
			$t_version_text .= ' (' . date( $t_short_date_format, $t_version->date_order ) . ')';
		}
	
		return $t_version_text;
	}

}
