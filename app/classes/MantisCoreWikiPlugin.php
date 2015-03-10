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
 * Mantis Core Wiki Plugins
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * requires MantisWikiPlugin.class
 */

/**
 * Base that uses the old style wiki definitions from config_inc.php
 */
abstract class MantisCoreWikiPlugin extends MantisWikiPlugin {
	/**
	 * Config Function
	 * @return array
	 */
	function config() {
		return array(
			'root_namespace' => \Core\Config::get_global( 'wiki_root_namespace' ),
			'engine_url' => \Core\Config::get_global( 'wiki_engine_url' ),
		);
	}
}
