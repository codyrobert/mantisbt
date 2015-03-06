<?php
spl_autoload_register(function($class) 
{
	 if (substr($class, 0, 11) == 'Flickerbox\\')
	 {
	 	$class = str_replace('\\', '/', substr($class, 11));
	 	require APP.'classes/'.$class.'.php';
	 }
});