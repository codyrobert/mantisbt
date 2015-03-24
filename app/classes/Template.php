<?php
namespace Core;


use Core\Config;
use Core\Template\Engine;
use Core\Template\Extension\Body_Class;


class Template
{
	protected static $engine = null;
	
	protected static $body_classes = [];
	
	protected function __construct() {}
	
	static function engine()
	{
		if (Template::$engine === null)
		{
			Template::$engine = new Engine();
			Template::$engine->loadExtension(new Body_Class());
		}
		
		return Template::$engine;
	}
}