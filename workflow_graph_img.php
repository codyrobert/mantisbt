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
 * Workflow Graph
 * @package MantisBT
 * @author Author Bernard de Rubinat - bernard@derubinat.net
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses relationship_graph_api.php
 * @uses workflow_api.php
 */

require_once( 'core.php' );

\Core\Auth::ensure_user_authenticated();

if( !\Core\Config::mantis_get( 'relationship_graph_enable' ) ) {
	\Core\Access::denied();
}

\Core\Compress::enable();

$t_status_arr  = \Core\Enum::getAssocArrayIndexedByValues( \Core\Config::mantis_get( 'status_enum_string' ) );

$t_graph_fontname = \Core\Config::mantis_get( 'relationship_graph_fontname' );
$t_graph_fontsize = \Core\Config::mantis_get( 'relationship_graph_fontsize' );
$t_graph_fontpath = \Core\Utility::get_font_path();
$t_dot_tool = \Core\Config::mantis_get( 'dot_tool' );

$t_graph_attributes = array();

if( !empty( $t_graph_fontpath ) ) {
	$t_graph_attributes['fontpath'] = $t_graph_fontpath;
}

$t_graph = new \Core\Graph( 'workflow', $t_graph_attributes, $t_dot_tool );

$t_graph->set_default_node_attr( array ( 'fontname' => $t_graph_fontname,
										 'fontsize' => $t_graph_fontsize,
										 'shape'    => 'record',
										 'style'    => 'filled',
										 'height'   => '0.2',
										 'width'    => '0.4' ) );

$t_graph->set_default_edge_attr( array ( 'style' => 'solid',
										 'color' => '#0000C0',
										 'dir'   => 'forward' ) );

foreach ( $t_status_arr as $t_from_status => $t_from_label ) {
	$t_enum_status = \Core\Enum::getAssocArrayIndexedByValues( \Core\Config::mantis_get( 'status_enum_string' ) );
	foreach ( $t_enum_status as $t_to_status_id => $t_to_status_label ) {
		if( \Core\Workflow::transition_edge_exists( $t_from_status, $t_to_status_id ) ) {
			$t_graph->add_edge( \Core\String::no_break( \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $t_from_status ) ),
			                    \Core\String::no_break( \Core\Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $t_to_status_id ) ),
			                    array() );
		}
	}
}

$t_graph->output( 'png', true );
