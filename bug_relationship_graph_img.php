<?php
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
 * Display Bug relationship Graph
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses relationship_graph_api.php
 */



# If relationship graphs were made disabled, we disallow any access to
# this script.

\Core\Auth::ensure_user_authenticated();

if( ON != \Core\Config::mantis_get( 'relationship_graph_enable' ) ) {
	\Core\Access::denied();
}

$f_bug_id		= \Core\GPC::get_int( 'bug_id' );
$f_type			= \Core\GPC::get_string( 'graph', 'relation' );
$f_orientation	= \Core\GPC::get_string( 'orientation', \Core\Config::mantis_get( 'relationship_graph_orientation' ) );

$t_bug = \Core\Bug::get( $f_bug_id, true );

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'view_bug_threshold', null, null, $t_bug->project_id ), $f_bug_id );

\Core\Compress::enable();

$t_graph_relation = ( 'relation' == $f_type );
$t_graph_horizontal = ( 'horizontal' == $f_orientation );

if( $t_graph_relation ) {
	$t_graph = \Core\Relationship\Graph::generate_rel_graph( $f_bug_id );
} else {
	$t_graph = \Core\Relationship\Graph::generate_dep_graph( $f_bug_id, $t_graph_horizontal );
}

\Core\Relationship\Graph::output_image( $t_graph );
