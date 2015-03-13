<?php
namespace Core\Controller;


use Core\Auth;
use Core\Current_User;
use Core\Config;
use Core\Controller\Page;
use Core\Request;
use Core\URL;


abstract class Unauthenticated_Page extends Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		$request = new Request('REQUEST', [
			// Default values
			'return' => Config::mantis_get('default_home_page'),
		], [], [
			// Filters
		    'return' => 'trim|sanitize_url',
		]);

		
		if (Auth::is_user_authenticated())
		{
			URL::redirect($request->return);
		}
	}
}