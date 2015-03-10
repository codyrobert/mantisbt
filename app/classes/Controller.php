<?php
namespace Core;


abstract class Controller
{
	protected $parameters = null;
	
	function __construct($params)
	{
		$this->parameters = (array)$params;
	}
}