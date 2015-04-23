<?php
namespace Core\Controller;


use Core\App;
use Core\Auth;
use Core\Current_User;
use Core\Controller\Page;
use Core\URL;


abstract class Authenticated_Page extends Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		Auth::ensure_user_authenticated();
		Current_User::ensure_unprotected();
		
		App::queue_js(URL::get('media/js/app.js'), true);
	}
}