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
 * Plugin Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

auth_reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_plugin_threshold' ) );

\Core\HTML::page_top( \Core\Lang::get( 'manage_plugin_link' ) );

\Core\HTML::print_manage_menu( 'manage_plugin_page.php' );

$t_plugins = \Core\Plugin::find_all();
uasort( $t_plugins,
	function ( $p_p1, $p_p2 ) {
		return strcasecmp( $p_p1->name, $p_p2->name );
	}
);

$t_plugins_installed = array();
$t_plugins_available = array();

foreach( $t_plugins as $t_basename => $t_plugin ) {
	if( \Core\Plugin::is_registered( $t_basename ) ) {
		$t_plugins_installed[$t_basename] = $t_plugin;
	} else {
		$t_plugins_available[$t_basename] = $t_plugin;
	}
}

if( 0 < count( $t_plugins_installed ) ) {
?>
<br/>
<div class="form-container">

	<form action="manage_plugin_update.php" method="post">
		<fieldset>
		<?php echo \Core\Form::security_field( 'manage_plugin_update' ) ?>
		</fieldset>
		<table>
			<colgroup>
				<col style="width:20%" />
				<col style="width:35%" />
				<col style="width:20%" />
				<col style="width:7%" />
				<col style="width:8%" />
				<col style="width:10%" />
			</colgroup>
			<thead>
				<!-- Title -->
				<tr>
					<td class="form-title" colspan="7">
						<?php echo \Core\Lang::get( 'plugins_installed' ) ?>
					</td>
				</tr>

				<!-- Info -->
				<tr class="row-category">
					<th><?php echo \Core\Lang::get( 'plugin' ) ?></th>
					<th><?php echo \Core\Lang::get( 'plugin_description' ) ?></th>
					<th><?php echo \Core\Lang::get( 'plugin_depends' ) ?></th>
					<th><?php echo \Core\Lang::get( 'plugin_priority' ) ?></th>
					<th><?php echo \Core\Lang::get( 'plugin_protected' ) ?></th>
					<th><?php echo \Core\Lang::get( 'plugin_actions' ) ?></th>
				</tr>
			</thead>

			<tbody>
<?php
foreach ( $t_plugins_installed as $t_basename => $t_plugin ) {
	$t_description = \Core\String::display_line_links( $t_plugin->description );
	$t_author = $t_plugin->author;
	$t_contact = $t_plugin->contact;
	$t_page = $t_plugin->page;
	$t_url = $t_plugin->url;
	$t_requires = $t_plugin->requires;
	$t_depends = array();
	$t_priority = \Core\Plugin::priority( $t_basename );
	$t_protected = \Core\Plugin::is_protected( $t_basename );

	$t_name = \Core\String::display_line( $t_plugin->name.' '.$t_plugin->version );
	if( !\Core\Utility::is_blank( $t_page ) ) {
		$t_name = '<a href="' . \Core\String::attribute( \Core\Plugin::page( $t_page, false, $t_basename ) ) . '">' . $t_name . '</a>';
	}

	if( !\Core\Utility::is_blank( $t_author ) ) {
		if( is_array( $t_author ) ) {
			$t_author = implode( $t_author, ', ' );
		}
		if( !\Core\Utility::is_blank( $t_contact ) ) {
			$t_author = '<br/>' . sprintf( \Core\Lang::get( 'plugin_author' ),
				'<a href="mailto:' . \Core\String::attribute( $t_contact ) . '">' . \Core\String::display_line( $t_author ) . '</a>' );
		} else {
			$t_author = '<br/>' . \Core\String::display_line( sprintf( \Core\Lang::get( 'plugin_author' ), $t_author ) );
		}
	}

	if( !\Core\Utility::is_blank( $t_url ) ) {
		$t_url = '<br/>' . \Core\Lang::get( 'plugin_url' ) . \Core\Lang::get( 'word_separator' ) . '<a href="' . $t_url . '">' . $t_url . '</a>';
	}

	$t_upgrade = \Core\Plugin::needs_upgrade( $t_plugin );

	if( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_plugin => $t_version ) {
			$t_dependency = \Core\Plugin::dependency( $t_plugin, $t_version );
			if( 1 == $t_dependency ) {
				if( \Core\Utility::is_blank( $t_upgrade ) ) {
					$t_depends[] = '<span class="small dependency_met">'.\Core\String::display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_depends[] = '<span class="small dependency_upgrade">'.\Core\String::display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				}
			} else if( -1 == $t_dependency ) {
				$t_depends[] = '<span class="small dependency_dated">'.\Core\String::display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
			} else {
				$t_depends[] = '<span class="small dependency_unmet">'.\Core\String::display_line( $t_plugin.' '.$t_version ).'</span>';
			}
		}
	}

	if( 0 < count( $t_depends ) ) {
		$t_depends = implode( $t_depends, '<br/>' );
	} else {
		$t_depends = '<span class="small dependency_met">' . \Core\Lang::get( 'plugin_no_depends' ) . '</span>';
	}

	echo '<tr>';
	echo '<td class="small center">',$t_name,'<input type="hidden" name="change_',$t_basename,'" value="1"/></td>';
	echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="small center">',$t_depends,'</td>';
	if( 'MantisCore' == $t_basename ) {
		echo '<td>&#160;</td><td>&#160;</td>';
	} else {
		echo '<td class="center">',
			'<select name="priority_' . $t_basename . '"',
				\Core\Helper::check_disabled( $t_protected ), '>',
				\Core\Print_Util::plugin_priority_list( $t_priority ),
			'</select>','</td>';
		echo '<td class="center">',
			'<input type="checkbox" name="protected_' . $t_basename . '"',
				\Core\Helper::check_disabled( $t_protected ), \Core\Helper::check_checked( $t_protected ), ' />',
			'</select>','</td>';
	}
	echo '<td class="center">';
	if( $t_upgrade ) {
		\Core\Print_Util::bracket_link(
			'manage_plugin_upgrade.php?name=' . $t_basename . \Core\Form::security_param( 'manage_plugin_upgrade' ),
			\Core\Lang::get( 'plugin_upgrade' ) );
	}
	if( !$t_protected ) {
		\Core\Print_Util::bracket_link(
			'manage_plugin_uninstall.php?name=' . $t_basename . \Core\Form::security_param( 'manage_plugin_uninstall' ),
			\Core\Lang::get( 'plugin_uninstall' ) );
	}
	echo '</td></tr>';
} ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="3"></td>
					<td colspan="2" class="center">
						<input type="submit" class="button" value="<?php echo \Core\Lang::get( 'plugin_update' ) ?>"/>
					</td>
					<td></td>
				</tr>
			</tfoot>

		</table>
	</form>
</div>
<?php
}

if( 0 < count( $t_plugins_available ) ) {
?>
<br/>
<div class="table-container">
	<table>
		<colgroup>
			<col style="width:25%" />
			<col style="width:45%" />
			<col style="width:20%" />
			<col style="width:10%" />
		</colgroup>
		<thead>
			<!-- Title -->
			<tr>
				<td class="form-title" colspan="7">
					<?php echo \Core\Lang::get( 'plugins_available' ) ?>
				</td>
			</tr>

			<!-- Info -->
			<tr class="row-category">
				<td><?php echo \Core\Lang::get( 'plugin' ) ?></td>
				<td><?php echo \Core\Lang::get( 'plugin_description' ) ?></td>
				<td><?php echo \Core\Lang::get( 'plugin_depends' ) ?></td>
				<td><?php echo \Core\Lang::get( 'plugin_actions' ) ?></td>
			</tr>
		</thead>

		<tbody>
<?php
	foreach ( $t_plugins_available as $t_basename => $t_plugin ) {
		$t_description = \Core\String::display_line_links( $t_plugin->description );
		$t_author = $t_plugin->author;
		$t_contact = $t_plugin->contact;
		$t_url = $t_plugin->url ;
		$t_requires = $t_plugin->requires;
		$t_depends = array();

		$t_name = \Core\String::display_line( $t_plugin->name.' '.$t_plugin->version );

		if( !\Core\Utility::is_blank( $t_author ) ) {
			if( is_array( $t_author ) ) {
				$t_author = implode( $t_author, ', ' );
			}
			if( !\Core\Utility::is_blank( $t_contact ) ) {
				$t_author = '<br/>' . sprintf( \Core\Lang::get( 'plugin_author' ),
					'<a href="mailto:' . \Core\String::display_line( $t_contact ) . '">' . \Core\String::display_line( $t_author ) . '</a>' );
			} else {
				$t_author = '<br/>' . \Core\String::display_line( sprintf( \Core\Lang::get( 'plugin_author' ), $t_author ) );
			}
		}

		if( !\Core\Utility::is_blank( $t_url ) ) {
			$t_url = '<br/>' . \Core\Lang::get( 'plugin_url' ) . \Core\Lang::get( 'word_separator' ) . '<a href="' . $t_url . '">' . $t_url . '</a>';
		}

		$t_ready = true;
		if( is_array( $t_requires ) ) {
			foreach( $t_requires as $t_plugin => $t_version ) {
				$t_dependency = \Core\Plugin::dependency( $t_plugin, $t_version );
				if( 1 == $t_dependency ) {
					$t_depends[] = '<span class="small dependency_met">'.\Core\String::display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else if( -1 == $t_dependency ) {
					$t_ready = false;
					$t_depends[] = '<span class="small dependency_dated">'.\Core\String::display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_ready = false;
					$t_depends[] = '<span class="small dependency_unmet">'.\Core\String::display_line( $t_plugin.' '.$t_version ).'</span>';
				}
			}
		}

		if( 0 < count( $t_depends ) ) {
			$t_depends = implode( $t_depends, '<br/>' );
		} else {
			$t_depends = '<span class="small dependency_met">' . \Core\Lang::get( 'plugin_no_depends' ) . '</span>';
		}

		echo '<tr>';
		echo '<td class="small center">',$t_name,'</td>';
		echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
		echo '<td class="center">',$t_depends,'</td>';
		echo '<td class="center">';
		if( $t_ready ) {
			\Core\Print_Util::bracket_link(
				'manage_plugin_install.php?name=' . $t_basename . \Core\Form::security_param( 'manage_plugin_install' ),
				\Core\Lang::get( 'plugin_install' ) );
		}
		echo '</td></tr>';
	}
?>
		</tbody>
	</table>
</div>

<?php
} # available plugins
?>
<div class="center">
	<br/><?php echo \Core\Lang::get( 'plugin_key_label' ) ?>
	<span class='dependency_met'><?php echo \Core\Lang::get( 'plugin_key_met' ) ?></span>,
	<span class='dependency_unmet'><?php echo \Core\Lang::get( 'plugin_key_unmet' ) ?></span>,
	<span class='dependency_dated'><?php echo \Core\Lang::get( 'plugin_key_dated' ) ?></span>,
	<span class='dependency_upgrade'><?php echo \Core\Lang::get( 'plugin_key_upgrade' ) ?></span>.
</div>
<?php
\Core\HTML::page_bottom();

