<?php
namespace Core;


class Request
{
	protected static $_added_filters_and_rules = false;
	
	protected $data = null;
	protected $validated_data = null;
	
	protected $validator;
	
	static function add_filters_and_rules()
	{
		if (self::$_added_filters_and_rules === false)
		{
			\GUMP::add_filter('boolean', function($value, $params = NULL) 
			{
			    return (bool)$value;
			});
			
			\GUMP::add_filter('sanitize_url', function($value, $params = NULL) 
			{
			    return filter_var($value, FILTER_SANITIZE_URL);
			});
		
			self::$_added_filters_and_rules = true;
		}
	}
	
	function __construct($type, array $defaults = null, array $validation_rules = null, array $filter_rules = null)
	{
		self::add_filters_and_rules();
		
		switch ($type)
		{
			case 'POST':
				$this->data = $_POST;
				break;
				
			case 'GET':
				$this->data = $_GET;
				break;
			
			case 'COOKIE':
				$this->data = $_COOKIE;
				break;
				
			default:
				$this->data = $_REQUEST;
				break;
		}
		
		$this->data = array_merge((array)$defaults, (array)$this->data);
		
		$this->validator = new \GUMP();
		
		$this->data = $this->validator->sanitize($this->data);
		
		$this->validator->validation_rules($validation_rules);
		$this->validator->filter_rules($filter_rules);
	}
	
	function __get($key)
	{
		return @$this->validated_data ? $this->validated_data[$key] : @$this->data[$key];
	}
	
	function data()
	{
		return @$this->validated_data ? $this->validated_data : @$this->data;
	}
	
	function errors()
	{
		if (!$this->is_valid())
		{
			return $this->validator->get_readable_errors(true);
		}
	}
	
	function is_valid()
	{
		$this->run();
		return (bool)($this->validated_data);
	}
	
	function run()
	{
		$this->validated_data = $this->validator->run($this->data);
	}
}