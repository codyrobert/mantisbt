<?php
namespace Core\Template;


use Core\Config;


class Engine extends \League\Plates\Engine
{
	public function __construct()
	{
		parent::__construct(ROOT, 'php');
	}
	
	public function find($name)
	{
		$theme_path = THEMES.Config::get('_/app.theme').'/views/'.$name.'.php';
		$app_path = APP.'views/'.$name.'.php';
		
		if (file_exists($theme_path))
		{
			return substr($theme_path, strlen(ROOT), -4);
		}
		
		if (file_exists($app_path))
		{
			return substr($app_path, strlen(ROOT), -4);
		}
		
		return false;
	}
	
	public function path($name)
	{
		return parent::path($this->find($name));
	}
	
	public function exists($name)
	{
		return parent::exists($this->find($name));
	}
	
	public function make($name)
	{
		return parent::make($this->find($name));
	}
}