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
 * Permissions Report
 * @package MantisBT
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );

\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'manage_configuration_threshold' ) );

\Flickerbox\HTML::page_top( \Flickerbox\Lang::get( 'permissions_summary_report' ) );

\Flickerbox\HTML::print_manage_menu( 'adm_permissions_report.php' );
\Flickerbox\HTML::print_manage_config_menu( 'adm_permissions_report.php' );

/**
 * return html for start of administration report section
 * @param string $p_section_name Section name.
 * @return string
 */
function get_section_begin_apr( $p_section_name ) {
	$t_access_levels = \Flickerbox\MantisEnum::getValues( \Flickerbox\Config::mantis_get( 'access_levels_enum_string' ) );

	$t_output = '<div class="table-container">';
	$t_output .= '<table>';
	$t_output .= '<thead>';
	$t_output .= '<tr><td class="form-title-caps" colspan="' . ( count( $t_access_levels ) + 1 ) . '">' . $p_section_name . '</td></tr>' . "\n";
	$t_output .= '<tr class="row-category2">';
	$t_output .= '<th class="form-title">' . \Flickerbox\Lang::get( 'perm_rpt_capability' ) . '</th>';

	foreach( $t_access_levels as $t_access_level ) {
		$t_output .= '<th class="form-title" style="text-align:center">&#160;' . \Flickerbox\MantisEnum::getLabel( \Flickerbox\Lang::get( 'access_levels_enum_string' ), $t_access_level ) . '&#160;</th>';
	}

	$t_output .= '</tr>' . "\n";
	$t_output .= '</thead>';
	$t_output .= '<tbody>';

	return $t_output;
}

/**
 * Return html for a row
 * @param string  $p_caption      Caption.
 * @param integer $p_access_level Access level.
 * @return string
 */
function get_capability_row( $p_caption, $p_access_level ) {
	$t_access_levels = \Flickerbox\MantisEnum::getValues( \Flickerbox\Config::mantis_get( 'access_levels_enum_string' ) );

	$t_output = '<tr><td>' . \Flickerbox\String::display( $p_caption ) . '</td>';
	foreach( $t_access_levels as $t_access_level ) {
		if( $t_access_level >= (int)$p_access_level ) {
			$t_value = '<img src="images/ok.gif" width="20" height="15" alt="X" title="X" />';
		} else {
			$t_value = '&#160;';
		}

		$t_output .= '<td class="center">' . $t_value . '</td>';
	}

	$t_output .= '</tr>' . "\n";

	return $t_output;
}

/**
 * return html for end of administration report section
 * @return string
 */
function get_section_end() {
	$t_output = '</tbody></table></div><br />' . "\n";
	return $t_output;
}

# News
if( \Flickerbox\Config::mantis_get( 'news_enabled' ) == ON ) {
	echo get_section_begin_apr( \Flickerbox\Lang::get( 'news' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'view_private_news' ), \Flickerbox\Config::mantis_get( 'private_news_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'manage_news' ), \Flickerbox\Config::mantis_get( 'manage_news_threshold' ) );
	echo get_section_end();
}

# Attachments
if( \Flickerbox\Config::mantis_get( 'allow_file_upload' ) == ON ) {
	echo get_section_begin_apr( \Flickerbox\Lang::get( 'attachments' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'view_list_of_attachments' ), \Flickerbox\Config::mantis_get( 'view_attachments_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'download_attachments' ), \Flickerbox\Config::mantis_get( 'download_attachments_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'delete_attachments' ), \Flickerbox\Config::mantis_get( 'delete_attachments_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'upload_issue_attachments' ), \Flickerbox\Config::mantis_get( 'upload_bug_file_threshold' ) );
	echo get_section_end();
}

# Filters
echo get_section_begin_apr( \Flickerbox\Lang::get( 'filters' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'save_filters' ), \Flickerbox\Config::mantis_get( 'stored_query_create_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'save_filters_as_shared' ), \Flickerbox\Config::mantis_get( 'stored_query_create_shared_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'use_saved_filters' ), \Flickerbox\Config::mantis_get( 'stored_query_use_threshold' ) );
echo get_section_end();

# Projects
echo get_section_begin_apr( \Flickerbox\Lang::get( 'projects_link' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'create_project' ), \Flickerbox\Config::mantis_get( 'create_project_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'delete_project' ), \Flickerbox\Config::mantis_get( 'delete_project_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'manage_projects_link' ), \Flickerbox\Config::mantis_get( 'manage_project_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'manage_user_access_to_project' ), \Flickerbox\Config::mantis_get( 'project_user_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'automatically_included_in_private_projects' ), \Flickerbox\Config::mantis_get( 'private_project_threshold' ) );
echo get_section_end();

# Project Documents
if( \Flickerbox\Config::mantis_get( 'enable_project_documentation' ) == ON ) {
	echo get_section_begin_apr( \Flickerbox\Lang::get( 'project_documents' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'view_project_documents' ), \Flickerbox\Config::mantis_get( 'view_proj_doc_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'upload_project_documents' ), \Flickerbox\Config::mantis_get( 'upload_project_file_threshold' ) );
	echo get_section_end();
}

# Custom Fields
echo get_section_begin_apr( \Flickerbox\Lang::get( 'custom_fields_setup' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'manage_custom_field_link' ), \Flickerbox\Config::mantis_get( 'manage_custom_fields_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'link_custom_fields_to_projects' ), \Flickerbox\Config::mantis_get( 'custom_field_link_threshold' ) );
echo get_section_end();

# Sponsorships
if( \Flickerbox\Config::mantis_get( 'enable_sponsorship' ) == ON ) {
	echo get_section_begin_apr( \Flickerbox\Lang::get( 'sponsorships' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'view_sponsorship_details' ), \Flickerbox\Config::mantis_get( 'view_sponsorship_details_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'view_sponsorship_total' ), \Flickerbox\Config::mantis_get( 'view_sponsorship_total_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'sponsor_issue' ), \Flickerbox\Config::mantis_get( 'sponsor_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'assign_sponsored_issue' ), \Flickerbox\Config::mantis_get( 'assign_sponsored_bugs_threshold' ) );
	echo get_capability_row( \Flickerbox\Lang::get( 'handle_sponsored_issue' ), \Flickerbox\Config::mantis_get( 'handle_sponsored_bugs_threshold' ) );
	echo get_section_end();
}

# Others
echo get_section_begin_apr( \Flickerbox\Lang::get( 'others' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'view' ) . ' ' . \Flickerbox\Lang::get( 'summary_link' ), \Flickerbox\Config::mantis_get( 'view_summary_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'see_email_addresses_of_other_users' ), \Flickerbox\Config::mantis_get( 'show_user_email_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'send_reminders' ), \Flickerbox\Config::mantis_get( 'bug_reminder_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'add_profiles' ), \Flickerbox\Config::mantis_get( 'add_profile_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'manage_users_link' ), \Flickerbox\Config::mantis_get( 'manage_user_threshold' ) );
echo get_capability_row( \Flickerbox\Lang::get( 'notify_of_new_user_created' ), \Flickerbox\Config::mantis_get( 'notify_new_user_created_threshold_min' ) );
echo get_section_end();

\Flickerbox\HTML::page_bottom();
