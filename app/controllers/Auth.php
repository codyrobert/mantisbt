<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Category;
use Core\Config;
use Core\Controller\Unauthenticated_Page;
use Core\Current_User;
use Core\Form;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Menu;
use Core\Print_Util;
use Core\Request;
use Core\Session;
use Core\String;
use Core\Template;
use Core\URL;
use Core\User;
use Core\Utility;


class Auth extends Unauthenticated_Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		App::queue_css(URL::get('media/css/auth.css'));
	}
	
	function action_login()
	{
		$request = new Request('REQUEST', [
			// Default values
			'return'			=> Config::mantis_get('default_home_page'),
			'secure_session'	=> false,
		], [
			// Validations
		    'username'			=> 'required',
		    'password'			=> 'required',
		], [
			// Filters
		    'username'			=> 'trim|sanitize_string',
		    'password'			=> 'trim',
		    'return'			=> 'trim|sanitize_url',
		    'secure_session'	=> 'boolean',
		]);
		
		if ($_POST && $request->is_valid())
		{
			$allow_permanent_login = (bool)(Config::mantis_get('allow_permanent_cookie') == ON && $request->perm_login);
			GPC::set_cookie(Config::get_global('cookie_prefix').'_secure_session', $request->secure_session ? '1' : '0');
			
			if(\Core\Auth::attempt_login($request->username, $request->password, $allow_permanent_login)) 
			{
				Session::set('secure_session', $request->secure_session);
				URL::redirect($request->return);
			}
		}
		
		$this->set([
			'page_title'	=> Lang::get( 'login_title' ),
			'view'			=> 'Pages/Auth/Login',
			'error'			=> $_POST && $request->errors() ? Lang::get( 'login_error' ) : false,
		]);
	}
	
	function action_logout()
	{
		\Core\Auth::logout();
		URL::redirect(Config::get('redirects.logout'));
	}
	
	function action_lost_password()
	{
		$this->set([
			'page_title'	=> Lang::get( 'my_view_link' ),
			'view'			=> 'Pages/Auth/Lost_Password',
			'error'			=> $_POST && $request->errors() ? Lang::get( 'login_error' ) : false,
		]);
	}
}