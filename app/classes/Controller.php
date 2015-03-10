<?php
namespace Flickerbox;


abstract class Controller
{
	protected $parameters = null;
	
	function __construct($params)
	{
		$this->parameters = (array)$params;
	}
}