<?php
# @global array $g_cache_access_matrix
$g_cache_access_matrix = array();

# @global array $g_cache_access_matrix_project_ids
$g_cache_access_matrix_project_ids = array();

# @global array $g_cache_access_matrix_user_ids
$g_cache_access_matrix_user_ids = array();

# @global array $g_script_login_cookie
$g_script_login_cookie = null;

# @global array $g_cache_anonymous_user_cookie_string
$g_cache_anonymous_user_cookie_string = null;

# @global array $g_cache_cookie_valid
$g_cache_cookie_valid = null;

# @global int $g_cache_current_user_id
$g_cache_current_user_id = null;

# Category data cache (to prevent excessive db queries)
$g_category_cache = array();	
$g_cache_category_project = null;


# @global string $g_current_collapse_section
$g_current_collapse_section = null;

# @global bool $g_open_collapse_section
$g_open_collapse_section = false;


# @global string $g_collapse_cache_token
$g_collapse_cache_token = null;

# Starts the buffering/compression (only if the compression option is ON)
# This variable is used internally.  It is not used for configuration
# @global bool $g_compression_started
$g_compression_started = false;

# Keeps track of whether the external files required for jscalendar to work
# have already been included in the output sent to the client. jscalendar
# will not work correctly if it is included multiple times on the same page.
# @global bool $g_jscalendar_included_already
$g_calendar_already_imported = false;

$g_cache_timezone = array();

$g_cache_file_count = array();


# ==========================================================================
# CACHING
# ==========================================================================

# @internal SECURITY NOTE: cache globals are initialized here to prevent them
# being spoofed if register_globals is turned on.
# We cache filter requests to reduce the number of SQL queries
# @global array $g_cache_filter
# @global array $g_cache_filter_db_filters
$g_cache_filter = array();
$g_cache_filter_db_filters = array();

$g_filter = null;

# Cache of localization strings in the language specified by the last
# lang_load call
$g_lang_strings = array();

# stack for language overrides
$g_lang_overrides = array();

# To be used in custom_strings_inc.php :
$g_active_language = '';

# Set up global for \Flickerbox\Token::purge_expired_once()
$g_tokens_purged = false;
	

$g_cache_ldap_email = array();


$g_rss_feed_url = null;

$g_robots_meta = '';

# flag for error handler to skip header menus
$g_error_send_page_header = true;

$g_stylesheets_included = array();
$g_scripts_included = array();


$g_log_levels = array(
	LOG_EMAIL => 'MAIL',
	LOG_EMAIL_RECIPIENT => 'RECIPIENT',
	LOG_FILTERING => 'FILTER',
	LOG_AJAX => 'AJAX',
	LOG_LDAP => 'LDAP',
	LOG_DATABASE => 'DB',
	LOG_WEBSERVICE => 'WEBSERVICE'
);


$g_error_parameters = array();
$g_errors_delayed = array();
$g_error_handled = false;
$g_error_proceed_url = null;
$g_error_send_page_header = true;



$g_session = null;


# ########################################
# SECURITY NOTE: cache globals are initialized here to prevent them
#   being spoofed if register_globals is turned on

$g_cache_sponsorships = array();



$g_cache_versions = array();
$g_cache_versions_project = null;


$g_cache_html_valid_tags = '';
$g_cache_html_valid_tags_single_line = '';


	
$g_string_process_bug_link_callback = array();
$g_string_process_bugnote_link_callback = array();