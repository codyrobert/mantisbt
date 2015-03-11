<?php
namespace Controller;


use Core\Auth;
use Core\Config;
use Core\Controller;
use Core\Print_Util;


class Master extends Controller
{
	function action_index()
	{
		if(Auth::is_user_authenticated()) 
		{
			Print_Util::header_redirect(Config::mantis_get('default_home_page'));
		}
		else
		{
			Print_Util::header_redirect('login_page.php');
		}

	}
}