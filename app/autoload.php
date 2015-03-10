<?php
spl_autoload_register(function($class) 
{
	 if (substr($class, 0, 22) == 'Flickerbox\\Controller\\')
	 {
	 	$class = str_replace('\\', '/', substr($class, 22));
	 	require APP.'controllers/'.$class.'.php';
	 }
	 elseif (substr($class, 0, 16) == 'Flickerbox\\Core\\')
	 {
	 	$class = str_replace('\\', '/', substr($class, 16));
	 	require APP.'classes/'.$class.'.php';
	 }
});