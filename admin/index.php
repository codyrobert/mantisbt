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
 * Mantis Administration Section
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_once( 'schema.php' );

\Core\Access::ensure_global_level( \Core\Config::get_global( 'admin_site_threshold' ) );

\Core\HTML::page_top( 'MantisBT Administration' );

/**
 * Print Check result - information only
 *
 * @param string $p_description Description.
 * @param string $p_value       Information.
 * @return void
 */
function print_info_row( $p_description, $p_value ) {
	echo '<tr>';
	echo '<th class="category">' . $p_description . '</th>';
	echo '<td>' . $p_value . '</td>';
	echo '</tr>';
}
?>

<div id="admin-menu">
	<ul class="menu">
		<li><a href="check/index.php">Check your installation</a></li>
	<?php if( count( $g_upgrade ) - 1 != \Core\Config::mantis_get( 'database_version' ) ) { ?>
		<li><a href="install.php"><span class="bold">Upgrade your installation</span></a></li>
	<?php } ?>
		<li><a href="system_utils.php">System Utilities</a></li>
		<li><a href="test_langs.php">Test Langs</a></li>
		<li><a href="email_queue.php">Email Queue</a></li>
	</ul>
</div>

<div id="admin-overview" class="table-container">
	<table>
		<tr>
			<td class="form-title" width="30%" colspan="2">
				<?php echo \Core\Lang::get( 'install_information' ) ?>
			</td>
		</tr>
<?php
	if( ON == \Core\Config::mantis_get( 'show_version' ) ) {
		$t_version_suffix = \Core\Config::get_global( 'version_suffix' );
	} else {
		$t_version_suffix = '';
	}
	print_info_row( \Core\Lang::get( 'mantis_version' ), MANTIS_VERSION, $t_version_suffix );
	print_info_row( \Core\Lang::get( 'php_version' ), phpversion() );
?>
		<tr>
			<td class="form-title" width="30%" colspan="2">
				<?php echo \Core\Lang::get( 'database_information' ) ?>
			</td>
		</tr>
<?php
	print_info_row( \Core\Lang::get( 'schema_version' ), \Core\Config::mantis_get( 'database_version' ) );
	print_info_row( \Core\Lang::get( 'adodb_version' ), $g_db->Version() );
?>
		<tr>
			<td class="form-title" width="30%" colspan="2">
				<?php echo \Core\Lang::get( 'path_information' ) ?>
			</td>
		</tr>
<?php
	print_info_row( \Core\Lang::get( 'site_path' ), \Core\Config::mantis_get( 'absolute_path' ) );
	print_info_row( \Core\Lang::get( 'core_path' ), \Core\Config::mantis_get( 'core_path' ) );
	print_info_row( \Core\Lang::get( 'plugin_path' ), \Core\Config::mantis_get( 'plugin_path' ) );
?>
	</table>
</div>

<?php
	\Core\HTML::page_bottom();
