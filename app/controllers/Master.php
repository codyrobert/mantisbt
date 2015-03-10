<?php
namespace Flickerbox\Controller;


class Master extends \Flickerbox\Controller
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