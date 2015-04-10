<?php
require '../app/bootstrap.php';


$router = new \AltoRouter();


foreach ((array)\Core\Config::get('routes') as $row)
{
	$target = null;
	
	if (@$row['controller'] && @$row['action'])
	{
		$target = '\\Controller\\'.$row['controller'].'->action_'.$row['action'];
	}
	elseif (@$row['fn'])
	{
		$target = $row['fn'];
	}
	
	if (@$target)
	{
		$router->map(
			@$row['method'] ? $row['method'] : 'GET',
			@$row['route'],
			$target,
			@$row['name'] ? $row['name'] : $target
		);
	}
}

if ($match = $router->match())
{
	if (strstr($match['target'], '->'))
	{
		$params = $match['params'];
		
		$match['target'] = preg_replace_callback('/\[([^\]]*)\]/iU', function($matches) use($params) 
		{
			if ($matches[1] == 'action' && !$params[$matches[1]])
			{
				$params[$matches[1]] = 'index';
			}
			
			return $matches[1] ? $params[$matches[1]] : $matches[0];
		}, 
		$match['target']);
		
		list($controller, $action) = explode('->', $match['target']);
		
		$page = new $controller($params);
		
		if (method_exists($page, $action))
		{
			call_user_func_array(array(&$page, $action), $params);
			
			$page->render();
			exit;
		}
	}
	elseif (is_callable($match['target']))
	{
		call_user_func_array($match['target'], $match['params']);
		exit;
	}
}

echo '404';
!dd($router);