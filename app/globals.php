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


# @global array $g_event_cache
$g_event_cache = array(
	# Events specific to plugins
	'EVENT_PLUGIN_INIT' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Events specific to the core system
	'EVENT_CORE_READY' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# MantisBT Layout Events
	'EVENT_LAYOUT_RESOURCES' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_LAYOUT_BODY_BEGIN' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_LAYOUT_PAGE_HEADER' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_LAYOUT_CONTENT_BEGIN' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_LAYOUT_CONTENT_END' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_LAYOUT_PAGE_FOOTER' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_LAYOUT_BODY_END' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),

	# Events for displaying data
	'EVENT_DISPLAY_BUG_ID' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_DISPLAY_TEXT' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_DISPLAY_FORMATTED' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_DISPLAY_RSS' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_DISPLAY_EMAIL' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_DISPLAY_EMAIL_BUILD_SUBJECT' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),

	# Menu Events
	'EVENT_MENU_MAIN' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_MAIN_FRONT' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_MANAGE' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_MANAGE_CONFIG' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_SUMMARY' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_SUBMENU_SUMMARY' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_DOCS' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_ACCOUNT' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_FILTER' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_MENU_ISSUE' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),

	# Management pages
	'EVENT_MANAGE_OVERVIEW_INFO' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_MANAGE_PROJECT_CREATE_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_PROJECT_CREATE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_PROJECT_UPDATE_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_PROJECT_UPDATE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_PROJECT_DELETE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_PROJECT_PAGE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_VERSION_CREATE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_VERSION_UPDATE_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_VERSION_UPDATE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_MANAGE_VERSION_DELETE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# User account pages
	'EVENT_ACCOUNT_PREF_UPDATE_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_ACCOUNT_PREF_UPDATE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Bug filter events
	'EVENT_FILTER_FIELDS' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_FILTER_COLUMNS' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),

	# Bug report event
	'EVENT_REPORT_BUG_FORM_TOP' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_REPORT_BUG_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_REPORT_BUG_DATA' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_REPORT_BUG' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Bug view events
	'EVENT_VIEW_BUG_DETAILS' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_VIEW_BUG_EXTRA' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_VIEW_BUG_ATTACHMENT' => array('type' => EVENT_TYPE_OUTPUT, 'callbacks' => array()),
	'EVENT_VIEW_BUGNOTES_START' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_VIEW_BUGNOTE' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_VIEW_BUGNOTES_END' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Bug update events
	'EVENT_UPDATE_BUG_FORM_TOP' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_UPDATE_BUG_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_UPDATE_BUG_DATA' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_UPDATE_BUG' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_UPDATE_BUG_STATUS_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Other bug events
	'EVENT_BUG_DELETED' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_BUG_ACTION' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Bugnote events
	'EVENT_BUGNOTE_ADD_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_BUGNOTE_ADD' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_BUGNOTE_DATA' => array('type' => EVENT_TYPE_CHAIN, 'callbacks' => array()),
	'EVENT_BUGNOTE_EDIT_FORM' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_BUGNOTE_EDIT' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_BUGNOTE_DELETED' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_TAG_ATTACHED' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
	'EVENT_TAG_DETACHED' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),

	# Email notification events
	'EVENT_NOTIFY_USER_INCLUDE' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),
	'EVENT_NOTIFY_USER_EXCLUDE' => array('type' => EVENT_TYPE_DEFAULT, 'callbacks' => array()),

	# Wiki events
	'EVENT_WIKI_INIT' => array('type' => EVENT_TYPE_FIRST, 'callbacks' => array()),
	'EVENT_WIKI_LINK_BUG' => array('type' => EVENT_TYPE_FIRST, 'callbacks' => array()),
	'EVENT_WIKI_LINK_PROJECT' => array('type' => EVENT_TYPE_FIRST, 'callbacks' => array()),

	# Logging (tracing) events
	'EVENT_LOG' => array('type' => EVENT_TYPE_EXECUTE, 'callbacks' => array()),
);


# ########################################
# SECURITY NOTE: cache globals are initialized here to prevent them
#   being spoofed if register_globals is turned on

$g_cache_user_pref = array();
$g_cache_current_user_pref = array();


# this allows pages to override the current project settings.
# This typically applies to the view bug pages where the "current"
# project as used by the filters, etc, does not match the bug being viewed.
$g_project_override = null;
$g_cache_current_project = null;


$g_relationships = array();
$g_relationships[BUG_DEPENDANT] = array(
	'#forward' => true,
	'#complementary' => BUG_BLOCKS,
	'#description' => 'dependant_on',
	'#notify_added' => 'email_notification_title_for_action_dependant_on_relationship_added',
	'#notify_deleted' => 'email_notification_title_for_action_dependant_on_relationship_deleted',
	'#edge_style' => array(
		'color' => '#C00000',
		'dir' => 'back',
	),
);
$g_relationships[BUG_BLOCKS] = array(
	'#forward' => false,
	'#complementary' => BUG_DEPENDANT,
	'#description' => 'blocks',
	'#notify_added' => 'email_notification_title_for_action_blocks_relationship_added',
	'#notify_deleted' => 'email_notification_title_for_action_blocks_relationship_deleted',
	'#edge_style' => array(
		'color' => '#C00000',
		'dir' => 'forward',
	),
);
$g_relationships[BUG_DUPLICATE] = array(
	'#forward' => true,
	'#complementary' => BUG_HAS_DUPLICATE,
	'#description' => 'duplicate_of',
	'#notify_added' => 'email_notification_title_for_action_duplicate_of_relationship_added',
	'#notify_deleted' => 'email_notification_title_for_action_duplicate_of_relationship_deleted',
	'#edge_style' => array(
		'style' => 'dashed',
		'color' => '#808080',
	),
);
$g_relationships[BUG_HAS_DUPLICATE] = array(
	'#forward' => false,
	'#complementary' => BUG_DUPLICATE,
	'#description' => 'has_duplicate',
	'#notify_added' => 'email_notification_title_for_action_has_duplicate_relationship_added',
	'#notify_deleted' => 'email_notification_title_for_action_has_duplicate_relationship_deleted',
);
$g_relationships[BUG_RELATED] = array(
	'#forward' => true,
	'#complementary' => BUG_RELATED,
	'#description' => 'related_to',
	'#notify_added' => 'email_notification_title_for_action_related_to_relationship_added',
	'#notify_deleted' => 'email_notification_title_for_action_related_to_relationship_deleted',
);



$g_cache_project = array();
$g_cache_project_missing = array();
$g_cache_project_all = false;



$g_cache_project_hierarchy = null;
$g_cache_project_inheritance = null;
$g_cache_show_disabled = null;