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
 * Check that obsolete configs are not used.
 * THIS FILE ASSUMES THAT THE CONFIGURATION IS INCLUDED AS WELL AS THE
 * config_api.php.
 *
 * @package CoreAPI
 * @subpackage ObsoleteAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# ==== Changes after 0.18.2 ====
\Flickerbox\Config::obsolete( 'use_phpMailer', '' );
\Flickerbox\Config::obsolete( 'phpMailer_path', '' );
\Flickerbox\Config::obsolete( 'use_x_priority', '' );

# ==== Changes after 0.17.5 ====
\Flickerbox\Config::obsolete( 'new_color', 'status_colors' );
\Flickerbox\Config::obsolete( 'feedback_color', 'status_colors' );
\Flickerbox\Config::obsolete( 'acknowledged_color', 'status_colors' );
\Flickerbox\Config::obsolete( 'confirmed_color', 'status_colors' );
\Flickerbox\Config::obsolete( 'assigned_color', 'status_colors' );
\Flickerbox\Config::obsolete( 'resolved_color', 'status_colors' );
\Flickerbox\Config::obsolete( 'closed_color', 'status_colors' );

\Flickerbox\Config::obsolete( 'primary_table_tags', '' );
\Flickerbox\Config::obsolete( 'background_color', '' );
\Flickerbox\Config::obsolete( 'required_color', '' );
\Flickerbox\Config::obsolete( 'table_border_color', '' );
\Flickerbox\Config::obsolete( 'category_title_color', '' );
\Flickerbox\Config::obsolete( 'primary_color1', '' );
\Flickerbox\Config::obsolete( 'primary_color2', '' );
\Flickerbox\Config::obsolete( 'form_title_color', '' );
\Flickerbox\Config::obsolete( 'spacer_color', '' );
\Flickerbox\Config::obsolete( 'menu_color', '' );
\Flickerbox\Config::obsolete( 'fonts', '' );
\Flickerbox\Config::obsolete( 'font_small', '' );
\Flickerbox\Config::obsolete( 'font_normal', '' );
\Flickerbox\Config::obsolete( 'font_large', '' );
\Flickerbox\Config::obsolete( 'font_color', '' );

\Flickerbox\Config::obsolete( 'notify_developers_on_new', 'notify_flags' );
\Flickerbox\Config::obsolete( 'notify_on_new_threshold', 'notify_flags' );
\Flickerbox\Config::obsolete( 'notify_admin_on_new', 'notify_flags' );
\Flickerbox\Config::obsolete( 'view_bug_inc', '' );
\Flickerbox\Config::obsolete( 'ldap_organisation', 'ldap_organization' );
\Flickerbox\Config::obsolete( 'ldapauth_type', '' );
\Flickerbox\Config::obsolete( 'summary_product_colon_category', 'summary_category_include_project' );

\Flickerbox\Config::obsolete( 'allow_href_tags', 'html_make_links' );
\Flickerbox\Config::obsolete( 'allow_html_tags', 'html_valid_tags' );
\Flickerbox\Config::obsolete( 'html_tags', 'html_valid_tags' );
\Flickerbox\Config::obsolete( 'show_user_email', 'show_user_email_threshold' );

\Flickerbox\Config::obsolete( 'manage_custom_fields', 'manage_custom_fields_threshold' );
\Flickerbox\Config::obsolete( 'allow_bug_delete_access_level', 'delete_bug_threshold' );
\Flickerbox\Config::obsolete( 'bug_move_access_level', 'move_bug_threshold' );

\Flickerbox\Config::obsolete( 'php', '' );
\Flickerbox\Config::obsolete( 'use_experimental_custom_fields', '' );
\Flickerbox\Config::obsolete( 'mail_send_crlf', '' );

\Flickerbox\Config::obsolete( 'bugnote_include_file', '' );
\Flickerbox\Config::obsolete( 'bugnote_view_include_file', '' );
\Flickerbox\Config::obsolete( 'bugnote_add_include_file', '' );
\Flickerbox\Config::obsolete( 'history_include_file', '' );
\Flickerbox\Config::obsolete( 'print_bugnote_include_file', '' );
\Flickerbox\Config::obsolete( 'view_all_include_file', '' );
\Flickerbox\Config::obsolete( 'bug_view_inc', '' );
\Flickerbox\Config::obsolete( 'bug_file_upload_inc', '' );

\Flickerbox\Config::obsolete( 'show_source', '' );

\Flickerbox\Config::obsolete( 'summary_pad', '' );

\Flickerbox\Config::obsolete( 'show_project_in_title', '' );

# removed in 0.19
\Flickerbox\Config::obsolete( 'hide_closed_default', 'hide_status_default' );

\Flickerbox\Config::obsolete( 'close_bug_threshold', 'set_status_threshold' );

\Flickerbox\Config::obsolete( 'status_pulldown_enum_mask_string', '' );
\Flickerbox\Config::obsolete( 'to_email', '' );
\Flickerbox\Config::obsolete( 'use_bcc', '' );

# removed in 0.19.1
\Flickerbox\Config::obsolete( 'port', 'hostname' );

# changes in 0.19.3
\Flickerbox\Config::obsolete( 'relationship_graph_fontpath', 'system_font_folder' );

# changes in 1.1.0rc1
\Flickerbox\Config::obsolete( 'show_notices', 'display_errors' );
\Flickerbox\Config::obsolete( 'show_warnings', 'display_errors' );

# changes in 1.1.0rc2
\Flickerbox\Config::obsolete( 'wait_time', 'default_redirect_delay' );
\Flickerbox\Config::obsolete( 'default_bug_category', '' );

# changes in 1.2.0a1
\Flickerbox\Config::obsolete( 'enable_relationship', '' );
\Flickerbox\Config::obsolete( 'ldap_port', 'ldap_server' );

# changes in 1.2.0rc1
\Flickerbox\Config::obsolete( 'jpgraph_path', '' );
\Flickerbox\Config::obsolete( 'use_jpgraph', '' );
\Flickerbox\Config::obsolete( 'jpgraph_antialias', '' );

# changes in 1.2.0rc2
\Flickerbox\Config::obsolete( 'reminder_recipents_monitor_bug', 'reminder_recipients_monitor_bug' );
\Flickerbox\Config::obsolete( 'graph_window_width', '' );
\Flickerbox\Config::obsolete( 'graph_bar_aspect', '' );
\Flickerbox\Config::obsolete( 'graph_summary_graphs_per_row', '' );
\Flickerbox\Config::obsolete( 'show_report', '' );
\Flickerbox\Config::obsolete( 'show_view', '' );
\Flickerbox\Config::obsolete( 'show_update', '' );
\Flickerbox\Config::obsolete( 'default_advanced_report', '' );
\Flickerbox\Config::obsolete( 'default_advanced_view', '' );
\Flickerbox\Config::obsolete( 'default_advanced_update', '' );
\Flickerbox\Config::obsolete( 'default_graph_type', '' );
\Flickerbox\Config::obsolete( 'graph_font', '' );
\Flickerbox\Config::obsolete( 'graph_colors', '' );

# changes in 1.2.8
\Flickerbox\Config::obsolete( 'show_attachment_indicator' );
\Flickerbox\Config::obsolete( 'default_avatar', '' );

# changes in 1.2.13
\Flickerbox\Config::obsolete( 'manage_cookie', 'manage_users_cookie' );

# changes in 1.3.0dev
\Flickerbox\Config::obsolete( 'bugnote_allow_user_edit_delete', '' );
\Flickerbox\Config::obsolete( 'password_confirm_hash_magic_string', 'crypto_master_salt' );
\Flickerbox\Config::obsolete( 'rss_key_seed', 'crypto_master_salt' );
\Flickerbox\Config::obsolete( 'cvs_web' );
\Flickerbox\Config::obsolete( 'source_control_notes_view_status' );
\Flickerbox\Config::obsolete( 'source_control_account' );
\Flickerbox\Config::obsolete( 'source_control_set_status_to' );
\Flickerbox\Config::obsolete( 'source_control_set_resolution_to' );
\Flickerbox\Config::obsolete( 'source_control_regexp' );
\Flickerbox\Config::obsolete( 'source_control_fixed_regexp' );
\Flickerbox\Config::obsolete( 'allow_close_immediately' );
\Flickerbox\Config::obsolete( 'show_extended_project_browser' );
\Flickerbox\Config::obsolete( 'show_queries_threshold', 'show_log_threshold' );
\Flickerbox\Config::obsolete( 'show_queries_list' );
\Flickerbox\Config::obsolete( 'administrator_email', 'webmaster_email' );
\Flickerbox\Config::obsolete( 'session_key' );
\Flickerbox\Config::obsolete( 'dhtml_filters', 'use_dynamic_filters' );
\Flickerbox\Config::obsolete( 'use_iis' );
\Flickerbox\Config::obsolete( 'page_title', 'top_include_page' );
\Flickerbox\Config::obsolete( 'limit_email_domain', 'limit_email_domains' );
\Flickerbox\Config::obsolete( 'file_upload_ftp_server' );
\Flickerbox\Config::obsolete( 'file_upload_ftp_user' );
\Flickerbox\Config::obsolete( 'file_upload_ftp_pass' );
\Flickerbox\Config::obsolete( 'mantistouch_url' );
\Flickerbox\Config::obsolete( 'custom_strings_file' );
\Flickerbox\Config::obsolete( 'mc_readonly_access_level_threshold', 'webservice_readonly_access_level_threshold' );
\Flickerbox\Config::obsolete( 'mc_readwrite_access_level_threshold', 'webservice_readwrite_access_level_threshold' );
\Flickerbox\Config::obsolete( 'mc_admin_access_level_threshold', 'webservice_admin_access_level_threshold' );
\Flickerbox\Config::obsolete( 'mc_specify_reporter_on_add_access_level_threshold', 'webservice_specify_reporter_on_add_access_level_threshold' );
\Flickerbox\Config::obsolete( 'mc_priority_enum_default_when_not_found', 'webservice_priority_enum_default_when_not_found' );
\Flickerbox\Config::obsolete( 'mc_severity_enum_default_when_not_found', 'webservice_severity_enum_default_when_not_found' );
\Flickerbox\Config::obsolete( 'mc_status_enum_default_when_not_found', 'webservice_status_enum_default_when_not_found' );
\Flickerbox\Config::obsolete( 'mc_resolution_enum_default_when_not_found', 'webservice_resolution_enum_default_when_not_found' );
\Flickerbox\Config::obsolete( 'mc_projection_enum_default_when_not_found', 'webservice_projection_enum_default_when_not_found' );
\Flickerbox\Config::obsolete( 'mc_eta_enum_default_when_not_found', 'webservice_eta_enum_default_when_not_found' );
\Flickerbox\Config::obsolete( 'mc_error_when_version_not_found', 'webservice_error_when_version_not_found' );
\Flickerbox\Config::obsolete( 'mc_version_when_not_found', 'webservice_version_when_not_found' );
env_obsolete( 'MANTIS_CONFIG', 'MANTIS_CONFIG_FOLDER' );
\Flickerbox\Config::obsolete( 'colour_project' );
\Flickerbox\Config::obsolete( 'colour_global' );
\Flickerbox\Config::obsolete( 'content_expire' );
\Flickerbox\Config::obsolete( 'use_javascript' );
\Flickerbox\Config::obsolete( 'recently_visited', 'recently_visited_count' );
\Flickerbox\Config::obsolete( 'email_set_category' );
