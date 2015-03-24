<?php
namespace Controller;


class Media extends \Core\Controller
{
	function action_find($path = null)
	{
		if (substr($path, -4) == '.css')
		{
			header('Content-type: text/css');
		}
		
		if ($theme = \Core\Config::get('_/app.theme'))
		{
			exit(file_get_contents(THEMES.$theme.'/media/'.$path));
		}
		
		exit(file_get_contents(APP.'media/'.$path));
	}
}