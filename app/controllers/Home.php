<?php
namespace Controller;


use Core\Controller\Authenticated_Page;
use Core\URL;


class Home extends Authenticated_Page
{
	function action_index($category = null, $project = null)
	{
		$categories = [
			'open'		=> ['label' => 'Open Tickets',		'href' => URL::get('/view:open/'),		'icon' => 'bookmark'],
			'assigned'	=> ['label' => 'Assigned to You',	'href' => URL::get('/view:assigned/'),	'icon' => 'bookmark'],
			'reported'	=> ['label' => 'Reported by You',	'href' => URL::get('/view:reported/'),	'icon' => 'bookmark'],
			'closed'	=> ['label' => 'Recently Closed',	'href' => URL::get('/view:closed/'),	'icon' => 'bookmark-outline'],
		];
		
		if ($category && !in_array($category, array_keys($categories)))
		{
			URL::redirect('/');
		}
		
		$this->set([
			'current_category'	=> $category ? $category : current(array_keys($categories)),
			'current_project'	=> $project,
			'categories'		=> array_values($categories),
			'view'				=> 'Pages/Home',
		]);
	}
}