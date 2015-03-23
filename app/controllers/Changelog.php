<?php
namespace Controller;


class Changelog extends \Core\Controller\Page
{
	function action_index()
	{
		$this->set([
			'page_title'	=> \Core\Lang::get( 'changelog' ),
			'view'			=> 'Pages/Changelog',
		]);
	}
}