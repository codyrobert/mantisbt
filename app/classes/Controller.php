<?php
namespace Core;


abstract class Controller
{
	protected $parameters = null;
	
	function __construct($params)
	{
		$this->parameters = (array)$params;
	}
	
	function name()
	{
		$class = get_called_class();
		$controller = str_replace('Controller\\', '', $class);
		
		return str_replace(['\\'], ['_'], strtolower($controller));
	}
	
	function render() {}
}