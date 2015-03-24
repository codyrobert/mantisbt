<?php
namespace Controller;


class Media extends \Core\Controller
{
	function action_find($path = null)
	{
		if (substr($path, -4) == '.css')
		{
			header('Content-type: text/css');
			$type = 'css';
		}
		
		if ($theme = \Core\Config::get('_/app.theme'))
		{
			$theme_file = THEMES.$theme.'/media/'.$path;
			
			if ($type === 'css' || file_exists($theme_file))
			{
				exit(file_get_contents($theme_file));
			}
		}
		
		exit(file_get_contents(APP.'media/'.$path));
	}
}