<?php
namespace Controller;


class View_all extends \Core\Controller\Authenticated_Page
{
	function action_index()
	{
		$this->set([
			'view' => 'Pages/View_All',
		]);
		
		include ROOT.'view_all_bug_page.php';
	}
}