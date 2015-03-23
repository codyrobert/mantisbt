<?php
namespace Core;


use Core\Access;
use Core\Config;
use Core\Current_User;
use Core\Lang;
use Core\String;
use Core\URL;


class Menu
{
	static function account()
	{
		$menu_items[URL::get('account')] = Lang::get('account_link');
		$menu_items[URL::get('account/preferences')] = Lang::get('change_preferences_link');
		$menu_items[URL::get('account_manage_columns_page.php')] = Lang::get('manage_columns_config');
	
		if (Config::mantis_get('enable_profiles') == ON && Access::has_project_level(Config::mantis_get('add_profile_threshold')))
		{
			$menu_items[URL::get('account_prof_menu_page.php')] = Lang::get('manage_profiles_link');
		}
	
		if (Config::mantis_get('enable_sponsorship') == ON && Access::has_project_level(Config::mantis_get('view_sponsorship_total_threshold')) && !Current_User::is_anonymous())
		{
			$menu_items[URL::get('account_sponsor_page.php')] = Lang::get('my_sponsorship');
		}
		
		# Plugin / Event added options
		/*$t_event_menu_options = \Core\Event::signal( 'EVENT_MENU_ACCOUNT' );
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
		}*/
		
		return $menu_items;
	}
	
	
	static function main()
	{
		if( Config::mantis_get( 'news_enabled' ) == ON )
		{
			$menu_items[URL::home()] = Lang::get( 'main_link' );
		}
		
		/*# Plugin / Event added options
		$t_event_menu_options = Event::signal( 'EVENT_MENU_MAIN_FRONT' );
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
		}*/
		
		
		# My View Page
		$menu_items[URL::get('my_view')] = Lang::get('my_view_link');
		
		# View All Page
		$menu_items[URL::get('view_all_bug_page.php')] = Lang::get('view_bugs_link');
		
		# Report Page
		if (Access::has_project_level(Config::mantis_get('report_bug_threshold')))
		{
			$menu_items[URL::get('report')] = Lang::get('report_bug_link');
		}
		
		# Changelog Page
		if (Access::has_project_level(Config::mantis_get('view_changelog_threshold')))
		{
			$menu_items[URL::get('changelog')] = Lang::get('changelog_link');
		}
		
		# Roadmap Page
		if (Access::has_project_level(Config::mantis_get('roadmap_view_threshold')))
		{
			$menu_items[URL::get('roadmap')] = Lang::get('roadmap_link');
		}
		
		# Summary Page
		if (Access::has_project_level(Config::mantis_get('view_summary_threshold')))
		{
			$menu_items[URL::get('summary_page.php')] = Lang::get('summary_link');
		}
		
		# Project Documentation Page
		if (Config::mantis_get('enable_project_documentation') == ON)
		{
			$menu_items[URL::get('proj_doc_page.php')] = Lang::get('docs_link');
		}
		
		# Project Documentation Page
		if (Config::mantis_get('wiki_enable') == ON)
		{
			$menu_items[URL::get('wiki.php?type=project&amp;id=')] = Lang::get('wiki');
		}
		
		
		# Plugin / Event added options
		/*$t_event_menu_options = Event::signal( 'EVENT_MENU_MAIN' );
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
		}*/
		
		
		# Manage Users (admins) or Manage Project (managers) or Manage Custom Fields
		
		if (Access::has_global_level(Config::mantis_get('manage_site_threshold')))
		{
			$menu_items[URL::get('manage_overview_page.php')] = Lang::get('manage_link');
		}
		else
		{
			$t_show_access = min(Config::mantis_get('manage_user_threshold'), Config::mantis_get('manage_project_threshold'), Config::mantis_get('manage_custom_fields_threshold'));
			
			if (Access::has_global_level($t_show_access) || Access::has_any_project($t_show_access))
			{
				$t_current_project = Helper::get_current_project();
				
				if (Access::has_global_level(Config::mantis_get('manage_user_threshold')))
				{
					$link = URL::get( 'manage_user_page.php' );
				}
				elseif (Access::has_project_level(Config::mantis_get('manage_project_threshold'), $t_current_project) && ($t_current_project <> ALL_PROJECTS))
				{
					$link = URL::get('manage_proj_edit_page.php?project_id=') . $t_current_project;
				}
				else
				{
					$link = URL::get('manage_proj_page.php');
				}
				
				$menu_items[$link] = Lang::get('manage_link');
			}
		}
		
		# News Page
		if (News::is_enabled() && Access::has_project_level(Config::mantis_get('manage_news_threshold')))
		{
			# Admin can edit news for All Projects (site-wide)
			if (Helper::get_current_project() != ALL_PROJECTS || Current_User::is_administrator())
			{
				$menu_items[URL::get('news_menu_page.php')] = Lang::get('edit_news_link');
			}
			else
			{
				$menu_items[URL::get('login_select_proj_page.php')] = Lang::get('edit_news_link');
			}
		}
		
		# Account Page (only show accounts that are NOT protected)
		if (Current_User::get_field('protected') == OFF)
		{
			$menu_items[URL::get('account')] = Lang::get('account_link');
		}
		
		# Add custom options
		//$t_custom_options = HTML::prepare_custom_menu_options( 'main_menu_custom_options' );
		//$t_menu_options = array_merge( $t_menu_options, $t_custom_options );
		
		# Time Tracking / Billing
		if (Config::mantis_get('time_tracking_enabled') && Access::has_global_level(Config::mantis_get('time_tracking_reporting_threshold')))
		{
			$menu_items[URL::get('billing_page.php')] = Lang::get('time_tracking_billing_link');
		}
		
		# Logout (no if anonymously logged in)
		if (!Current_User::is_anonymous())
		{
			$menu_items[URL::get('logout')] = Lang::get('logout_link');
		}
		
		
		return $menu_items;
	}
}