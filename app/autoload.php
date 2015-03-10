<?php
spl_autoload_register(function($class) 
{
	 if (substr($class, 0, 11) == 'Controller\\')
	 {
	 	$class = str_replace('\\', '/', substr($class, 11));
	 	require APP.'controllers/'.$class.'.php';
	 }
	 elseif (substr($class, 0, 5) == 'Core\\')
	 {
	 	if (strstr($class, 'View_Issues')) echo $class;
	 	
	 	$class = str_replace('\\', '/', substr($class, 5));
	 	require APP.'classes/'.$class.'.php';
	 }
});