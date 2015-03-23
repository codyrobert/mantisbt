<?php
namespace Controller;


class Roadmap extends \Core\Controller\Page
{
	function action_index()
	{
		$this->set([
			'page_title'	=> \Core\Lang::get( 'roadmap' ),
			'view'			=> 'Pages/Roadmap',
		]);
	}
}