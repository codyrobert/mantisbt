<?php
require dirname(__DIR__).'/app/bootstrap.php';


$router = new \AltoRouter();
$router->setBasePath('/public');


foreach ((array)\Flickerbox\Config::get('routes') as $row)
{
	$target = null;
	
	if (@$row['controller'] && @$row['action'])
	{
		$target = '\\Flickerbox\\Controller\\'.$row['controller'].'->action_'.$row['action'];
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
		(new $controller($match['params']))->$action();
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