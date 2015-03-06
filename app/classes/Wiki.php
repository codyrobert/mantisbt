<?php
namespace Flickerbox;


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
 * Wiki API
 *
 * @package CoreAPI
 * @subpackage WikiAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses plugin_api.php
 */

require_api( 'config_api.php' );
require_api( 'event_api.php' );
require_api( 'plugin_api.php' );


class Wiki
{

	/**
	 * Returns whether wiki functionality is enabled
	 * @return boolean indicating whether wiki is enabled
	 * @access public
	 */
	static function enabled() {
		return( config_get_global( 'wiki_enable' ) == ON );
	}
	
	/**
	 * Initialise wiki engine
	 * @return void
	 * @access public
	 */
	static function init() {
		if( \Flickerbox\Wiki::enabled() ) {
	
			# handle legacy style wiki integration
			require_once( config_get_global( 'class_path' ) . 'MantisCoreWikiPlugin.class.php' );
			switch( config_get_global( 'wiki_engine' ) ) {
				case 'dokuwiki':
					plugin_child( 'MantisCoreDokuwiki' );
					break;
				case 'mediawiki':
					plugin_child( 'MantisCoreMediaWiki' );
					break;
				case 'twiki':
					plugin_child( 'MantisCoreTwiki' );
					break;
				case 'WikkaWiki':
					plugin_child( 'MantisCoreWikkaWiki' );
					break;
				case 'xwiki':
					plugin_child( 'MantisCoreXwiki' );
					break;
			}
	
			if( is_null( event_signal( 'EVENT_WIKI_INIT' ) ) ) {
				config_set_global( 'wiki_enable', OFF );
			}
		}
	}
	
	/**
	 * Generate wiki link to a bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return string url
	 * @access public
	 */
	static function link_bug( $p_bug_id ) {
		return event_signal( 'EVENT_WIKI_LINK_BUG', $p_bug_id );
	}
	
	/**
	 * Generate wiki link to a project
	 * @param integer $p_project_id A valid project identifier.
	 * @return string url
	 * @access public
	 */
	static function link_project( $p_project_id ) {
		return event_signal( 'EVENT_WIKI_LINK_PROJECT', $p_project_id );
	}


}