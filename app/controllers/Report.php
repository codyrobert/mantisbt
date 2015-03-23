<?php
namespace Controller;


class Report extends \Core\Controller\Page
{
	function action_index()
	{
		$this->set([
			'page_title'	=> \Core\Lang::get('report_bug_link'),
			'view'			=> 'Pages/Report_A_Bug',
		]);
	}
}