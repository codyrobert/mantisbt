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
 * Overview Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );

auth_reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_site_threshold' ) );

$t_version_suffix = \Core\Config::get_global( 'version_suffix' );

\Core\HTML::page_top( \Core\Lang::get( 'manage_link' ) );

\Core\HTML::print_manage_menu();
?>
<div id="manage-overview-div" class="table-container">
	<h2><?php echo \Core\Lang::get( 'site_information' ) ?></h2>
	<table id="manage-overview-table" cellspacing="1" cellpadding="5" border="1">
		<tr>
			<th class="category"><?php echo \Core\Lang::get( 'mantis_version' ) ?></th>
			<td><?php echo MANTIS_VERSION, ( $t_version_suffix ? ' ' . $t_version_suffix : '' ) ?></td>
		</tr>
		<tr>
			<th class="category"><?php echo \Core\Lang::get( 'schema_version' ) ?></th>
			<td><?php echo \Core\Config::mantis_get( 'database_version' ) ?></td>
		</tr>
		<tr class="spacer">
			<td colspan="2"></td>
		</tr>
		<tr class="hidden"></tr>
	<?php
	$t_is_admin = \Core\Current_User::is_administrator();
	if( $t_is_admin ) {
	?>
		<tr>
			<th class="category"><?php echo \Core\Lang::get( 'site_path' ) ?></th>
			<td><?php echo \Core\Config::mantis_get( 'absolute_path' ) ?></td>
		</tr>
		<tr>
			<th class="category"><?php echo \Core\Lang::get( 'core_path' ) ?></th>
			<td><?php echo \Core\Config::mantis_get( 'core_path' ) ?></td>
		</tr>
		<tr>
			<th class="category"><?php echo \Core\Lang::get( 'plugin_path' ) ?></th>
			<td><?php echo \Core\Config::mantis_get( 'plugin_path' ) ?></td>
		</tr>
		<tr class="spacer">
			<td colspan="2"></td>
		</tr>
	<?php
	}

	\Core\Event::signal( 'EVENT_MANAGE_OVERVIEW_INFO', array( $t_is_admin ) )
	?>
	</table>
</div>
<?php
\Core\HTML::page_bottom();

