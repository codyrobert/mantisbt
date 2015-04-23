<?php
namespace Controller;


use Core\Auth;
use Core\Config;
use Core\Controller\Authenticated_Page;
use Core\Lang;
use Core\Print_Util;

use Model\Ticket;
use Model\User;


class Home extends Authenticated_Page
{
	function action_index()
	{
		$this->set([
			'view' => 'Pages/Home',
		]);
	}
}