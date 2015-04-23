<?php
use Core\Form;
use Core\Lang;
use Core\Request;

use PFBC\Element;

$request = new Request('POST');
$form = new Form('search');

$form->addElement(new Element\Textbox(null, 'query', [
	'placeholder'	=> 'Search issues', 
	'type' 			=> 'search', 
	'value' 		=> $this->e(@$request->query),
]));

$form->render();