<?php
require 'app/bootstrap.php';


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
		list($controller, $action) = explode('->', $match['target']);
		
		$page = new $controller($match['params']);
		
		$page->$action();
		$page->render();
	}
	elseif (is_callable($match['target']))
	{
		call_user_func_array($match['target'], $match['params']); 
	}
	else
	{
		header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
		dd($router);
	}
}