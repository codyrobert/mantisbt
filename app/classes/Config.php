<?php
namespace Flickerbox;

class Config
{
	protected static $data = null;
	
	static function get($key)
	{
		if (!array_key_exists($key, (array)Config::$data))
		{
			if ($val = config_get($key))
			{
				Config::$data[$key] = $val;
			}
			else
			{
				$config_file = APP.'config/'.$key.'.php';
				
				if (file_exists($config_file))
				{
					Config::$data[$key] = include($config_file);
				}
			}
		}
		
		return @Config::$data[$key] ? Config::$data[$key] : false;
	}
}