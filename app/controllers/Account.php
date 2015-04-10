<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Auth;
use Core\Category;
use Core\Config;
use Core\Controller\Authenticated_Page;
use Core\Form;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Menu;
use Core\Print_Util;
use Core\Request;
use Core\String;
use Core\Template;
use Core\URL;
use Core\Utility;

use Model\User;


class Account extends Authenticated_Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		App::queue_css(URL::get('css/status_config.php'));
	}
	
	function action_index()
	{
		$request = new Request('POST', [], [
			// Validations
			'_token'				=> 'valid_token,account_update|required',
			'email'					=> 'valid_email|unique_user_email,'.User::current()->id.'|required',
			'realname'				=> 'unique_user_realname,'.User::current()->id.'|required',
			'password'				=> 'matches,password_confirm|min_len,6',
		], [
			// Filters
			'realname'				=> 'trim|sanitize_string',
			'password'				=> 'trim',
		]);
		
		if ($_POST && $request->valid())
		{
			if ($request->email != User::current()->email) 
			{
				User::current()->email = $request->email;
				$messages[] = Lang::get('email_updated');
			}
			
			if ($request->realname != User::current()->realname)
			{
				User::current()->realname = $request->realname;
				$messages[] = Lang::get('realname_updated');
			}
			
			if ($request->password)
			{
				\Core\User::set_password(User::current()->id, $request->password);
				$messages[] = Lang::get('password_updated');
			}
			
			User::current()->save();
			Form::security_purge('account_update');
			
			if (!count(@$messages))
			{
				$messages[] = Lang::get('operation_successful');
			}
		}
		
		$this->set([
			'page_title'	=> Lang::get( 'edit_account_title' ),
			'view'			=> 'Pages/Account/Index',
			'messages'		=> @$messages,
			'errors'		=> $_POST ? $request->errors() : [],
		]);
	}
	
	function action_preferences()
	{
		$severity_levels = implode(' ', [OFF] + array_keys(Config::get('levels.severity')));
		
		$request = new Request('POST', [], [
			// Validations
			'_token'								=> 'valid_token,account_prefs_update|required',
			'bugnote_order'							=> 'contains,ASC DESC',
			'email_bugnote_limit'					=> 'integer',
			'timezone'								=> 'contains,'.implode(' ', array_keys(Config::get('_/timezones.ungrouped'))),
			'language'								=> 'contains,'.implode(' ', Config::get('_/languages')),
			'email_on_new_min_severity'				=> 'contains,'.$severity_levels,
			'email_on_assigned_min_severity'		=> 'contains,'.$severity_levels, 
			'email_on_feedback_min_severity'		=> 'contains,'.$severity_levels, 
			'email_on_resolved_min_severity'		=> 'contains,'.$severity_levels, 
			'email_on_closed_min_severity'			=> 'contains,'.$severity_levels, 
			'email_on_reopened_min_severity'		=> 'contains,'.$severity_levels, 
			'email_on_bugnote_min_severity'			=> 'contains,'.$severity_levels, 
			'email_on_status_min_severity'			=> 'contains,'.$severity_levels, 
			'email_on_priority_min_severity'		=> 'contains,'.$severity_levels,
		], [
			'email_on_new'							=> 'boolean',
			'email_on_assigned'						=> 'boolean', 
			'email_on_feedback'						=> 'boolean', 
			'email_on_resolved'						=> 'boolean', 
			'email_on_closed'						=> 'boolean', 
			'email_on_reopened'						=> 'boolean', 
			'email_on_bugnote'						=> 'boolean', 
			'email_on_status'						=> 'boolean', 
			'email_on_priority'						=> 'boolean',
		]);
		
		if ($_POST && $request->valid())
		{
			$fields = [
				'bugnote_order', 
				'email_bugnote_limit', 
				'timezone', 
				'language', 
				'email_on_new', 
				'email_on_assigned', 
				'email_on_feedback', 
				'email_on_resolved', 
				'email_on_closed', 
				'email_on_reopened', 
				'email_on_bugnote', 
				'email_on_status', 
				'email_on_priority', 
				'email_on_new_min_severity', 
				'email_on_assigned_min_severity', 
				'email_on_feedback_min_severity', 
				'email_on_resolved_min_severity', 
				'email_on_closed_min_severity', 
				'email_on_reopened_min_severity', 
				'email_on_bugnote_min_severity', 
				'email_on_status_min_severity', 
				'email_on_priority_min_severity'
			];
			
			foreach ($fields as $key)
			{
				User::current()->preferences()->{$key} = $request->{$key};
			}
			
			User::current()->preferences()->save();
			Form::security_purge('account_prefs_update');
			
			$messages[] = Lang::get('operation_successful');
		}
		
		$this->set([
			'page_title'	=> Lang::get( 'change_preferences_link' ),
			'view'			=> 'Pages/Account/Preferences',
			'messages'		=> @$messages,
			'errors'		=> $_POST ? $request->errors() : [],
		]);
	}
	
	function action_columns()
	{
		if ($_POST && Form::security_validate( 'manage_config_columns_reset' ))
		{
			$t_user_id = Auth::get_current_user_id();
			
			Config::delete_for_user( 'view_issues_page_columns', $t_user_id );
			Config::delete_for_user( 'print_issues_page_columns', $t_user_id );
			Config::delete_for_user( 'csv_columns', $t_user_id );
			Config::delete_for_user( 'excel_columns', $t_user_id );
			
			Form::security_purge( 'manage_config_columns_reset' );
			
			$this->message = Lang::get( 'operation_successful' );
		}

		$this->set([
			'page_title'	=> Lang::get( 'manage_columns_config' ),
			'view'			=> 'Pages/Account/Columns',
			'project_id'	=> Helper::get_current_project(),
			'user_id'		=> Auth::get_current_user_id(),
		]);
	}
	
	function action_profiles()
	{
		$this->set([
			'page_title'	=> Lang::get( 'manage_profiles_link' ),
			'view'			=> 'Pages/Account/Profiles',
		]);
	}
}