<?php return array(

	array(
		'route'			=> '/?',
		'controller'	=> 'Master',
		'action'		=> 'index',
		'method'		=> 'GET',
	),

	array(
		'route'			=> '/account/?',
		'controller'	=> 'Account',
		'action'		=> 'edit',
		'method'		=> 'GET|POST',
	),

	array(
		'route'			=> '/account/preferences/?',
		'controller'	=> 'Account',
		'action'		=> 'edit_preferences',
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