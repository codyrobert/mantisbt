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
 * XMLHttpRequest API
 *
 * @package CoreAPI
 * @subpackage XMLHttpRequestAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses profile_api.php
 */



class XMLHTTPRequest
{

	/**
	 * Filter a set of strings by finding strings that start with a case-insensitive prefix.
	 * @param array  $p_set    An array of strings to search through.
	 * @param string $p_prefix The prefix to filter by.
	 * @return array An array of strings which match the supplied prefix.
	 */
	static function filter_by_prefix( array $p_set, $p_prefix ) {
		$t_matches = array();
		foreach ( $p_set as $p_item ) {
			if( utf8_strtolower( utf8_substr( $p_item, 0, utf8_strlen( $p_prefix ) ) ) === utf8_strtolower( $p_prefix ) ) {
				$t_matches[] = $p_item;
			}
		}
		return $t_matches;
	}
	
	/**
	 * Outputs a serialized list of platforms starting with the prefix specified in the $_POST
	 * @return void
	 * @access public
	 */
	static function platform_get_with_prefix() {
		$f_platform = \Core\GPC::get_string( 'platform' );
	
		$t_unique_entries = \Core\Profile::get_field_all_for_user( 'platform' );
		$t_matching_entries = \Core\XMLHTTPRequest::filter_by_prefix( $t_unique_entries, $f_platform );
	
		echo json_encode( $t_matching_entries );
	}
	
	/**
	 * Outputs a serialized list of Operating Systems starting with the prefix specified in the $_POST
	 * @return void
	 * @access public
	 */
	static function os_get_with_prefix() {
		$f_os = \Core\GPC::get_string( 'os' );
	
		$t_unique_entries = \Core\Profile::get_field_all_for_user( 'os' );
		$t_matching_entries = \Core\XMLHTTPRequest::filter_by_prefix( $t_unique_entries, $f_os );
	
		echo json_encode( $t_matching_entries );
	}
	
	/**
	 * Outputs a serialized list of Operating System Versions starting with the prefix specified in the $_POST
	 * @return void
	 * @access public
	 */
	static function os_build_get_with_prefix() {
		$f_os_build = \Core\GPC::get_string( 'os_build' );
	
		$t_unique_entries = \Core\Profile::get_field_all_for_user( 'os_build' );
		$t_matching_entries = \Core\XMLHTTPRequest::filter_by_prefix( $t_unique_entries, $f_os_build );
	
		echo json_encode( $t_matching_entries );
	}

}