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
 * Edit Graph Plugin Configuration
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

\Core\Form::security_validate( 'plugin_graph_config_edit' );

\Core\Auth::reauthenticate( );
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_plugin_threshold' ) );

$f_library = \Core\GPC::get_int( 'eczlibrary', ON );

$f_window_width = \Core\GPC::get_int( 'window_width', 800 );
$f_bar_aspect = (float)\Core\GPC::get_string( 'bar_aspect', '0.9' );
$f_summary_graphs_per_row = \Core\GPC::get_int( 'summary_graphs_per_row', 2 );

$f_jpgraph_antialias = \Core\GPC::get_int( 'jpgraph_antialias', ON );
$f_font = \Core\GPC::get_string( 'font', '' );

if( \Core\Plugin::config_get( 'eczlibrary' ) != $f_library ) {
	\Core\Plugin::config_set( 'eczlibrary', $f_library );
}

if( \Core\Plugin::config_get( 'window_width' ) != $f_window_width ) {
	\Core\Plugin::config_set( 'window_width', $f_window_width );
}

if( \Core\Plugin::config_get( 'bar_aspect' ) != $f_bar_aspect ) {
	\Core\Plugin::config_set( 'bar_aspect', $f_bar_aspect );
}

if( \Core\Plugin::config_get( 'summary_graphs_per_row' ) != $f_summary_graphs_per_row ) {
	\Core\Plugin::config_set( 'summary_graphs_per_row', $f_summary_graphs_per_row );
}

if( \Core\Plugin::config_get( 'font' ) != $f_font ) {
	switch( $f_font ) {
		case 'arial':
		case 'verdana':
		case 'trebuchet':
		case 'verasans':
		case 'times':
		case 'georgia':
		case 'veraserif':
		case 'courier':
		case 'veramono':
			\Core\Plugin::config_set( 'font', $f_font );
			break;
		default:
			\Core\Plugin::config_set( 'font', 'arial' );
	}
}

if( \Core\Current_User::is_administrator() ) {
	$f_jpgraph_path = \Core\GPC::get_string( 'jpgraph_path', '' );
	if( \Core\Plugin::config_get( 'jpgraph_path' ) != $f_jpgraph_path ) {
		\Core\Plugin::config_set( 'jpgraph_path', $f_jpgraph_path );
	}
}

if( \Core\Plugin::config_get( 'jpgraph_antialias' ) != $f_jpgraph_antialias ) {
	\Core\Plugin::config_set( 'jpgraph_antialias', $f_jpgraph_antialias );
}

\Core\Form::security_purge( 'plugin_graph_config_edit' );

\Core\Print_Util::successful_redirect( \Core\Plugin::page( 'config', true ) );
