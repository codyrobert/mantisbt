<?php
namespace Core;


use Core\Template\Extension\Body_Class;
use League\Plates\Engine;


class Template
{
	protected static $engine = null;
	
	protected static $body_classes = [];
	
	protected function __construct() {}
	
	static function engine()
	{
		if (Template::$engine === null)
		{
			Template::$engine = new Engine(APP.'views');
			
			Template::$engine->loadExtension(new Body_Class());
		}
		
		return Template::$engine;
	}
}