<?php
namespace Core;


use Core\Config;
use Core\Template\Engine;
use Core\Template\Extension\Body_Class;
use Core\Template\Extension\Gravatar;


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
			Template::$engine->loadExtension(new Gravatar());
		}
		
		return Template::$engine;
	}
}