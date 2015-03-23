<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Auth;
use Core\Category;
use Core\Config;
use Core\Controller\Authenticated_Page;
use Core\Current_User;
use Core\Form;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Menu;
use Core\Print_Util;
use Core\String;
use Core\Template;
use Core\URL;
use Core\User;
use Core\Utility;


class Account extends Authenticated_Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		App::queue_css(URL::get('css/status_config.php'));
	}
	
	function action_index()
	{
		$this->set([
			'page_title'	=> Lang::get( 'my_view_link' ),
			'view'			=> 'Pages/Account/Edit',
		]);
	}
	
	function action_preferences()
	{
		$this->set([
			'page_title'	=> Lang::get( 'change_preferences_link' ),
			'view'			=> 'Pages/Account/Edit_Preferences',
		]);
	}
	
	function action_columns()
	{
		$this->set([
			'page_title'	=> '',//Lang::get( 'change_preferences_link' ),
			'view'			=> 'Pages/Account/Columns',
		]);
	}
	
	function action_profiles()
	{
		$this->set([
			'page_title'	=> \Core\Lang::get( 'manage_profiles_link' ),
			'view'			=> 'Pages/Account/Profiles',
		]);
	}
}