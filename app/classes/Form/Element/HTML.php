<?php
namespace Core\Form\Element;


use Core\Config;


class HTML extends \PFBC\Element
{
	function __construct($label, $name, $value)
	{
		parent::__construct($label, $name, ['value' => $value]);
	}
	
	function render()
	{
		echo $this->_attributes['value'];
	}
}