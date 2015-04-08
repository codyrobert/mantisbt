<?php
namespace Core\Form\Element;


use Core\Config;


class Select extends \PFBC\Element\Select
{
	function render()
	{
		if (isset($this->_attributes['value']))
		{
			if (!is_array($this->_attributes['value']))
			{
				$this->_attributes['value'] = array($this->_attributes['value']);
			}
		}
		else
		{
			$this->_attributes['value'] = array();
		}
		
		if(!empty($this->_attributes['multiple']) && substr($this->_attributes['name'], -2) != '[]')
		{
			$this->_attributes['name'] .= '[]';
		}

		echo '<select', $this->getAttributes(array('value', 'selected')), '>', PHP_EOL;
		
		$selected = false;
		
		foreach($this->options as $value => $label)
		{
			if (is_array($label))
			{
				echo '<optgroup label="'.$value.'">', PHP_EOL;
				
				foreach ($label as $child_value => $child_label)
				{
					$child_value = $this->getOptionValue($child_value);
					echo '<option value="', $this->filter($child_value), '"';
					
					if(!$selected && in_array($child_value, $this->_attributes['value']))
					{
						echo ' selected="selected"';
						$selected = true;
					}	
					
					echo '>', $child_label, '</option>', PHP_EOL;
				}
				
				echo '</optgroup>', PHP_EOL;
			}
			else
			{
				$value = $this->getOptionValue($value);
				echo '<option value="', $this->filter($value), '"';
				
				if(!$selected && in_array($value, $this->_attributes['value']))
				{
					echo ' selected="selected"';
					$selected = true;
				}	
				
				echo '>', $label, '</option>', PHP_EOL;
			}
		}	
		echo '</select>', PHP_EOL;
	}
}