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



class Wiki
{

	/**
	 * Returns whether wiki functionality is enabled
	 * @return boolean indicating whether wiki is enabled
	 * @access public
	 */
	static function enabled() {
		return( \Core\Config::get_global( 'wiki_enable' ) == ON );
	}
	
	/**
	 * Initialise wiki engine
	 * @return void
	 * @access public
	 */
	static function init() {
		if( \Core\Wiki::enabled() ) {
	
			# handle legacy style wiki integration
			//require_once( \Core\Config::get_global( 'class_path' ) . 'MantisCoreWikiPlugin.class.php' );
			switch( \Core\Config::get_global( 'wiki_engine' ) ) {
				case 'dokuwiki':
					\Core\Plugin::child( 'MantisCoreDokuwiki' );
					break;
				case 'mediawiki':
					\Core\Plugin::child( 'MantisCoreMediaWiki' );
					break;
				case 'twiki':
					\Core\Plugin::child( 'MantisCoreTwiki' );
					break;
				case 'WikkaWiki':
					\Core\Plugin::child( 'MantisCoreWikkaWiki' );
					break;
				case 'xwiki':
					\Core\Plugin::child( 'MantisCoreXwiki' );
					break;
			}
	
			if( is_null( \Core\Event::signal( 'EVENT_WIKI_INIT' ) ) ) {
				\Core\Config::set_global( 'wiki_enable', OFF );
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
		return \Core\Event::signal( 'EVENT_WIKI_LINK_BUG', $p_bug_id );
	}
	
	/**
	 * Generate wiki link to a project
	 * @param integer $p_project_id A valid project identifier.
	 * @return string url
	 * @access public
	 */
	static function link_project( $p_project_id ) {
		return \Core\Event::signal( 'EVENT_WIKI_LINK_PROJECT', $p_project_id );
	}


}