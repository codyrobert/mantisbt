<?php
use Core\Auth;
use Core\Form;
use Core\Helper;
use Core\Lang;
use Core\String;

use Core\Form\Element as _Element;
use PFBC\Element as Element;

use Model\User;


$form = new Form('account_update', [
	'class'	=> 'form-style--standard',
]);

$form->addElement(new Element\Textbox(Lang::get('username'), 'username', [
	'disabled'	=> true,
	'value'		=> User::current()->username,
]));

$form->addElement(new Element\Password(Lang::get('password'), 'password', [
	'maxlength'	=> Auth::get_password_max_size(),
	'value'		=> '',
]));

$form->addElement(new Element\Password(Lang::get('confirm_password'), 'password_confirm', [
	'maxlength'	=> Auth::get_password_max_size(),
	'value'		=> '',
]));

$form->addElement(new Element\Email(Lang::get('email'), 'email', [
	'value'		=> User::current()->email,
]));

$form->addElement(new Element\Textbox(Lang::get('realname'), 'realname', [
	'maxlength'	=> DB_FIELD_SIZE_REALNAME,
	'value'		=> User::current()->realname,
]));

$form->addElement(new Element\Textbox(Lang::get('access_level'), 'access_level', [
	'disabled'	=> true,
	'value'		=> Helper::get_enum_element( 'access_levels', User::current()->access_level ),
]));

$form->addElement(new Element\Textbox(Lang::get('access_level_project'), 'access_level_project', [
	'disabled'	=> true,
	'value'		=> Helper::get_enum_element( 'access_levels', User::current()->access_level ),
]));

if ($projects = \Core\User::get_assigned_projects(User::current()->id))
{
	foreach ($projects as $project) 
	{
		$project_rows[] = [
			String::attribute($project['name']),
			Helper::get_enum_element('access_levels', $project['access_level']),
			Helper::get_enum_element('project_view_state', $project['view_state']),
		];
	}
	
	$form->addElement(new _Element\TableData(Lang::get('assigned_projects'), 'assigned_projects', [
		'head'	=> [
			'Project',
			'Access Level',
			'Permissions',
		],
		'body'	=> @$project_rows,
	]));
}

$form->addElement(new Element\Button(Lang::get('update_user_button')));

$form->render();