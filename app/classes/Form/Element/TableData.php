<?php
namespace Core\Form\Element;


use Core\Config;


class TableData extends \PFBC\Element
{
	function __construct($label, $name, $properties)
	{
		parent::__construct($label, $name, $properties);
	}
	
	function render()
	{
		echo '<table>', PHP_EOL;
		
		if (count(@(array)$this->_attributes['head']))
		{
			echo '<thead>', PHP_EOL;
			echo '<tr>', PHP_EOL;
			
			foreach (@(array)$this->_attributes['head'] as $cell)
			{
				echo '<td>', $cell, '</td>', PHP_EOL;
			}
			
			echo '</tr>', PHP_EOL;
			echo '</thead>', PHP_EOL;
		}
		
		echo '<tbody>', PHP_EOL;
		
		foreach (@(array)$this->_attributes['body'] as $row)
		{
			echo '<tr>', PHP_EOL;
			
			foreach ($row as $cell)
			{
				echo '<td>', $cell, '</td>', PHP_EOL;
			}
			
			echo '</tr>', PHP_EOL;
		}
		
		echo '</tbody>', PHP_EOL;
		echo '</table>', PHP_EOL;
	}
}