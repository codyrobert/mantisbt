<?php return array(

	array(
		'route'			=> '/?',
		'controller'	=> 'Home',
		'action'		=> 'index',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/account/?[a:action]?/?',
		'controller'	=> 'Account',
		'action'		=> '[action]',
		'method'		=> 'GET|POST',
	),

	array(
		'route'			=> '/login/?',
		'controller'	=> 'Auth',
		'action'		=> 'login',
		'method'		=> 'GET|POST',
	),

	array(
		'route'			=> '/logout/?',
		'controller'	=> 'Auth',
		'action'		=> 'logout',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/lost_password/?',
		'controller'	=> 'Auth',
		'action'		=> 'lost_password',
		'method'		=> 'GET|POST',
	),
	
	array(
		'route'			=> '/media/[**:path]',
		'controller'	=> 'Media',
		'action'		=> 'find',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/report/?',
		'controller'	=> 'Report',
		'action'		=> 'get',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/report/?',
		'controller'	=> 'Report',
		'action'		=> 'post',
		'method'		=> 'POST',
	),

	array(
		'route'			=> '/set_project/?',
		'controller'	=> 'Set_Project',
		'action'		=> 'index',
		'method'		=> 'GET|POST',
	),

	array(
		'route'			=> '/ticket/[i:id]/?',
		'controller'	=> 'Ticket',
		'action'		=> 'view',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/view_all/?',
		'controller'	=> 'View_All',
		'action'		=> 'index',
		'method'		=> 'GET|POST',
	),
	
);