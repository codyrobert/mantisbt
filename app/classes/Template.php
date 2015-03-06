<?php
namespace Flickerbox;

class Template
{
	protected static $engine = null;
	
	protected function __construct() {}
	
	static function engine()
	{
		if (Template::$engine === null)
		{
			Template::$engine = new \League\Plates\Engine(APP.'views');
		}
		
		return Template::$engine;
	}
}