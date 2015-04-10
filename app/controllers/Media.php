<?php
namespace Controller;


class Media extends \Core\Controller
{
	function action_find($path = null)
	{
		$type = array_pop(explode('.', $path));
		$mime_types = [
			'css'		=> 'text/css',
			'js'		=> 'application/javascript',
			'jpg'		=> 'image/jpeg',
			'jpeg'		=> 'image/jpeg',
			'png'		=> 'image/png',
			'gif'		=> 'image/gif',
			'svg'		=> 'image/svg+xml',
		];
		
		if ($theme = \Core\Config::get('_/app.theme'))
		{
			$theme_file = THEMES.$theme.'/media/'.$path;
			
			if ($type === 'css' || file_exists($theme_file))
			{
				$served_file = $theme_file;
			}
		}
		
		if (!@$served_file)
		{
			$app_file = APP.'media/'.$path;
			
			if (file_exists($app_file))
			{
				$served_file = $app_file;
			}
		}
		
		if (@$served_file)
		{
			if (@$mime_types[$type])
			{
				header('Content-type: '.$mime_types[$type]);
			}
			
			echo file_get_contents($served_file);
		}
		
		exit;
	}
}