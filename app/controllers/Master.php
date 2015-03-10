<?php
namespace Core\Controller;


class Master extends \Core\Controller
{
	function __construct()
	{
		echo 'hello construct';
	}
	
	function action_index()
	{
		echo 'hello action';
		d($this->parameters);
	}
}