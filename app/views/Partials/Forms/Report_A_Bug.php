<?php
use Core\Config;
use Core\Form;
use PFBC\Element;

$form = new Form('bug_report');

$form->addElement(new Element\Hidden('m_id', $bug_id));
$form->addElement(new Element\Hidden('project_id', $project_id));

if (in_array('category_id', $show))
{
	$form->addElement(new Form\Element\CategorySelect(\Core\Lang::get('category'), 'category_id', $project_id));
}

if (in_array('reproducibility', $show))
{
	$form->addElement(new Element\Select(\Core\Lang::get('reproducibility'), 'reproducibility', Config::get('levels.reproducibility'), ['value' => $reproducibility]));
}

if (in_array('eta', $show))
{
	$form->addElement(new Element\Select(\Core\Lang::get('eta'), 'eta', Config::get('levels.eta'), ['value' => $eta]));
}

if (in_array('severity', $show))
{
	$form->addElement(new Element\Select(\Core\Lang::get('severity'), 'severity', Config::get('levels.severity'), ['value' => $severity]));
}
		
		
/*$form->addElement(new Element\Email('Email Address:', 'Email', array(
    'required' => 1
)));
$form->addElement(new Element\Password('Password:', 'Password', array(
    'required' => 1
)));
$form->addElement(new Element\Checkbox('', 'Remember', array(
    '1' => 'Remember me'
)));
$form->addElement(new Element\Button('Login'));
$form->addElement(new Element\Button('Cancel', 'button', array(
    'onclick' => 'history.go(-1);'
)));*/

$form->addElement(new Element\Button(\Core\Lang::get('submit_report_button')));

$form->render();