<?php
namespace Core\Form\Element;


use Core\Config;


class CategorySelect extends \PFBC\Element\Select
{
	function __construct($label, $name, $project_id = null, $selected = null, array $properties = null)
	{
		if ($project_id === null)
		{
			$project_id = \Core\Helper::get_current_project();
		}
		
		if ($selected === null)
		{
			$selected = $_POST[$name];
		}
	
		if (Config::mantis_get('allow_no_category'))
		{
			$options[0] = \Core\Category::full_name(0, false);
			
		} else {
			$options[0] = \Core\String::attribute(\Core\Lang::get('select_option'));
		}
		
		foreach (\Core\Category::get_all_rows((int)$project_id, null, true) as $row)
		{
			$category_id = (int)$row['id'];
			$options[$category_id] = \Core\String::attribute(\Core\Category::full_name($category_id, $row['project_id'] != $project_id));
		}
		
		parent::__construct($label, $name, $options, ['value' => (int)$selected] + (array)$properties);
	}
}