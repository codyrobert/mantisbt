<?php
namespace Flickerbox;


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
 * HTML API
 *
 * These functions control the HTML output of each page.
 *
 * This is the call order of these functions, should you need to figure out
 * which to modify or which to leave out:
 *
 * html_page_top
 *   html_page_top1
 *     html_begin
 *     html_head_begin
 *     html_content_type
 *     (Additional META tags: {@see $g_meta_include_file} and {@see robots_meta config})
 *     html_title
 *     html_css
 *     html_rss_link
 *     html_head_javascript
 *   (html_meta_redirect)
 *   html_page_top2
 *     html_page_top2a
 *       html_head_end
 *       html_body_begin
 *       html_top_banner
 *     html_login_info
 *     (print_project_menu_bar)
 *     print_menu
 *
 * ...Page content here...
 *
 * html_page_bottom
 *   html_page_bottom1
 *     (print_menu)
 *     html_page_bottom1a
 *       html_bottom_banner
 *       html_footer
 *       html_body_end
 *       html_end
 *
 * @package CoreAPI
 * @subpackage HTMLAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses php_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses rss_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'event_api.php' );
require_api( 'helper_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );


class HTML
{
	
	/**
	 * Sets the url for the rss link associated with the current page.
	 * null: means no feed (default).
	 * @param string $p_rss_feed_url RSS feed URL.
	 * @return void
	 */
	static function set_rss_link( $p_rss_feed_url ) {
		if( OFF != config_get( 'rss_enabled' ) ) {
			global $g_rss_feed_url;
			$g_rss_feed_url = $p_rss_feed_url;
		}
	}
	
	/**
	 * This method must be called before the html_page_top* methods.  It marks the page as not
	 * for indexing.
	 * @return void
	 */
	static function robots_noindex() {
		global $g_robots_meta;
		$g_robots_meta = 'noindex,follow';
	}
	
	/**
	 * Prints the link that allows auto-detection of the associated feed.
	 * @return void
	 */
	static function rss_link() {
		global $g_rss_feed_url;
	
		if( $g_rss_feed_url !== null ) {
			echo '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . \Flickerbox\String::attribute( $g_rss_feed_url ) . '" />' . "\n";
		}
	}
	
	/**
	 * Prints a <script> tag to include a JavaScript file.
	 * @param string $p_filename Name of JavaScript file (with extension) to include.
	 * @return void
	 */
	static function javascript_link( $p_filename ) {
		echo "\t", '<script type="text/javascript" src="', helper_mantis_url( 'javascript/' . $p_filename ), '"></script>' . "\n";
	}
	
	/**
	 * Defines the top of a HTML page
	 * @param string $p_page_title   Html page title.
	 * @param string $p_redirect_url URL to redirect to if necessary.
	 * @return void
	 */
	static function page_top( $p_page_title = null, $p_redirect_url = null ) {
		\Flickerbox\HTML::page_top1( $p_page_title );
		if( $p_redirect_url !== null ) {
			\Flickerbox\HTML::meta_redirect( $p_redirect_url );
		}
		\Flickerbox\HTML::page_top2();
	}
	
	/**
	 * Print the part of the page that comes before meta redirect tags should be inserted
	 * @param string $p_page_title Page title.
	 * @return void
	 */
	static function page_top1( $p_page_title = null ) {
		\Flickerbox\HTML::begin();
		\Flickerbox\HTML::head_begin();
	
		\Flickerbox\HTML::content_type();
		$t_meta = config_get_global( 'meta_include_file' );
		if( !\Flickerbox\Utility::is_blank( $t_meta ) ) {
			include( $t_meta );
		}
		global $g_robots_meta;
		if( !\Flickerbox\Utility::is_blank( $g_robots_meta ) ) {
			echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
		}
	
		\Flickerbox\HTML::title( $p_page_title );
		\Flickerbox\HTML::css();
		\Flickerbox\HTML::rss_link();
	
		$t_favicon_image = config_get( 'favicon_image' );
		if( !\Flickerbox\Utility::is_blank( $t_favicon_image ) ) {
			echo "\t", '<link rel="shortcut icon" href="', helper_mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
		}
	
		# Advertise the availability of the browser search plug-ins.
		echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" href="' . \Flickerbox\String::sanitize_url( 'browser_search_plugin.php?type=text', true ) . '" />' . "\n";
		echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" href="' . \Flickerbox\String::sanitize_url( 'browser_search_plugin.php?type=id', true ) . '" />' . "\n";
	
		\Flickerbox\HTML::head_javascript();
	}
	
	/**
	 * Print the part of the page that comes after meta tags, but before the actual page content
	 * @return void
	 */
	static function page_top2() {
		\Flickerbox\HTML::page_top2a();
	
		if( !db_is_connected() ) {
			return;
		}
	
		if( \Flickerbox\Auth::is_user_authenticated() ) {
			\Flickerbox\HTML::login_info();
	
			if( ON == config_get( 'show_project_menu_bar' ) ) {
				\Flickerbox\HTML::print_project_menu_bar();
				echo '<br />';
			}
		}
		\Flickerbox\HTML::print_menu();
		echo '<div id="content">', "\n";
		event_signal( 'EVENT_LAYOUT_CONTENT_BEGIN' );
	}
	
	/**
	 * Print the part of the page that comes after meta tags and before the
	 *  actual page content, but without login info or menus.  This is used
	 *  directly during the login process and other times when the user may
	 *  not be authenticated
	 * @return void
	 */
	static function page_top2a() {
		global $g_error_send_page_header;
	
		\Flickerbox\HTML::head_end();
		\Flickerbox\HTML::body_begin();
		$g_error_send_page_header = false;
		\Flickerbox\HTML::top_banner();
	}
	
	/**
	 * Print the part of the page that comes below the page content
	 * $p_file should always be the __FILE__ variable. This is passed to show source
	 * @param string $p_file Should always be the __FILE__ variable. This is passed to show source.
	 * @return void
	 */
	static function page_bottom( $p_file = null ) {
		\Flickerbox\HTML::page_bottom1( $p_file );
	}
	
	/**
	 * Print the part of the page that comes below the page content
	 * $p_file should always be the __FILE__ variable. This is passed to show source
	 * @param string $p_file Should always be the __FILE__ variable. This is passed to show source.
	 * @return void
	 */
	static function page_bottom1( $p_file = null ) {
		if( !db_is_connected() ) {
			return;
		}
	
		event_signal( 'EVENT_LAYOUT_CONTENT_END' );
		echo '</div>', "\n";
		if( config_get( 'show_footer_menu' ) ) {
			echo '<br />';
			\Flickerbox\HTML::print_menu();
		}
	
		\Flickerbox\HTML::page_bottom1a( $p_file );
	}
	
	/**
	 * Print the part of the page that comes below the page content but leave off
	 * the menu.  This is used during the login process and other times when the
	 * user may not be authenticated.
	 * @param string $p_file Should always be the __FILE__ variable.
	 * @return void
	 */
	static function page_bottom1a( $p_file = null ) {
		if( null === $p_file ) {
			$p_file = basename( $_SERVER['SCRIPT_NAME'] );
		}
	
		\Flickerbox\Error::print_delayed();
	
		\Flickerbox\HTML::bottom_banner();
		\Flickerbox\HTML::footer();
		\Flickerbox\HTML::body_end();
		\Flickerbox\HTML::end();
	}
	
	/**
	 * (1) Print the document type and the opening <html> tag
	 * @return void
	 */
	static function begin() {
		echo '<!DOCTYPE html>', "\n";
		echo '<html>', "\n";
	}
	
	/**
	 * (2) Begin the <head> section
	 * @return void
	 */
	static function head_begin() {
		echo '<head>', "\n";
	}
	
	/**
	 * (3) Print the content-type
	 * @return void
	 */
	static function content_type() {
		echo "\t", '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />', "\n";
	}
	
	/**
	 * (4) Print the window title
	 * @param string $p_page_title Window title.
	 * @return void
	 */
	static function title( $p_page_title = null ) {
		$t_page_title = \Flickerbox\String::html_specialchars( $p_page_title );
		$t_title = \Flickerbox\String::html_specialchars( config_get( 'window_title' ) );
		echo "\t", '<title>';
		if( empty( $t_page_title ) ) {
			echo $t_title;
		} else {
			if( empty( $t_title ) ) {
				echo $t_page_title;
			} else {
				echo $t_page_title . ' - ' . $t_title;
			}
		}
		echo '</title>', "\n";
	}
	
	/**
	 * Require a CSS file to be in html page headers
	 * @param string $p_stylesheet_path Path to CSS style sheet.
	 * @return void
	 */
	static function require_css( $p_stylesheet_path ) {
		global $g_stylesheets_included;
		$g_stylesheets_included[$p_stylesheet_path] = $p_stylesheet_path;
	}
	
	/**
	 * (5) Print the link to include the CSS file
	 * @return void
	 */
	static function css() {
		global $g_stylesheets_included;
		\Flickerbox\HTML::css_link( config_get( 'css_include_file' ) );
		\Flickerbox\HTML::css_link( 'jquery-ui-1.11.2.min.css' );
		\Flickerbox\HTML::css_link( 'common_config.php' );
		# Add right-to-left css if needed
		if( \Flickerbox\Lang::get( 'directionality' ) == 'rtl' ) {
			\Flickerbox\HTML::css_link( config_get( 'css_rtl_include_file' ) );
		}
		foreach( $g_stylesheets_included as $t_stylesheet_path ) {
			\Flickerbox\HTML::css_link( $t_stylesheet_path );
		}
	}
	
	/**
	 * Prints a CSS link
	 * @param string $p_filename Filename.
	 * @return void
	 */
	static function css_link( $p_filename ) {
		echo "\t", '<link rel="stylesheet" type="text/css" href="', \Flickerbox\String::sanitize_url( helper_mantis_url( 'css/' . $p_filename ), true ), '" />' . "\n";
	}
	
	
	/**
	 * (6) Print an HTML meta tag to redirect to another page
	 * This function is optional and may be called by pages that need a redirect.
	 * $p_time is the number of seconds to wait before redirecting.
	 * If we have handled any errors on this page return false and don't redirect.
	 *
	 * @param string  $p_url      The page to redirect: has to be a relative path.
	 * @param integer $p_time     Seconds to wait for before redirecting.
	 * @param boolean $p_sanitize Apply string_sanitize_url to passed URL.
	 * @return boolean
	 */
	static function meta_redirect( $p_url, $p_time = null, $p_sanitize = true ) {
		if( ON == config_get_global( 'stop_on_errors' ) && \Flickerbox\Error::handled() ) {
			return false;
		}
	
		if( null === $p_time ) {
			$p_time = \Flickerbox\Current_User::get_pref( 'redirect_delay' );
		}
	
		$t_url = config_get( 'path' );
		if( $p_sanitize ) {
			$t_url .= \Flickerbox\String::sanitize_url( $p_url );
		} else {
			$t_url .= $p_url;
		}
	
		$t_url = htmlspecialchars( $t_url );
	
		echo "\t" . '<meta http-equiv="Refresh" content="' . $p_time . ';URL=' . $t_url . '" />' . "\n";
	
		return true;
	}
	
	/**
	 * Require a javascript file to be in html page headers
	 * @param string $p_script_path Path to javascript file.
	 * @return void
	 */
	static function require_js( $p_script_path ) {
		global $g_scripts_included;
		$g_scripts_included[$p_script_path] = $p_script_path;
	}
	
	/**
	 * (6a) Javascript...
	 * @return void
	 */
	static function head_javascript() {
		global $g_scripts_included;
	
		echo "\t" . '<script type="text/javascript" src="' . helper_mantis_url( 'javascript_config.php' ) . '"></script>' . "\n";
		echo "\t" . '<script type="text/javascript" src="' . helper_mantis_url( 'javascript_translations.php' ) . '"></script>' . "\n";
		\Flickerbox\HTML::javascript_link( 'jquery-1.11.1.min.js' );
		\Flickerbox\HTML::javascript_link( 'jquery-ui-1.11.2.min.js' );
		\Flickerbox\HTML::javascript_link( 'common.js' );
		foreach ( $g_scripts_included as $t_script_path ) {
			\Flickerbox\HTML::javascript_link( $t_script_path );
		}
	}
	
	/**
	 * (7) End the <head> section
	 * @return void
	 */
	static function head_end() {
		event_signal( 'EVENT_LAYOUT_RESOURCES' );
	
		echo '</head>', "\n";
	}
	
	/**
	 * (8) Begin the <body> section
	 * @return void
	 */
	static function body_begin() {
		$t_centered_page = \Flickerbox\Utility::is_page_name( 'login_page' ) || \Flickerbox\Utility::is_page_name( 'signup_page' ) || \Flickerbox\Utility::is_page_name( 'signup' ) || \Flickerbox\Utility::is_page_name( 'lost_pwd_page' );
	
		echo '<body>', "\n";
	
		if( $t_centered_page ) {
			echo '<div id="mantis" class="centered_page">', "\n";
		} else {
			echo '<div id="mantis">', "\n";
		}
	
		event_signal( 'EVENT_LAYOUT_BODY_BEGIN' );
	}
	
	/**
	 * (9) Print a user-defined banner at the top of the page if there is one.
	 * @return void
	 */
	static function top_banner() {
		$t_page = config_get( 'top_include_page' );
		$t_logo_image = config_get( 'logo_image' );
		$t_logo_url = config_get( 'logo_url' );
	
		if( \Flickerbox\Utility::is_blank( $t_logo_image ) ) {
			$t_show_logo = false;
		} else {
			$t_show_logo = true;
			if( \Flickerbox\Utility::is_blank( $t_logo_url ) ) {
				$t_show_url = false;
			} else {
				$t_show_url = true;
			}
		}
	
		if( !\Flickerbox\Utility::is_blank( $t_page ) && file_exists( $t_page ) && !is_dir( $t_page ) ) {
			include( $t_page );
		} else if( $t_show_logo ) {
			echo '<div id="banner">';
			if( $t_show_url ) {
				echo '<a id="logo-link" href="', config_get( 'logo_url' ), '">';
			}
			$t_alternate_text = \Flickerbox\String::html_specialchars( config_get( 'window_title' ) );
			echo '<img id="logo-image" alt="', $t_alternate_text, '" src="' . helper_mantis_url( $t_logo_image ) . '" />';
			if( $t_show_url ) {
				echo '</a>';
			}
			echo '</div>';
		}
	
		event_signal( 'EVENT_LAYOUT_PAGE_HEADER' );
	}
	
	/**
	 * (10) Print the user's account information
	 * Also print the select box where users can switch projects
	 * @return void
	 */
	static function login_info() {
		$t_username = \Flickerbox\Current_User::get_field( 'username' );
		$t_access_level = get_enum_element( 'access_levels', \Flickerbox\Current_User::get_access_level() );
		$t_now = date( config_get( 'complete_date_format' ) );
		$t_realname = \Flickerbox\Current_User::get_field( 'realname' );
	
		echo '<div class="info-bar">' . "\n";
	
		# Login information
		echo '<div id="login-info">' . "\n";
		if( \Flickerbox\Current_User::is_anonymous() ) {
			$t_return_page = $_SERVER['SCRIPT_NAME'];
			if( isset( $_SERVER['QUERY_STRING'] ) ) {
				$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
			}
	
			$t_return_page = \Flickerbox\String::url( $t_return_page );
	
			echo "\t" . '<span id="logged-anon-label">' . \Flickerbox\Lang::get( 'anonymous' ) . '</span>' . "\n";
			echo "\t" . '<span id="login-link"><a href="' . helper_mantis_url( 'login_page.php?return=' . $t_return_page ) . '">' . \Flickerbox\Lang::get( 'login_link' ) . '</a></span>' . "\n";
			if( config_get_global( 'allow_signup' ) == ON ) {
				echo "\t" . '<span id="signup-link"><a href="' . helper_mantis_url( 'signup_page.php' ) . '">' . \Flickerbox\Lang::get( 'signup_link' ) . '</a></span>' . "\n";
			}
		} else {
			echo "\t" . '<span id="logged-in-label">' . \Flickerbox\Lang::get( 'logged_in_as' ) . '</span>' . "\n";
			echo "\t" . '<span id="logged-in-user">' . \Flickerbox\String::html_specialchars( $t_username ) . '</span>' . "\n";
			echo "\t" . '<span id="logged-in">';
			echo !\Flickerbox\Utility::is_blank( $t_realname ) ?  "\t" . '<span id="logged-in-realname">' . \Flickerbox\String::html_specialchars( $t_realname ) . '</span>' . "\n" : '';
			echo "\t" . '<span id="logged-in-accesslevel" class="' . $t_access_level . '">' . $t_access_level . '</span>' . "\n";
			echo "\t" . '</span>' . "\n";
		}
		echo '</div>' . "\n";
	
		# RSS feed
		if( OFF != config_get( 'rss_enabled' ) ) {
			echo '<div id="rss-feed">' . "\n";
			# Link to RSS issues feed for the selected project, including authentication details.
			echo "\t" . '<a href="' . htmlspecialchars( \Flickerbox\RSS::get_issues_feed_url() ) . '">' . "\n";
			echo "\t" . '<img src="' . helper_mantis_url( 'images/rss.png' ) . '" alt="' . \Flickerbox\Lang::get( 'rss' ) . '" title="' . \Flickerbox\Lang::get( 'rss' ) . '" />' . "\n";
			echo "\t" . '</a>' . "\n";
			echo '</div>' . "\n";
		}
	
		# Project Selector (hidden if only one project visisble to user)
		$t_show_project_selector = true;
		$t_project_ids = \Flickerbox\Current_User::get_accessible_projects();
		if( count( $t_project_ids ) == 1 ) {
			$t_project_id = (int)$t_project_ids[0];
			if( count( \Flickerbox\Current_User::get_accessible_subprojects( $t_project_id ) ) == 0 ) {
				$t_show_project_selector = false;
			}
		}
	
		if( $t_show_project_selector ) {
			echo '<div id="project-selector-div">';
			echo '<form method="post" id="form-set-project" action="' . helper_mantis_url( 'set_project.php' ) . '">';
			echo '<fieldset id="project-selector">';
			# CSRF protection not required here - form does not result in modifications
	
			echo '<label for="form-set-project-id">' . \Flickerbox\Lang::get( 'email_project' ) . '</label>';
			echo '<select id="form-set-project-id" name="project_id">';
			print_project_option_list( join( ';', helper_get_current_project_trace() ), true, null, true );
			echo '</select> ';
			echo '<input type="submit" class="button" value="' . \Flickerbox\Lang::get( 'switch' ) . '" />';
			echo '</fieldset>';
			echo '</form>';
			echo '</div>';
		} else {
			# User has only one project, set it as both current and default
			if( ALL_PROJECTS == helper_get_current_project() ) {
				helper_set_current_project( $t_project_id );
	
				if( !\Flickerbox\Current_User::is_protected() ) {
					\Flickerbox\Current_User::set_default_project( $t_project_id );
				}
	
				# Force reload of current page, except if we got here after
				# creating the first project
				$t_redirect_url = str_replace( config_get( 'short_path' ), '', $_SERVER['REQUEST_URI'] );
				if( 'manage_proj_create.php' != $t_redirect_url ) {
					\Flickerbox\HTML::meta_redirect( $t_redirect_url, 0, false );
				}
			}
		}
	
		# Current time
		echo '<div id="current-time">' . $t_now . '</div>';
		echo '</div>' . "\n";
	}
	
	/**
	 * (11) Print a user-defined banner at the bottom of the page if there is one.
	 * @return void
	 */
	static function bottom_banner() {
		$t_page = config_get( 'bottom_include_page' );
	
		if( !\Flickerbox\Utility::is_blank( $t_page ) && file_exists( $t_page ) && !is_dir( $t_page ) ) {
			include( $t_page );
		}
	}
	
	/**
	 * A function that outputs that an operation was successful and provides a redirect link.
	 * @param string $p_redirect_url The url to redirect to.
	 * @param string $p_message      Message to display to the user.
	 * @return void
	 */
	static function operation_successful( $p_redirect_url, $p_message = '' ) {
		echo '<div class="success-msg">';
	
		if( !\Flickerbox\Utility::is_blank( $p_message ) ) {
			echo $p_message . '<br />';
		}
	
		echo \Flickerbox\Lang::get( 'operation_successful' ).'<br />';
		print_bracket_link( $p_redirect_url, \Flickerbox\Lang::get( 'proceed' ) );
		echo '</div>';
	}
	
	/**
	 * Checks if the current page load was triggered by auto-refresh or real activity
	 * @return bool true: auto-refresh, false: triggered by user.
	 */
	static function is_auto_refresh() {
		return \Flickerbox\GPC::get_bool( 'refresh' );
	}
	
	/**
	 * (13) Print the page footer information
	 * @return void
	 */
	static function footer() {
		global $g_queries_array, $g_request_time;
	
		# If a user is logged in, update their last visit time.
		# We do this at the end of the page so that:
		#  1) we can display the user's last visit time on a page before updating it
		#  2) we don't invalidate the user cache immediately after fetching it
		#  3) don't do this on the password verification or update page, as it causes the
		#    verification comparison to fail
		#  4) don't do that on pages that auto-refresh (View Issues page).
		if( \Flickerbox\Auth::is_user_authenticated() &&
			!\Flickerbox\Current_User::is_anonymous() &&
			!( \Flickerbox\Utility::is_page_name( 'verify.php' ) || \Flickerbox\Utility::is_page_name( 'account_update.php' ) ) &&
			!\Flickerbox\HTML::is_auto_refresh() ) {
			$t_user_id = \Flickerbox\Auth::get_current_user_id();
			user_update_last_visit( $t_user_id );
		}
	
		echo '<div id="footer">' . "\n";
		echo '<hr />' . "\n";
	
		# We don't have a button anymore, so for now we will only show the resized
		# version of the logo when not on login page.
		if( !\Flickerbox\Utility::is_page_name( 'login_page' ) ) {
			echo "\t" . '<div id="powered-by-mantisbt-logo">' . "\n";
			$t_mantisbt_logo_url = helper_mantis_url( 'images/mantis_logo.png' );
			echo "\t\t" . '<a href="http://www.mantisbt.org"
				title="Mantis Bug Tracker: a free and open source web based bug tracking system.">
				<img src="' . $t_mantisbt_logo_url . '" width="102" height="35"
					alt="Powered by Mantis Bug Tracker: a free and open source web based bug tracking system." />
				</a>' . "\n";
			echo "\t" . '</div>' . "\n";
		}
	
		# Show MantisBT version and copyright statement
		$t_version_suffix = '';
		$t_copyright_years = ' 2000 - ' . date( 'Y' );
		if( config_get( 'show_version' ) == ON ) {
			$t_version_suffix = ' ' . htmlentities( MANTIS_VERSION . ' ' . config_get_global( 'version_suffix' ) );
		}
	
		echo '<address id="mantisbt-copyright">' . "\n";
		echo '<address id="version">Powered by <a href="http://www.mantisbt.org" title="bug tracking software">MantisBT ' . $t_version_suffix . "</a></address>\n";
		echo 'Copyright &copy;' . $t_copyright_years . ' MantisBT Team';
	
		# Show optional user-specified custom copyright statement
		$t_copyright_statement = config_get( 'copyright_statement' );
		if( $t_copyright_statement ) {
			echo "\t" . '<address id="user-copyright">' . $t_copyright_statement . '</address>' . "\n";
		}
	
		echo '</address>' . "\n";
	
		# Show contact information
		if( !\Flickerbox\Utility::is_page_name( 'login_page' ) ) {
			$t_webmaster_email = config_get( 'webmaster_email' );
			if( !\Flickerbox\Utility::is_blank( $t_webmaster_email ) ) {
				$t_webmaster_contact_information = sprintf( \Flickerbox\Lang::get( 'webmaster_contact_information' ), \Flickerbox\String::html_specialchars( $t_webmaster_email ) );
				echo "\t" . '<address id="webmaster-contact-information">' . $t_webmaster_contact_information . '</address>' . "\n";
			}
		}
	
		event_signal( 'EVENT_LAYOUT_PAGE_FOOTER' );
	
		# Print horizontal rule if any debugging statistics follow
		if( config_get( 'show_timer' ) || config_get( 'show_memory_usage' ) || config_get( 'show_queries_count' ) ) {
			echo "\t" . '<hr />' . "\n";
		}
	
		# Print the page execution time
		if( config_get( 'show_timer' ) ) {
			$t_page_execution_time = sprintf( \Flickerbox\Lang::get( 'page_execution_time' ), number_format( microtime( true ) - $g_request_time, 4 ) );
			echo "\t" . '<p id="page-execution-time">' . $t_page_execution_time . '</p>' . "\n";
		}
	
		# Print the page memory usage
		if( config_get( 'show_memory_usage' ) ) {
			$t_page_memory_usage = sprintf( \Flickerbox\Lang::get( 'memory_usage_in_kb' ), number_format( memory_get_peak_usage() / 1024 ) );
			echo "\t" . '<p id="page-memory-usage">' . $t_page_memory_usage . '</p>' . "\n";
		}
	
		# Determine number of unique queries executed
		if( config_get( 'show_queries_count' ) ) {
			$t_total_queries_count = count( $g_queries_array );
			$t_unique_queries_count = 0;
			$t_total_query_execution_time = 0;
			$t_unique_queries = array();
			for( $i = 0; $i < $t_total_queries_count; $i++ ) {
				if( !in_array( $g_queries_array[$i][0], $t_unique_queries ) ) {
					$t_unique_queries_count++;
					$g_queries_array[$i][3] = false;
					array_push( $t_unique_queries, $g_queries_array[$i][0] );
				} else {
					$g_queries_array[$i][3] = true;
				}
				$t_total_query_execution_time += $g_queries_array[$i][1];
			}
	
			$t_total_queries_executed = sprintf( \Flickerbox\Lang::get( 'total_queries_executed' ), $t_total_queries_count );
			echo "\t" . '<p id="total-queries-count">' . $t_total_queries_executed . '</p>' . "\n";
			if( config_get_global( 'db_log_queries' ) ) {
				$t_unique_queries_executed = sprintf( \Flickerbox\Lang::get( 'unique_queries_executed' ), $t_unique_queries_count );
				echo "\t" . '<p id="unique-queries-count">' . $t_unique_queries_executed . '</p>' . "\n";
			}
			$t_total_query_time = sprintf( \Flickerbox\Lang::get( 'total_query_execution_time' ), $t_total_query_execution_time );
			echo "\t" . '<p id="total-query-execution-time">' . $t_total_query_time . '</p>' . "\n";
		}
	
		# Print table of log events
		\Flickerbox\Log::print_to_page();
	
		echo '</div>' . "\n";
	}
	
	/**
	 * (14) End the <body> section
	 * @return void
	 */
	static function body_end() {
		event_signal( 'EVENT_LAYOUT_BODY_END' );
	
		echo '</div>', "\n";
	
		echo '</body>', "\n";
	}
	
	/**
	 * (15) Print the closing <html> tag
	 * @return void
	 */
	static function end() {
		global $g_email_stored;
	
		echo '</html>', "\n";
	
		if( $g_email_stored == true ) {
			if( function_exists( 'fastcgi_finish_request' ) ) {
				fastcgi_finish_request();
			}
			email_send_all();
		}
	}
	
	/**
	 * Prepare an array of additional menu options from a configuration variable
	 * @param string $p_config Configuration variable name.
	 * @return array
	 */
	static function prepare_custom_menu_options( $p_config ) {
		$t_custom_menu_options = config_get( $p_config );
		$t_options = array();
	
		foreach( $t_custom_menu_options as $t_custom_option ) {
			$t_access_level = $t_custom_option[1];
			if( \Flickerbox\Access::has_project_level( $t_access_level ) ) {
				$t_caption = \Flickerbox\String::html_specialchars( \Flickerbox\Lang::get_defaulted( $t_custom_option[0] ) );
				$t_link = \Flickerbox\String::attribute( $t_custom_option[2] );
				$t_options[] = '<a href="' . $t_link . '">' . $t_caption . '</a>';
			}
		}
	
		return $t_options;
	}
	
	/**
	 * Print the main menu
	 * @return void
	 */
	static function print_menu() {
		if( \Flickerbox\Auth::is_user_authenticated() ) {
			$t_protected = \Flickerbox\Current_User::get_field( 'protected' );
			$t_current_project = helper_get_current_project();
	
			$t_menu_options = array();
	
			# Main Page
			if( config_get( 'news_enabled' ) == ON ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'main_page.php' ) . '">' . \Flickerbox\Lang::get( 'main_link' ) . '</a>';
			}
	
			# Plugin / Event added options
			$t_event_menu_options = event_signal( 'EVENT_MENU_MAIN_FRONT' );
			foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
				foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
					if( is_array( $t_callback_menu_options ) ) {
						$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
					} else {
						if( !is_null( $t_callback_menu_options ) ) {
							$t_menu_options[] = $t_callback_menu_options;
						}
					}
				}
			}
	
			# My View
			$t_menu_options[] = '<a href="' . helper_mantis_url( 'my_view_page.php">' ) . \Flickerbox\Lang::get( 'my_view_link' ) . '</a>';
	
			# View Bugs
			$t_menu_options[] = '<a href="' . helper_mantis_url( 'view_all_bug_page.php">' ) . \Flickerbox\Lang::get( 'view_bugs_link' ) . '</a>';
	
			# Report Bugs
			if( \Flickerbox\Access::has_project_level( config_get( 'report_bug_threshold' ) ) ) {
				$t_menu_options[] = \Flickerbox\String::get_bug_report_link();
			}
	
			# Changelog Page
			if( \Flickerbox\Access::has_project_level( config_get( 'view_changelog_threshold' ) ) ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'changelog_page.php">' ) . \Flickerbox\Lang::get( 'changelog_link' ) . '</a>';
			}
	
			# Roadmap Page
			if( \Flickerbox\Access::has_project_level( config_get( 'roadmap_view_threshold' ) ) ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'roadmap_page.php">' ) . \Flickerbox\Lang::get( 'roadmap_link' ) . '</a>';
			}
	
			# Summary Page
			if( \Flickerbox\Access::has_project_level( config_get( 'view_summary_threshold' ) ) ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'summary_page.php">' ) . \Flickerbox\Lang::get( 'summary_link' ) . '</a>';
			}
	
			# Project Documentation Page
			if( ON == config_get( 'enable_project_documentation' ) ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'proj_doc_page.php">' ) . \Flickerbox\Lang::get( 'docs_link' ) . '</a>';
			}
	
			# Project Wiki
			if( config_get_global( 'wiki_enable' ) == ON ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'wiki.php?type=project&amp;id=' ) . $t_current_project . '">' . \Flickerbox\Lang::get( 'wiki' ) . '</a>';
			}
	
			# Plugin / Event added options
			$t_event_menu_options = event_signal( 'EVENT_MENU_MAIN' );
			foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
				foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
					if( is_array( $t_callback_menu_options ) ) {
						$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
					} else {
						if( !is_null( $t_callback_menu_options ) ) {
							$t_menu_options[] = $t_callback_menu_options;
						}
					}
				}
			}
	
			# Manage Users (admins) or Manage Project (managers) or Manage Custom Fields
			if( \Flickerbox\Access::has_global_level( config_get( 'manage_site_threshold' ) ) ) {
				$t_link = helper_mantis_url( 'manage_overview_page.php' );
				$t_menu_options[] = '<a class="manage-menu-link" href="' . $t_link . '">' . \Flickerbox\Lang::get( 'manage_link' ) . '</a>';
			} else {
				$t_show_access = min( config_get( 'manage_user_threshold' ), config_get( 'manage_project_threshold' ), config_get( 'manage_custom_fields_threshold' ) );
				if( \Flickerbox\Access::has_global_level( $t_show_access ) || \Flickerbox\Access::has_any_project( $t_show_access ) ) {
					$t_current_project = helper_get_current_project();
					if( \Flickerbox\Access::has_global_level( config_get( 'manage_user_threshold' ) ) ) {
						$t_link = helper_mantis_url( 'manage_user_page.php' );
					} else {
						if( \Flickerbox\Access::has_project_level( config_get( 'manage_project_threshold' ), $t_current_project ) && ( $t_current_project <> ALL_PROJECTS ) ) {
							$t_link = helper_mantis_url( 'manage_proj_edit_page.php?project_id=' ) . $t_current_project;
						} else {
							$t_link = helper_mantis_url( 'manage_proj_page.php' );
						}
					}
					$t_menu_options[] = '<a href="' . $t_link . '">' . \Flickerbox\Lang::get( 'manage_link' ) . '</a>';
				}
			}
	
			# News Page
			if( \Flickerbox\News::is_enabled() && \Flickerbox\Access::has_project_level( config_get( 'manage_news_threshold' ) ) ) {
				# Admin can edit news for All Projects (site-wide)
				if( ALL_PROJECTS != helper_get_current_project() || \Flickerbox\Current_User::is_administrator() ) {
					$t_menu_options[] = '<a href="' . helper_mantis_url( 'news_menu_page.php">' ) . \Flickerbox\Lang::get( 'edit_news_link' ) . '</a>';
				} else {
					$t_menu_options[] = '<a href="' . helper_mantis_url( 'login_select_proj_page.php">' ) . \Flickerbox\Lang::get( 'edit_news_link' ) . '</a>';
				}
			}
	
			# Account Page (only show accounts that are NOT protected)
			if( OFF == $t_protected ) {
				$t_menu_options[] = '<a class="account-menu-link" href="' . helper_mantis_url( 'account_page.php">' ) . \Flickerbox\Lang::get( 'account_link' ) . '</a>';
			}
	
			# Add custom options
			$t_custom_options = \Flickerbox\HTML::prepare_custom_menu_options( 'main_menu_custom_options' );
			$t_menu_options = array_merge( $t_menu_options, $t_custom_options );
	
			# Time Tracking / Billing
			if( config_get( 'time_tracking_enabled' ) && \Flickerbox\Access::has_global_level( config_get( 'time_tracking_reporting_threshold' ) ) ) {
				$t_menu_options[] = '<a href="' . helper_mantis_url( 'billing_page.php">' ) . \Flickerbox\Lang::get( 'time_tracking_billing_link' ) . '</a>';
			}
	
			# Logout (no if anonymously logged in)
			if( !\Flickerbox\Current_User::is_anonymous() ) {
				$t_menu_options[] = '<a id="logout-link" href="' . helper_mantis_url( 'logout_page.php">' ) . \Flickerbox\Lang::get( 'logout_link' ) . '</a>';
			}
	
			# Display main menu
			echo "\n" . '<div class="main-menu">'. "\n";
	
			# Menu items
			echo '<ul id="menu-items">' . "\n";
			echo "\t" . '<li>' . implode( $t_menu_options, '</li>' . "\n\t" . '<li>' ) . '</li>' . "\n";
			echo '</ul>' . "\n";
	
			# Bug Jump form
			echo '<div id="bug-jump" >';
			echo '<form method="post" class="bug-jump-form" action="' . helper_mantis_url( 'jump_to_bug.php' ) . '">';
			echo '<fieldset class="bug-jump">';
			# CSRF protection not required here - form does not result in modifications
			echo '<input type="hidden" name="bug_label" value="' . \Flickerbox\Lang::get( 'issue_id' ) . '" />';
			echo '<input type="text" name="bug_id" size="8" />&#160;';
			echo '<input type="submit" value="' . \Flickerbox\Lang::get( 'jump' ) . '" />&#160;';
			echo '</fieldset>';
			echo '</form>';
			echo '</div>' . "\n";
	
			echo '</div>' . "\n";
		}
	}
	
	/**
	 * Print the menu bar with a list of projects to which the user has access
	 * @return void
	 */
	static function print_project_menu_bar() {
		$t_project_ids = \Flickerbox\Current_User::get_accessible_projects();
	
		echo '<table class="width100" cellspacing="0">';
		echo '<tr>';
		echo '<td class="menu">';
		echo '<a href="' . helper_mantis_url( 'set_project.php?project_id=' . ALL_PROJECTS ) . '">' . \Flickerbox\Lang::get( 'all_projects' ) . '</a>';
	
		foreach( $t_project_ids as $t_id ) {
			echo ' | <a href="' . helper_mantis_url( 'set_project.php?project_id=' . $t_id ) . '">' . \Flickerbox\String::html_specialchars( project_get_field( $t_id, 'name' ) ) . '</a>';
			\Flickerbox\HTML::print_subproject_menu_bar( $t_id, $t_id . ';' );
		}
	
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
	
	/**
	 * Print the menu bar with a list of projects to which the user has access
	 * @todo check parents param - set_project.php?project_id=' . $p_parents . $t_subproject
	 * @param integer $p_project_id A Project id.
	 * @param string  $p_parents    Parent project identifiers.
	 * @return void
	 */
	static function print_subproject_menu_bar( $p_project_id, $p_parents = '' ) {
		$t_subprojects = \Flickerbox\Current_User::get_accessible_subprojects( $p_project_id );
		$t_char = ':';
		foreach( $t_subprojects as $t_subproject ) {
			echo $t_char . ' <a href="' . helper_mantis_url( 'set_project.php?project_id=' . $p_parents . $t_subproject ) . '">' . \Flickerbox\String::html_specialchars( project_get_field( $t_subproject, 'name' ) ) . '</a>';
			\Flickerbox\HTML::print_subproject_menu_bar( $t_subproject, $p_parents . $t_subproject . ';' );
			$t_char = ',';
		}
	}
	
	/**
	 * Print the menu for the graph summary section
	 * @return void
	 */
	static function print_summary_submenu() {
		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_SUBMENU_SUMMARY' );
		$t_menu_options = array();
		foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
			foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
				if( is_array( $t_callback_menu_options ) ) {
					$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
				} else {
					if( !is_null( $t_callback_menu_options ) ) {
						$t_menu_options[] = $t_callback_menu_options;
					}
				}
			}
		}
	
		if( sizeof( $t_menu_options ) > 0 ) {
			echo "\t" . '<div id="summary-submenu">' . "\n";
			echo "\t\t" . '<ul class="menu">' . "\n";
			# Plugins menu items - these are cooked links
			foreach ( $t_menu_options as $t_menu_item ) {
				echo "\t\t\t" . '<li>', $t_menu_item, '</li>' . "\n";
			}
			echo "\t\t" . '</ul>' . "\n";
			echo "\t" . '</div>' . "\n";
		}
	}
	
	/**
	 * Print the menu for the manage section
	 *
	 * @param string $p_page Specifies the current page name so it's link can be disabled.
	 * @return void
	 */
	static function print_manage_menu( $p_page = '' ) {
		$t_pages = array();
		if( \Flickerbox\Access::has_global_level( config_get( 'manage_user_threshold' ) ) ) {
			$t_pages['manage_user_page.php'] = array( 'url'   => 'manage_user_page.php', 'label' => 'manage_users_link' );
		}
		if( \Flickerbox\Access::has_project_level( config_get( 'manage_project_threshold' ) ) ) {
			$t_pages['manage_proj_page.php'] = array( 'url'   => 'manage_proj_page.php', 'label' => 'manage_projects_link' );
		}
		if( \Flickerbox\Access::has_global_level( config_get( 'tag_edit_threshold' ) ) ) {
			$t_pages['manage_tags_page.php'] = array( 'url'   => 'manage_tags_page.php', 'label' => 'manage_tags_link' );
		}
		if( \Flickerbox\Access::has_global_level( config_get( 'manage_custom_fields_threshold' ) ) ) {
			$t_pages['manage_custom_field_page.php'] = array( 'url'   => 'manage_custom_field_page.php', 'label' => 'manage_custom_field_link' );
		}
		if( config_get( 'enable_profiles' ) == ON && \Flickerbox\Access::has_global_level( config_get( 'manage_global_profile_threshold' ) ) ) {
			$t_pages['manage_prof_menu_page.php'] = array( 'url'   => 'manage_prof_menu_page.php', 'label' => 'manage_global_profiles_link' );
		}
		if( \Flickerbox\Access::has_global_level( config_get( 'manage_plugin_threshold' ) ) ) {
			$t_pages['manage_plugin_page.php'] = array( 'url'   => 'manage_plugin_page.php', 'label' => 'manage_plugin_link' );
		}
	
		if( \Flickerbox\Access::has_project_level( config_get( 'manage_configuration_threshold' ) ) ) {
			if( \Flickerbox\Access::has_global_level( config_get( 'view_configuration_threshold' ) ) ) {
				$t_pages['adm_config_report.php'] = array( 'url'   => 'adm_config_report.php', 'label' => 'manage_config_link' );
			} else {
				$t_pages['adm_permissions_report.php'] = array( 'url'   => 'adm_permissions_report.php', 'label' => 'manage_config_link' );
			}
		}
		# Remove the link from the current page
		if( isset( $t_pages[$p_page] ) ) {
			$t_pages[$p_page]['url'] = '';
		}
	
		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_MENU_MANAGE' );
		$t_menu_options = array();
		foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
			foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
				if( is_array( $t_callback_menu_options ) ) {
					$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
				} else {
					if( !is_null( $t_callback_menu_options ) ) {
						$t_menu_options[] = $t_callback_menu_options;
					}
				}
			}
		}
	
		echo "\n" . '<div id="manage-menu">' . "\n";
		echo '<ul class="menu">';
		foreach( $t_pages as $t_page ) {
			if( $t_page['url'] == '' ) {
				echo '<li><span>', \Flickerbox\Lang::get( $t_page['label'] ), '</span></li>';
			} else {
				echo '<li><a href="'. helper_mantis_url( $t_page['url'] ) .'">' . \Flickerbox\Lang::get( $t_page['label'] ) . '</a></li>';
			}
		}
	
		# Plugins menu items - these are cooked links
		foreach( $t_menu_options as $t_menu_item ) {
			echo '<li>', $t_menu_item, '</li>';
		}
	
		echo '</ul>';
		echo '</div>';
	}
	
	/**
	 * Print the menu for the manage configuration section
	 * @param string $p_page Specifies the current page name so it's link can be disabled.
	 * @return void
	 */
	static function print_manage_config_menu( $p_page = '' ) {
		if( !\Flickerbox\Access::has_project_level( config_get( 'manage_configuration_threshold' ) ) ) {
			return;
		}
	
		$t_pages = array();
	
		if( \Flickerbox\Access::has_global_level( config_get( 'view_configuration_threshold' ) ) ) {
			$t_pages['adm_config_report.php'] = array( 'url'   => 'adm_config_report.php',
			                                           'label' => 'configuration_report' );
		}
	
		$t_pages['adm_permissions_report.php'] = array( 'url'   => 'adm_permissions_report.php',
		                                                'label' => 'permissions_summary_report' );
	
		$t_pages['manage_config_work_threshold_page.php'] = array( 'url'   => 'manage_config_work_threshold_page.php',
		                                                           'label' => 'manage_threshold_config' );
	
		$t_pages['manage_config_workflow_page.php'] = array( 'url'   => 'manage_config_workflow_page.php',
		                                                     'label' => 'manage_workflow_config' );
	
		if( config_get( 'relationship_graph_enable' ) ) {
			$t_pages['manage_config_workflow_graph_page.php'] = array( 'url'   => 'manage_config_workflow_graph_page.php',
			                                                           'label' => 'manage_workflow_graph' );
		}
	
		$t_pages['manage_config_email_page.php'] = array( 'url'   => 'manage_config_email_page.php',
		                                                  'label' => 'manage_email_config' );
	
		$t_pages['manage_config_columns_page.php'] = array( 'url'   => 'manage_config_columns_page.php',
		                                                    'label' => 'manage_columns_config' );
	
		# Remove the link from the current page
		if( isset( $t_pages[$p_page] ) ) {
			$t_pages[$p_page]['url'] = '';
		}
	
		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_MENU_MANAGE_CONFIG' );
		$t_menu_options = array();
		foreach ( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
			foreach ( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
				if( is_array( $t_callback_menu_options ) ) {
					$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
				} else {
					if( !is_null( $t_callback_menu_options ) ) {
						$t_menu_options[] = $t_callback_menu_options;
					}
				}
			}
		}
	
		echo '<div id="manage-config-menu">';
		echo '<ul class="menu">';
		foreach ( $t_pages as $t_page ) {
			if( $t_page['url'] == '' ) {
				echo '<li><span>', \Flickerbox\Lang::get( $t_page['label'] ), '</span></li>';
			} else {
				echo '<li><a href="'. helper_mantis_url( $t_page['url'] ) .'">' . \Flickerbox\Lang::get( $t_page['label'] ) . '</a></li>';
			}
		}
	
		foreach ( $t_menu_options as $t_menu_item ) {
			echo '<li><span>', $t_menu_item, '</span></li>';
		}
	
		echo '</ul>';
		echo '</div>';
	}
	
	/**
	 * Print the menu for the account section
	 * @param string $p_page Specifies the current page name so it's link can be disabled.
	 * @return void
	 */
	static function print_account_menu( $p_page = '' ) {
		$t_pages['account_page.php'] = array( 'url'=>'account_page.php', 'label'=>'account_link' );
		$t_pages['account_prefs_page.php'] = array( 'url'=>'account_prefs_page.php', 'label'=>'change_preferences_link' );
		$t_pages['account_manage_columns_page.php'] = array( 'url'=>'account_manage_columns_page.php', 'label'=>'manage_columns_config' );
	
		if( config_get( 'enable_profiles' ) == ON && \Flickerbox\Access::has_project_level( config_get( 'add_profile_threshold' ) ) ) {
			$t_pages['account_prof_menu_page.php'] = array( 'url'=>'account_prof_menu_page.php', 'label'=>'manage_profiles_link' );
		}
	
		if( config_get( 'enable_sponsorship' ) == ON && \Flickerbox\Access::has_project_level( config_get( 'view_sponsorship_total_threshold' ) ) && !\Flickerbox\Current_User::is_anonymous() ) {
			$t_pages['account_sponsor_page.php'] = array( 'url'=>'account_sponsor_page.php', 'label'=>'my_sponsorship' );
		}
	
		# Remove the link from the current page
		if( isset( $t_pages[$p_page] ) ) {
			$t_pages[$p_page]['url'] = '';
		}
	
		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_MENU_ACCOUNT' );
		$t_menu_options = array();
		foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
			foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
				if( is_array( $t_callback_menu_options ) ) {
					$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
				} else {
					if( !is_null( $t_callback_menu_options ) ) {
						$t_menu_options[] = $t_callback_menu_options;
					}
				}
			}
		}
	
		echo '<div id="account-menu">';
		echo '<ul class="menu">';
		foreach ( $t_pages as $t_page ) {
			if( $t_page['url'] == '' ) {
				echo '<li><span>', \Flickerbox\Lang::get( $t_page['label'] ), '</span></li>';
			} else {
				echo '<li><a href="'. helper_mantis_url( $t_page['url'] ) .'">' . \Flickerbox\Lang::get( $t_page['label'] ) . '</a></li>';
			}
		}
	
		# Plugins menu items - these are cooked links
		foreach ( $t_menu_options as $t_menu_item ) {
			echo '<li>', $t_menu_item, '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	/**
	 * Print the menu for the documentation section
	 * @param string $p_page Specifies the current page name so it's link can be disabled.
	 * @return void
	 */
	static function print_doc_menu( $p_page = '' ) {
		# User Documentation
		$t_doc_url = config_get( 'manual_url' );
		if( is_null( parse_url( $t_doc_url, PHP_URL_SCHEME ) ) ) {
			# URL has no scheme, so it is relative to MantisBT root
			if( \Flickerbox\Utility::is_blank( $t_doc_url ) ||
				!file_exists( config_get_global( 'absolute_path' ) . $t_doc_url )
			) {
				# Local documentation not available, use online docs
				$t_doc_url = 'http://www.mantisbt.org/documentation.php';
			} else {
				$t_doc_url = helper_mantis_url( $t_doc_url );
			}
		}
	
		$t_pages[$t_doc_url] = array(
			'url'   => $t_doc_url,
			'label' => 'user_documentation'
		);
	
		# Project Documentation
		$t_pages['proj_doc_page.php'] = array(
			'url'   => helper_mantis_url( 'proj_doc_page.php' ),
			'label' => 'project_documentation'
		);
	
		# Add File
		if( \Flickerbox\File::allow_project_upload() ) {
			$t_pages['proj_doc_add_page.php'] = array(
				'url'   => helper_mantis_url( 'proj_doc_add_page.php' ),
				'label' => 'add_file'
			);
		}
	
		# Remove the link from the current page
		if( isset( $t_pages[$p_page] ) ) {
			$t_pages[$p_page]['url'] = '';
		}
	
		echo '<div id="doc-menu">';
		echo '<ul class="menu">';
		foreach ( $t_pages as $t_page ) {
			if( $t_page['url'] == '' ) {
				echo '<li>', \Flickerbox\Lang::get( $t_page['label'] ), '</li>';
			} else {
				echo '<li><a href="'. $t_page['url'] .'">' . \Flickerbox\Lang::get( $t_page['label'] ) . '</a></li>';
			}
		}
		echo '</ul>';
		echo '</div>';
	}
	
	/**
	 * Print the menu for the summary section
	 * @param string $p_page Specifies the current page name so it's link can be disabled.
	 * @return void
	 */
	static function print_summary_menu( $p_page = '' ) {
		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_MENU_SUMMARY' );
		$t_menu_options = array();
		foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
			foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
				if( is_array( $t_callback_menu_options ) ) {
					$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
				} else {
					if( !is_null( $t_callback_menu_options ) ) {
						$t_menu_options[] = $t_callback_menu_options;
					}
				}
			}
		}
	
		$t_pages['summary_page.php'] = array( 'url'=>'summary_page.php', 'label'=>'summary_link' );
		# Remove the link from the current page
		if( isset( $t_pages[$p_page] ) ) {
			$t_pages[$p_page]['url'] = '';
		}
	
		echo '<div id="summary-menu">';
		echo '<ul class="menu">';
	
		foreach ( $t_pages as $t_page ) {
			if( $t_page['url'] == '' ) {
				echo '<li>', \Flickerbox\Lang::get( $t_page['label'] ), '</li>';
			} else {
				echo '<li><a href="'. helper_mantis_url( $t_page['url'] ) .'">' . \Flickerbox\Lang::get( $t_page['label'] ) . '</a></li>';
			}
		}
	
		# Plugins menu items - these are cooked links
		foreach ( $t_menu_options as $t_menu_item ) {
			echo '<li>', $t_menu_item, '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	/**
	 * Print the color legend for the status colors
	 * @return void
	 */
	static function status_legend() {
		# Don't show the legend if only one status is selected by the current filter
		$t_current_filter = \Flickerbox\Current_User::get_bug_filter();
		if( $t_current_filter === false ) {
			$t_current_filter = \Flickerbox\Filter::get_default();
		}
		$t_simple_filter = $t_current_filter['_view_type'] == 'simple';
		if( $t_simple_filter ) {
			if( !\Flickerbox\Filter::field_is_any( $t_current_filter[FILTER_PROPERTY_STATUS][0] ) ) {
				return;
			}
		}
	
		$t_status_array = \MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
		$t_status_names = \MantisEnum::getAssocArrayIndexedByValues( \Flickerbox\Lang::get( 'status_enum_string' ) );
	
		# read through the list and eliminate unused ones for the selected project
		# assumes that all status are are in the enum array
		$t_workflow = config_get( 'status_enum_workflow' );
		if( !empty( $t_workflow ) ) {
			foreach( $t_status_array as $t_status => $t_name ) {
				if( !isset( $t_workflow[$t_status] ) ) {
	
					# drop elements that are not in the workflow
					unset( $t_status_array[$t_status] );
				}
			}
		}
	
		# Remove status values that won't appear as a result of the current filter
		foreach( $t_status_array as $t_status => $t_name ) {
			if( $t_simple_filter ) {
				if( !\Flickerbox\Filter::field_is_none( $t_current_filter[FILTER_PROPERTY_HIDE_STATUS][0] ) &&
					$t_status >= $t_current_filter[FILTER_PROPERTY_HIDE_STATUS][0] ) {
					unset( $t_status_array[$t_status] );
				}
			} else {
				if( !in_array( META_FILTER_ANY, $t_current_filter[FILTER_PROPERTY_STATUS] ) &&
					!in_array( $t_status, $t_current_filter[FILTER_PROPERTY_STATUS] ) ) {
					unset( $t_status_array[$t_status] );
				}
			}
		}
	
		# If there aren't at least two statuses showable by the current filter,
		# don't draw the status bar
		if( count( $t_status_array ) <= 1 ) {
			return;
		}
	
		echo '<br />';
		echo '<table class="status-legend width100" cellspacing="1">';
		echo '<tr>';
	
		# draw the status bar
		$t_status_enum_string = config_get( 'status_enum_string' );
		foreach( $t_status_array as $t_status => $t_name ) {
			$t_val = isset( $t_status_names[$t_status] ) ? $t_status_names[$t_status] : $t_status_array[$t_status];
			$t_status_label = \MantisEnum::getLabel( $t_status_enum_string, $t_status );
	
			echo '<td class="small-caption ' . $t_status_label . '-color">' . $t_val . '</td>';
		}
	
		echo '</tr>';
		echo '</table>';
		if( ON == config_get( 'status_percentage_legend' ) ) {
			\Flickerbox\HTML::status_percentage_legend();
		}
	}
	
	/**
	 * Print the legend for the status percentage
	 * @return void
	 */
	static function status_percentage_legend() {
		$t_status_percents = get_percentage_by_status();
		$t_status_enum_string = config_get( 'status_enum_string' );
		$t_enum_values = \MantisEnum::getValues( $t_status_enum_string );
		$t_enum_count = count( $t_enum_values );
	
		$t_bug_count = array_sum( $t_status_percents );
	
		if( $t_bug_count > 0 ) {
			echo '<br />';
			echo '<table class="width100" cellspacing="1">';
			echo '<tr>';
			echo '<td class="form-title" colspan="' . $t_enum_count . '">' . \Flickerbox\Lang::get( 'issue_status_percentage' ) . '</td>';
			echo '</tr>';
			echo '<tr>';
	
			foreach ( $t_enum_values as $t_status ) {
				$t_percent = ( isset( $t_status_percents[$t_status] ) ?  $t_status_percents[$t_status] : 0 );
	
				if( $t_percent > 0 ) {
					$t_status_label = \MantisEnum::getLabel( $t_status_enum_string, $t_status );
					echo '<td class="small-caption-center ' . $t_status_label . '-color ' . $t_status_label . '-percentage">' . $t_percent . '%</td>';
				}
			}
	
			echo '</tr>';
			echo '</table>';
		}
	}
	
	/**
	 * Print an html button inside a form
	 * @param string $p_action      Form Action.
	 * @param string $p_button_text Button Text.
	 * @param array  $p_fields      An array of hidden fields to include on the form.
	 * @param string $p_method      Form submit method - default post.
	 * @return void
	 */
	static function button( $p_action, $p_button_text, array $p_fields = array(), $p_method = 'post' ) {
		$t_form_name = explode( '.php', $p_action, 2 );
		$p_action = urlencode( $p_action );
		$p_button_text = \Flickerbox\String::attribute( $p_button_text );
	
		if( strtolower( $p_method ) == 'get' ) {
			$t_method = 'get';
		} else {
			$t_method = 'post';
		}
	
		echo '<form method="' . $t_method . '" action="' . $p_action . '" class="action-button">' . "\n";
		echo "\t" . '<fieldset>';
		# Add a CSRF token only when the form is being sent via the POST method
		if( $t_method == 'post' ) {
			echo \Flickerbox\Form::security_field( $t_form_name[0] );
		}
	
		foreach( $p_fields as $t_key => $t_val ) {
			$t_key = \Flickerbox\String::attribute( $t_key );
			$t_val = \Flickerbox\String::attribute( $t_val );
	
			echo "\t\t" . '<input type="hidden" name="' . $t_key . '" value="' . $t_val . '" />' . "\n";
		}
	
		echo "\t\t" . '<input type="submit" class="button" value="' . $p_button_text . '" />' . "\n";
		echo "\t" . '</fieldset>';
		echo '</form>' . "\n";
	}
	
	/**
	 * Print a button to update the given bug
	 * @param integer $p_bug_id A Bug identifier.
	 * @return void
	 */
	static function button_bug_update( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( \Flickerbox\String::get_bug_update_page(), \Flickerbox\Lang::get( 'update_bug_button' ), array( 'bug_id' => $p_bug_id ) );
		}
	}
	
	/**
	 * Print Change Status to: button
	 * This code is similar to print_status_option_list except
	 * there is no masking, except for the current state
	 *
	 * @param BugData $p_bug A valid bug object.
	 * @return void
	 */
	static function button_bug_change_status( BugData $p_bug ) {
		$t_current_access = \Flickerbox\Access::get_project_level( $p_bug->project_id );
	
		# User must have rights to change status to use this button
		if( !\Flickerbox\Access::has_bug_level( config_get( 'update_bug_status_threshold' ), $p_bug->id ) ) {
			return;
		}
	
		$t_enum_list = get_status_option_list(
			$t_current_access,
			$p_bug->status,
			false,
			# Add close if user is bug's reporter, still has rights to report issues
			# (to prevent users downgraded to viewers from updating issues) and
			# reporters are allowed to close their own issues
			(  bug_is_user_reporter( $p_bug->id, \Flickerbox\Auth::get_current_user_id() )
			&& \Flickerbox\Access::has_bug_level( config_get( 'report_bug_threshold' ), $p_bug->id )
			&& ON == config_get( 'allow_reporter_close' )
			),
			$p_bug->project_id );
	
		if( count( $t_enum_list ) > 0 ) {
			# resort the list into ascending order after noting the key from the first element (the default)
			$t_default_arr = each( $t_enum_list );
			$t_default = $t_default_arr['key'];
			ksort( $t_enum_list );
			reset( $t_enum_list );
	
			echo '<form method="post" action="bug_change_status_page.php">';
			# CSRF protection not required here - form does not result in modifications
	
			$t_button_text = \Flickerbox\Lang::get( 'bug_status_to_button' );
			echo '<input type="submit" class="button" value="' . $t_button_text . '" />';
	
			echo ' <select name="new_status">';
	
			# space at beginning of line is important
			foreach( $t_enum_list as $t_key => $t_val ) {
				echo '<option value="' . $t_key . '" ';
				check_selected( $t_key, $t_default );
				echo '>' . $t_val . '</option>';
			}
			echo '</select>';
	
			$t_bug_id = \Flickerbox\String::attribute( $p_bug->id );
			echo '<input type="hidden" name="id" value="' . $t_bug_id . '" />' . "\n";
	
			echo '</form>' . "\n";
		}
	}
	
	/**
	 * Print Assign To: combo box of possible handlers
	 * @param BugData $p_bug Bug object.
	 * @return void
	 */
	static function button_bug_assign_to( BugData $p_bug ) {
		# make sure status is allowed of assign would cause auto-set-status
		# workflow implementation
		if( ON == config_get( 'auto_set_status_to_assigned' )
			&& !bug_check_workflow( $p_bug->status, config_get( 'bug_assigned_status' ) )
		) {
			return;
		}
	
		# make sure current user has access to modify bugs.
		if( !\Flickerbox\Access::has_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $p_bug->id ) ) {
			return;
		}
	
		$t_current_user_id = \Flickerbox\Auth::get_current_user_id();
		$t_options = array();
		$t_default_assign_to = null;
	
		if( ( $p_bug->handler_id != $t_current_user_id )
			&& \Flickerbox\Access::has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug->id, $t_current_user_id )
		) {
			$t_options[] = array(
				$t_current_user_id,
				'[' . \Flickerbox\Lang::get( 'myself' ) . ']',
			);
			$t_default_assign_to = $t_current_user_id;
		}
	
		if( ( $p_bug->handler_id != $p_bug->reporter_id )
			&& user_exists( $p_bug->reporter_id )
			&& \Flickerbox\Access::has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug->id, $p_bug->reporter_id )
		) {
			$t_options[] = array(
				$p_bug->reporter_id,
				'[' . \Flickerbox\Lang::get( 'reporter' ) . ']',
			);
	
			if( $t_default_assign_to === null ) {
				$t_default_assign_to = $p_bug->reporter_id;
			}
		}
	
		echo '<form method="post" action="bug_update.php">';
		echo \Flickerbox\Form::security_field( 'bug_update' );
		echo '<input type="hidden" name="last_updated" value="' . $p_bug->last_updated . '" />';
		echo '<input type="hidden" name="action_type" value="' . BUG_UPDATE_TYPE_ASSIGN . '" />';
	
		$t_button_text = \Flickerbox\Lang::get( 'bug_assign_to_button' );
		echo '<input type="submit" class="button" value="' . $t_button_text . '" />';
	
		echo ' <select name="handler_id">';
	
		# space at beginning of line is important
	
		$t_already_selected = false;
	
		foreach( $t_options as $t_entry ) {
			$t_id = (int)$t_entry[0];
			$t_caption = \Flickerbox\String::attribute( $t_entry[1] );
	
			# if current user and reporter can't be selected, then select the first
			# user in the list.
			if( $t_default_assign_to === null ) {
				$t_default_assign_to = $t_id;
			}
	
			echo '<option value="' . $t_id . '" ';
	
			if( ( $t_id == $t_default_assign_to ) && !$t_already_selected ) {
				check_selected( $t_id, $t_default_assign_to );
				$t_already_selected = true;
			}
	
			echo '>' . $t_caption . '</option>';
		}
	
		# allow un-assigning if already assigned.
		if( $p_bug->handler_id != 0 ) {
			echo '<option value="0"></option>';
		}
	
		# 0 means currently selected
		print_assign_to_option_list( 0, $p_bug->project_id );
		echo '</select>';
	
		$t_bug_id = \Flickerbox\String::attribute( $p_bug->id );
		echo '<input type="hidden" name="bug_id" value="' . $t_bug_id . '" />' . "\n";
	
		echo '</form>' . "\n";
	}
	
	/**
	 * Print a button to move the given bug to a different project
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_move( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'move_bug_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( 'bug_actiongroup_page.php', \Flickerbox\Lang::get( 'move_bug_button' ), array( 'bug_arr[]' => $p_bug_id, 'action' => 'MOVE' ) );
		}
	}
	
	/**
	 * Print a button to clone the given bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_create_child( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'report_bug_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( \Flickerbox\String::get_bug_report_url(), \Flickerbox\Lang::get( 'create_child_bug_button' ), array( 'm_id' => $p_bug_id ) );
		}
	}
	
	/**
	 * Print a button to reopen the given bug
	 * @param BugData $p_bug A valid bug object.
	 * @return void
	 */
	static function button_bug_reopen( BugData $p_bug ) {
		if( \Flickerbox\Access::can_reopen_bug( $p_bug ) ) {
			$t_reopen_status = config_get( 'bug_reopen_status', null, null, $p_bug->project_id );
			\Flickerbox\HTML::button(
				'bug_change_status_page.php',
				\Flickerbox\Lang::get( 'reopen_bug_button' ),
				array( 'id' => $p_bug->id, 'new_status' => $t_reopen_status, 'reopen_flag' => ON ) );
		}
	}
	
	/**
	 * Print a button to close the given bug
	 * Only if user can close bugs and workflow allows moving them to that status
	 * @param BugData $p_bug A valid bug object.
	 * @return void
	 */
	static function button_bug_close( BugData $p_bug ) {
		$t_closed_status = config_get( 'bug_closed_status_threshold', null, null, $p_bug->project_id );
		if( \Flickerbox\Access::can_close_bug( $p_bug )
			&& bug_check_workflow( $p_bug->status, $t_closed_status )
		) {
			\Flickerbox\HTML::button(
				'bug_change_status_page.php',
				\Flickerbox\Lang::get( 'close_bug_button' ),
				array( 'id' => $p_bug->id, 'new_status' => $t_closed_status ) );
		}
	}
	
	/**
	 * Print a button to monitor the given bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_monitor( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'monitor_bug_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( 'bug_monitor_add.php', \Flickerbox\Lang::get( 'monitor_bug_button' ), array( 'bug_id' => $p_bug_id ) );
		}
	}
	
	/**
	 * Print a button to unmonitor the given bug
	 * no reason to ever disallow someone from unmonitoring a bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_unmonitor( $p_bug_id ) {
		\Flickerbox\HTML::button( 'bug_monitor_delete.php', \Flickerbox\Lang::get( 'unmonitor_bug_button' ), array( 'bug_id' => $p_bug_id ) );
	}
	
	/**
	 * Print a button to stick the given bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_stick( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'set_bug_sticky_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( 'bug_stick.php', \Flickerbox\Lang::get( 'stick_bug_button' ), array( 'bug_id' => $p_bug_id, 'action' => 'stick' ) );
		}
	}
	
	/**
	 * Print a button to unstick the given bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_unstick( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'set_bug_sticky_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( 'bug_stick.php', \Flickerbox\Lang::get( 'unstick_bug_button' ), array( 'bug_id' => $p_bug_id, 'action' => 'unstick' ) );
		}
	}
	
	/**
	 * Print a button to delete the given bug
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_bug_delete( $p_bug_id ) {
		if( \Flickerbox\Access::has_bug_level( config_get( 'delete_bug_threshold' ), $p_bug_id ) ) {
			\Flickerbox\HTML::button( 'bug_actiongroup_page.php', \Flickerbox\Lang::get( 'delete_bug_button' ), array( 'bug_arr[]' => $p_bug_id, 'action' => 'DELETE' ) );
		}
	}
	
	/**
	 * Print a button to create a wiki page
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function button_wiki( $p_bug_id ) {
		if( config_get_global( 'wiki_enable' ) == ON ) {
			if( \Flickerbox\Access::has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
				\Flickerbox\HTML::button( 'wiki.php', \Flickerbox\Lang::get_defaulted( 'Wiki' ), array( 'id' => $p_bug_id, 'type' => 'issue' ), 'get' );
			}
		}
	}
	
	/**
	 * Print all buttons for view bug pages
	 * @param integer $p_bug_id A valid bug identifier.
	 * @return void
	 */
	static function buttons_view_bug_page( $p_bug_id ) {
		$t_readonly = bug_is_readonly( $p_bug_id );
		$t_sticky = config_get( 'set_bug_sticky_threshold' );
	
		$t_bug = bug_get( $p_bug_id );
	
		echo '<table><tr class="details-buttons">';
		if( !$t_readonly ) {
			# UPDATE button
			echo '<td>';
			\Flickerbox\HTML::button_bug_update( $p_bug_id );
			echo '</td>';
	
			# ASSIGN button
			echo '<td>';
			\Flickerbox\HTML::button_bug_assign_to( $t_bug );
			echo '</td>';
		}
	
		# Change status button/dropdown
		if( !$t_readonly ) {
			echo '<td>';
			\Flickerbox\HTML::button_bug_change_status( $t_bug );
			echo '</td>';
		}
	
		# MONITOR/UNMONITOR button
		if( !\Flickerbox\Current_User::is_anonymous() ) {
			echo '<td>';
			if( user_is_monitoring_bug( \Flickerbox\Auth::get_current_user_id(), $p_bug_id ) ) {
				\Flickerbox\HTML::button_bug_unmonitor( $p_bug_id );
			} else {
				\Flickerbox\HTML::button_bug_monitor( $p_bug_id );
			}
			echo '</td>';
		}
	
		# STICK/UNSTICK button
		if( \Flickerbox\Access::has_bug_level( $t_sticky, $p_bug_id ) ) {
			echo '<td>';
			if( !bug_get_field( $p_bug_id, 'sticky' ) ) {
				\Flickerbox\HTML::button_bug_stick( $p_bug_id );
			} else {
				\Flickerbox\HTML::button_bug_unstick( $p_bug_id );
			}
			echo '</td>';
		}
	
		# CLONE button
		if( !$t_readonly ) {
			echo '<td>';
			\Flickerbox\HTML::button_bug_create_child( $p_bug_id );
			echo '</td>';
		}
	
		# REOPEN button
		echo '<td>';
		\Flickerbox\HTML::button_bug_reopen( $t_bug );
		echo '</td>';
	
		# CLOSE button
		echo '<td>';
		\Flickerbox\HTML::button_bug_close( $t_bug );
		echo '</td>';
	
		# MOVE button
		echo '<td>';
		\Flickerbox\HTML::button_bug_move( $p_bug_id );
		echo '</td>';
	
		# DELETE button
		echo '<td>';
		\Flickerbox\HTML::button_bug_delete( $p_bug_id );
		echo '</td>';
	
		helper_call_custom_function( 'print_bug_view_page_custom_buttons', array( $p_bug_id ) );
	
		echo '</tr></table>';
	}
	
	/**
	 * get the css class name for the given status, user and project
	 * @param integer $p_status  An enumeration value.
	 * @param integer $p_user    A valid user identifier.
	 * @param integer $p_project A valid project identifier.
	 * @return string
	 *
	 * @todo This does not work properly when displaying issues from a project other
	 * than then current one, if the other project has custom status or colors.
	 * This is due to the dynamic css for color coding (css/status_config.php).
	 * Build CSS including project or even user-specific colors ?
	 */
	static function get_status_css_class( $p_status, $p_user = null, $p_project = null ) {
		return \Flickerbox\String::attribute( \MantisEnum::getLabel( config_get( 'status_enum_string', null, $p_user, $p_project ), $p_status ) . '-color' );
	}



}