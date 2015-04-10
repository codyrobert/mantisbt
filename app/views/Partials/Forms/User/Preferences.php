<?php
use Core\Config;
use Core\Form;
use Core\Lang;
use Core\String;

use Core\Form\Element as _Element;
use PFBC\Element as Element;

use Model\User;


$form = new Form('account_prefs_update');

$form->addElement(new Element\Radio(Lang::get('bugnote_order'), 'bugnote_order', ['DESC', 'ASC'], [
	'value'	=> User::current()->preferences()->bugnote_order,
]));

$priorities = [
	'email_on_new'		=> Lang::get('email_on_new'), 
	'email_on_assigned'	=> Lang::get('email_on_assigned'), 
	'email_on_feedback'	=> Lang::get('email_on_feedback'), 
	'email_on_resolved'	=> Lang::get('email_on_resolved'), 
	'email_on_closed'	=> Lang::get('email_on_closed'), 
	'email_on_reopened'	=> Lang::get('email_on_reopened'), 
	'email_on_bugnote'	=> Lang::get('email_on_bugnote_added'), 
	'email_on_status'	=> Lang::get('email_on_status_change'), 
	'email_on_priority'	=> Lang::get('email_on_priority_change'),
];

foreach ($priorities as $key => $label)
{
	$checkbox_ele = new Element\Checkbox(null, $key, [ON => null], ['value' => User::current()->preferences()->{$key}]);
	
	ob_start();
	$checkbox_ele->render();
	$checkbox = ob_get_clean();
	
	$select_ele = new Element\Select(null, $key.'_min_severity', [OFF => Lang::get('any'), '-----'] + Config::get('levels.severity'), ['value' => User::current()->preferences()->{$key.'_min_severity'}]);
	
	ob_start();
	$select_ele->render();
	$select = ob_get_clean();
	
	$form->addElement(new _Element\HTML($label, $key,
		$checkbox.
		'<span>'.Lang::get( 'with_minimum_severity' ).'</span>'.
		$select
	));
}

$form->addElement(new Element\Textbox(Lang::get('email_bugnote_limit'), 'email_bugnote_limit', [
	'maxlength'	=> 2,
	'value'		=> User::current()->preferences()->email_bugnote_limit,
]));

$form->addElement(new _Element\Select(Lang::get('timezone'), 'timezone', Config::get('_/timezones.grouped'), [
	'value' => User::current()->preferences()->timezone ? User::current()->preferences()->timezone : Config::get('default.timezone'),
]));

$form->addElement(new Element\Select(Lang::get('language'), 'language', Config::get('_/languages'), [
	'value' => User::current()->preferences()->language,
]));

$form->addElement(new Element\Button(Lang::get('update_prefs_button')));

$form->render();