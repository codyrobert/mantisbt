<?php return array(

	array(
		'route'			=> '/?',
		'controller'	=> 'Master',
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
		'route'			=> '/changelog/?',
		'controller'	=> 'Changelog',
		'action'		=> 'index',
		'method'		=> 'GET',
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
		'action'		=> 'index',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/roadmap/?',
		'controller'	=> 'Roadmap',
		'action'		=> 'index',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/my_view/?',
		'controller'	=> 'View_Issues',
		'action'		=> 'my_view',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/ticket/[i:id]/?',
		'controller'	=> 'Ticket',
		'action'		=> 'view',
		'method'		=> 'GET',
	),
	
);