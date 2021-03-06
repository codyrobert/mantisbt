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
 * Summary Graphy by Reporter
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */



\Core\Plugin::require_api( 'core/graph_api.php' );

\Core\Access::ensure_project_level( \Core\Config::mantis_get( 'view_summary_threshold' ) );

$f_width = \Core\GPC::get_int( 'width', 300 );
$t_ar = \Core\Plugin::config_get( 'bar_aspect' );

$t_metrics = create_reporter_summary();
graph_bar( $t_metrics, \Core\Lang::get( 'by_reporter' ), $f_width, $f_width * $t_ar );
