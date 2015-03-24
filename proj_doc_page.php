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
 * Project Document Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 */



$f_project_id = \Core\GPC::get_int( 'project_id', \Core\Helper::get_current_project() );

# Check if project documentation feature is enabled.
if( OFF == \Core\Config::mantis_get( 'enable_project_documentation' ) || !\Core\File::is_uploading_enabled() ) {
	\Core\Access::denied();
}

# Override the current page to make sure we get the appropriate project-specific configuration
$g_project_override = $f_project_id;

$t_user_id = \Core\Auth::get_current_user_id();
$t_pub = VS_PUBLIC;
$t_priv = VS_PRIVATE;
$t_admin = \Core\Config::get_global( 'admin_site_threshold' );

if( $f_project_id == ALL_PROJECTS ) {
	# Select all the projects that the user has access to
	$t_projects = \Core\User::get_accessible_projects( $t_user_id );
} else {
	# Select the specific project
	$t_projects = array( $f_project_id );
}

$t_projects[] = ALL_PROJECTS; # add "ALL_PROJECTS to the list of projects to fetch

$t_reqd_access = \Core\Config::mantis_get( 'view_proj_doc_threshold' );
if( is_array( $t_reqd_access ) ) {
	if( 1 == count( $t_reqd_access ) ) {
		$t_access_clause = '= ' . array_shift( $t_reqd_access ) . ' ';
	} else {
		$t_access_clause = 'IN (' . implode( ',', $t_reqd_access ) . ')';
	}
} else {
	$t_access_clause = '>= ' . $t_reqd_access . ' ';
}

$t_query = 'SELECT pft.id, pft.project_id, pft.filename, pft.filesize, pft.title, pft.description, pft.date_added
			FROM {project_file} pft
				LEFT JOIN {project} pt ON pft.project_id = pt.id
				LEFT JOIN {project_user_list} pult
					ON pft.project_id = pult.project_id AND pult.user_id = ' . \Core\Database::param() . '
				LEFT JOIN {user} ut ON ut.id = ' . \Core\Database::param() . '
			WHERE pft.project_id in (' . implode( ',', $t_projects ) . ') AND
				( ( ( pt.view_state = ' . \Core\Database::param() . ' OR pt.view_state is null ) AND pult.user_id is null AND ut.access_level ' . $t_access_clause . ' ) OR
					( ( pult.user_id = ' . \Core\Database::param() . ' ) AND ( pult.access_level ' . $t_access_clause . ' ) ) OR
					( ut.access_level >= ' . \Core\Database::param() . ' ) )
			ORDER BY pt.name ASC, pft.title ASC';
$t_result = \Core\Database::query( $t_query, array( $t_user_id, $t_user_id, $t_pub, $t_user_id, $t_admin ) );

\Core\HTML::page_top( \Core\Lang::get( 'docs_link' ) );
?>
<br />
<div class="table-container">
<table>
<thead>

<tr>
	<td class="form-title">
		<?php echo \Core\Lang::get( 'project_documentation_title' ) ?>
	</td>
	<td class="right">
		<?php \Core\HTML::print_doc_menu( 'proj_doc_page.php' ) ?>
	</td>
</tr>

<tr class="row-category2">
	<th><?php echo \Core\Lang::get( 'filename' ); ?></th>
	<th><?php echo \Core\Lang::get( 'description' ); ?></th>
</tr>
</thead>

<?php
$i = 0;
while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
	$i++;
	extract( $t_row, EXTR_PREFIX_ALL, 'v' );
	$v_filesize = number_format( $v_filesize );
	$v_title = \Core\String::display( $v_title );
	$v_description = \Core\String::display_links( $v_description );
	$v_date_added = date( \Core\Config::mantis_get( 'normal_date_format' ), $v_date_added );

?>
<tr>
	<td>
		<span class="floatleft">
<?php
	$t_href = '<a href="file_download.php?file_id='.$v_id.'&amp;type=doc">';
	echo $t_href;
	\Core\Print_Util::file_icon( $v_filename );
	echo '</a>&#160;' . $t_href . $v_title . '</a> (' . $v_filesize . \Core\Lang::get( 'word_separator' ) . \Core\Lang::get( 'bytes' ) . ')';
?>
			<br />
			<span class="small">
<?php
	if( $v_project_id == ALL_PROJECTS ) {
		echo \Core\Lang::get( 'all_projects' ) . '<br/>';
	} else if( $v_project_id != $f_project_id ) {
		$t_project_name = \Core\Project::get_name( $v_project_id );
		echo $t_project_name . '<br/>';
	}
	echo '(' . $v_date_added . ')';
?>
			</span>
		</span>
		<span class="floatright">
<?php
	if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'upload_project_file_threshold', null, null, $v_project_id ), $v_project_id ) ) {
		echo '&#160;';
		\Core\Print_Util::button( 'proj_doc_edit_page.php?file_id='.$v_id, \Core\Lang::get( 'edit_link' ) );
		echo '&#160;';
		\Core\Print_Util::button( 'proj_doc_delete.php?file_id=' . $v_id, \Core\Lang::get( 'delete_link' ) );
	}
?>
		</span>
	</td>
	<td>
		<?php echo $v_description ?>
	</td>
</tr>
<?php
} # end for loop
?>
</table>
</div>

<?php
\Core\HTML::page_bottom();
