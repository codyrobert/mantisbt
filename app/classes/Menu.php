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
		$menu_items[URL::get('account')] = [
			'label'	=> Lang::get('account_link'),
		];
		
		$menu_items[URL::get('account/preferences')] = [
			'label'	=> Lang::get('change_preferences_link'),
		];
		
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
		/*$menu_items[URL::home()] = [
			'label'	=> Lang::get('my_view_link'),
		];
		
		# View All Page
		$menu_items[URL::get('view_all')] = [
			'label'	=> Lang::get('view_bugs_link'),
		];*/
		
		# Report Page
		if (Access::has_project_level(Config::mantis_get('report_bug_threshold')))
		{
			$menu_items[URL::get('report')] = [
				'label'	=> '<i class="mdi mdi-plus"></i>',
			];
		}
		
		# Summary Page
		if (Access::has_project_level(Config::mantis_get('view_summary_threshold')))
		{
			$menu_items[URL::get('summary_page.php')] = [
				'label'	=> Lang::get('summary_link'),
			];
		}
		
		# Project Documentation Page
		if (Config::mantis_get('enable_project_documentation') == ON)
		{
			$menu_items[URL::get('proj_doc_page.php')] = [
				'label'	=> Lang::get('docs_link'),
			];
		}
		
		# Project Documentation Page
		if (Config::mantis_get('wiki_enable') == ON)
		{
			$menu_items[URL::get('wiki.php?type=project&amp;id=')] = [
				'label'	=> Lang::get('wiki'),
			];
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
			$menu_items[URL::get('manage_overview_page.php')] = [
				'label'	=> Lang::get('manage_link'),
			];
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
				
				$menu_items[$link] = [
					'label'	=> Lang::get('manage_link'),
				];
			}
		}
		
		# News Page
		if (News::is_enabled() && Access::has_project_level(Config::mantis_get('manage_news_threshold')))
		{
			# Admin can edit news for All Projects (site-wide)
			if (Helper::get_current_project() != ALL_PROJECTS || Current_User::is_administrator())
			{
				$menu_items[URL::get('news_menu_page.php')] = [
					'label'	=> Lang::get('edit_news_link'),
				];
			}
			else
			{
				$menu_items[URL::get('login_select_proj_page.php')] = [
					'label'	=> Lang::get('edit_news_link'),
				];
			}
		}
		
		# Account Page (only show accounts that are NOT protected)
		if (Current_User::get_field('protected') == OFF)
		{
			$menu_items[URL::get('account')] = [
				'label'	=> '<i class="mdi mdi-settings"></i>',
			];
		}
		
		# Add custom options
		//$t_custom_options = HTML::prepare_custom_menu_options( 'main_menu_custom_options' );
		//$t_menu_options = array_merge( $t_menu_options, $t_custom_options );
		
		# Time Tracking / Billing
		if (Config::mantis_get('time_tracking_enabled') && Access::has_global_level(Config::mantis_get('time_tracking_reporting_threshold')))
		{
			$menu_items[URL::get('billing_page.php')] = [
				'label'	=> Lang::get('time_tracking_billing_link'),
			];
		}
		
		# Logout (no if anonymously logged in)
		if (!Current_User::is_anonymous())
		{
			$menu_items[URL::get('logout')] = [
				'label'	=> '<i class="mdi mdi-logout"></i>',
			];
		}
		
		
		return $menu_items;
	}
}