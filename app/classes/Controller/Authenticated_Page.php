<?php
namespace Core\Controller;


use Core\Auth;
use Core\Current_User;
use Core\Controller\Page;


abstract class Authenticated_Page extends Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		Auth::ensure_user_authenticated();
		Current_User::ensure_unprotected();
	}
}