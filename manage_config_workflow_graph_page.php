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
 * Manage configuration for workflow Config
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses workflow_api.php
 */

require_once( 'core.php' );

auth_reauthenticate();

if( !\Core\Config::mantis_get( 'relationship_graph_enable' ) ) {
	\Core\Access::denied();
}

\Core\HTML::page_top( \Core\Lang::get( 'manage_workflow_graph' ) );

\Core\HTML::print_manage_menu( 'adm_permissions_report.php' );
\Core\HTML::print_manage_config_menu( 'manage_config_workflow_graph_page.php' );

$t_project = \Core\Helper::get_current_project();

if( $t_project == ALL_PROJECTS ) {
	$t_project_title = \Core\Lang::get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( \Core\Lang::get( 'config_project' ), \Core\String::display( \Core\Project::get_name( $t_project ) ) );
}
?>
	<br />
	<br />
	<div class="center">
		<p class="bold"><?php echo $t_project_title ?></p>
		<br />
		<img src="workflow_graph_img.php" />
	</div>
<?php
\Core\HTML::page_bottom();
