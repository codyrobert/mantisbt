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
\Core\Config::obsolete( 'use_phpMailer', '' );
\Core\Config::obsolete( 'phpMailer_path', '' );
\Core\Config::obsolete( 'use_x_priority', '' );

# ==== Changes after 0.17.5 ====
\Core\Config::obsolete( 'new_color', 'status_colors' );
\Core\Config::obsolete( 'feedback_color', 'status_colors' );
\Core\Config::obsolete( 'acknowledged_color', 'status_colors' );
\Core\Config::obsolete( 'confirmed_color', 'status_colors' );
\Core\Config::obsolete( 'assigned_color', 'status_colors' );
\Core\Config::obsolete( 'resolved_color', 'status_colors' );
\Core\Config::obsolete( 'closed_color', 'status_colors' );

\Core\Config::obsolete( 'primary_table_tags', '' );
\Core\Config::obsolete( 'background_color', '' );
\Core\Config::obsolete( 'required_color', '' );
\Core\Config::obsolete( 'table_border_color', '' );
\Core\Config::obsolete( 'category_title_color', '' );
\Core\Config::obsolete( 'primary_color1', '' );
\Core\Config::obsolete( 'primary_color2', '' );
\Core\Config::obsolete( 'form_title_color', '' );
\Core\Config::obsolete( 'spacer_color', '' );
\Core\Config::obsolete( 'menu_color', '' );
\Core\Config::obsolete( 'fonts', '' );
\Core\Config::obsolete( 'font_small', '' );
\Core\Config::obsolete( 'font_normal', '' );
\Core\Config::obsolete( 'font_large', '' );
\Core\Config::obsolete( 'font_color', '' );

\Core\Config::obsolete( 'notify_developers_on_new', 'notify_flags' );
\Core\Config::obsolete( 'notify_on_new_threshold', 'notify_flags' );
\Core\Config::obsolete( 'notify_admin_on_new', 'notify_flags' );
\Core\Config::obsolete( 'view_bug_inc', '' );
\Core\Config::obsolete( 'ldap_organisation', 'ldap_organization' );
\Core\Config::obsolete( 'ldapauth_type', '' );
\Core\Config::obsolete( 'summary_product_colon_category', 'summary_category_include_project' );

\Core\Config::obsolete( 'allow_href_tags', 'html_make_links' );
\Core\Config::obsolete( 'allow_html_tags', 'html_valid_tags' );
\Core\Config::obsolete( 'html_tags', 'html_valid_tags' );
\Core\Config::obsolete( 'show_user_email', 'show_user_email_threshold' );

\Core\Config::obsolete( 'manage_custom_fields', 'manage_custom_fields_threshold' );
\Core\Config::obsolete( 'allow_bug_delete_access_level', 'delete_bug_threshold' );
\Core\Config::obsolete( 'bug_move_access_level', 'move_bug_threshold' );

\Core\Config::obsolete( 'php', '' );
\Core\Config::obsolete( 'use_experimental_custom_fields', '' );
\Core\Config::obsolete( 'mail_send_crlf', '' );

\Core\Config::obsolete( 'bugnote_include_file', '' );
\Core\Config::obsolete( 'bugnote_view_include_file', '' );
\Core\Config::obsolete( 'bugnote_add_include_file', '' );
\Core\Config::obsolete( 'history_include_file', '' );
\Core\Config::obsolete( 'print_bugnote_include_file', '' );
\Core\Config::obsolete( 'view_all_include_file', '' );
\Core\Config::obsolete( 'bug_view_inc', '' );
\Core\Config::obsolete( 'bug_file_upload_inc', '' );

\Core\Config::obsolete( 'show_source', '' );

\Core\Config::obsolete( 'summary_pad', '' );

\Core\Config::obsolete( 'show_project_in_title', '' );

# removed in 0.19
\Core\Config::obsolete( 'hide_closed_default', 'hide_status_default' );

\Core\Config::obsolete( 'close_bug_threshold', 'set_status_threshold' );

\Core\Config::obsolete( 'status_pulldown_enum_mask_string', '' );
\Core\Config::obsolete( 'to_email', '' );
\Core\Config::obsolete( 'use_bcc', '' );

# removed in 0.19.1
\Core\Config::obsolete( 'port', 'hostname' );

# changes in 0.19.3
\Core\Config::obsolete( 'relationship_graph_fontpath', 'system_font_folder' );

# changes in 1.1.0rc1
\Core\Config::obsolete( 'show_notices', 'display_errors' );
\Core\Config::obsolete( 'show_warnings', 'display_errors' );

# changes in 1.1.0rc2
\Core\Config::obsolete( 'wait_time', 'default_redirect_delay' );
\Core\Config::obsolete( 'default_bug_category', '' );

# changes in 1.2.0a1
\Core\Config::obsolete( 'enable_relationship', '' );
\Core\Config::obsolete( 'ldap_port', 'ldap_server' );

# changes in 1.2.0rc1
\Core\Config::obsolete( 'jpgraph_path', '' );
\Core\Config::obsolete( 'use_jpgraph', '' );
\Core\Config::obsolete( 'jpgraph_antialias', '' );

# changes in 1.2.0rc2
\Core\Config::obsolete( 'reminder_recipents_monitor_bug', 'reminder_recipients_monitor_bug' );
\Core\Config::obsolete( 'graph_window_width', '' );
\Core\Config::obsolete( 'graph_bar_aspect', '' );
\Core\Config::obsolete( 'graph_summary_graphs_per_row', '' );
\Core\Config::obsolete( 'show_report', '' );
\Core\Config::obsolete( 'show_view', '' );
\Core\Config::obsolete( 'show_update', '' );
\Core\Config::obsolete( 'default_advanced_report', '' );
\Core\Config::obsolete( 'default_advanced_view', '' );
\Core\Config::obsolete( 'default_advanced_update', '' );
\Core\Config::obsolete( 'default_graph_type', '' );
\Core\Config::obsolete( 'graph_font', '' );
\Core\Config::obsolete( 'graph_colors', '' );

# changes in 1.2.8
\Core\Config::obsolete( 'show_attachment_indicator' );
\Core\Config::obsolete( 'default_avatar', '' );

# changes in 1.2.13
\Core\Config::obsolete( 'manage_cookie', 'manage_users_cookie' );

# changes in 1.3.0dev
\Core\Config::obsolete( 'bugnote_allow_user_edit_delete', '' );
\Core\Config::obsolete( 'password_confirm_hash_magic_string', 'crypto_master_salt' );
\Core\Config::obsolete( 'rss_key_seed', 'crypto_master_salt' );
\Core\Config::obsolete( 'cvs_web' );
\Core\Config::obsolete( 'source_control_notes_view_status' );
\Core\Config::obsolete( 'source_control_account' );
\Core\Config::obsolete( 'source_control_set_status_to' );
\Core\Config::obsolete( 'source_control_set_resolution_to' );
\Core\Config::obsolete( 'source_control_regexp' );
\Core\Config::obsolete( 'source_control_fixed_regexp' );
\Core\Config::obsolete( 'allow_close_immediately' );
\Core\Config::obsolete( 'show_extended_project_browser' );
\Core\Config::obsolete( 'show_queries_threshold', 'show_log_threshold' );
\Core\Config::obsolete( 'show_queries_list' );
\Core\Config::obsolete( 'administrator_email', 'webmaster_email' );
\Core\Config::obsolete( 'session_key' );
\Core\Config::obsolete( 'dhtml_filters', 'use_dynamic_filters' );
\Core\Config::obsolete( 'use_iis' );
\Core\Config::obsolete( 'page_title', 'top_include_page' );
\Core\Config::obsolete( 'limit_email_domain', 'limit_email_domains' );
\Core\Config::obsolete( 'file_upload_ftp_server' );
\Core\Config::obsolete( 'file_upload_ftp_user' );
\Core\Config::obsolete( 'file_upload_ftp_pass' );
\Core\Config::obsolete( 'mantistouch_url' );
\Core\Config::obsolete( 'custom_strings_file' );
\Core\Config::obsolete( 'mc_readonly_access_level_threshold', 'webservice_readonly_access_level_threshold' );
\Core\Config::obsolete( 'mc_readwrite_access_level_threshold', 'webservice_readwrite_access_level_threshold' );
\Core\Config::obsolete( 'mc_admin_access_level_threshold', 'webservice_admin_access_level_threshold' );
\Core\Config::obsolete( 'mc_specify_reporter_on_add_access_level_threshold', 'webservice_specify_reporter_on_add_access_level_threshold' );
\Core\Config::obsolete( 'mc_priority_enum_default_when_not_found', 'webservice_priority_enum_default_when_not_found' );
\Core\Config::obsolete( 'mc_severity_enum_default_when_not_found', 'webservice_severity_enum_default_when_not_found' );
\Core\Config::obsolete( 'mc_status_enum_default_when_not_found', 'webservice_status_enum_default_when_not_found' );
\Core\Config::obsolete( 'mc_resolution_enum_default_when_not_found', 'webservice_resolution_enum_default_when_not_found' );
\Core\Config::obsolete( 'mc_projection_enum_default_when_not_found', 'webservice_projection_enum_default_when_not_found' );
\Core\Config::obsolete( 'mc_eta_enum_default_when_not_found', 'webservice_eta_enum_default_when_not_found' );
\Core\Config::obsolete( 'mc_error_when_version_not_found', 'webservice_error_when_version_not_found' );
\Core\Config::obsolete( 'mc_version_when_not_found', 'webservice_version_when_not_found' );
env_obsolete( 'MANTIS_CONFIG', 'MANTIS_CONFIG_FOLDER' );
\Core\Config::obsolete( 'colour_project' );
\Core\Config::obsolete( 'colour_global' );
\Core\Config::obsolete( 'content_expire' );
\Core\Config::obsolete( 'use_javascript' );
\Core\Config::obsolete( 'recently_visited', 'recently_visited_count' );
\Core\Config::obsolete( 'email_set_category' );
