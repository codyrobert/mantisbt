<?php
namespace Core;


class Request extends \GUMP
{
	protected $has_run = false;
	
	protected $data = null;
	protected $validated_data = null;
	
	function __construct($type, array $defaults = null, array $validation_rules = null, array $filter_rules = null)
	{
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
		$this->data = $this->sanitize($this->data);
		
		$this->validation_rules($validation_rules);
		$this->filter_rules($filter_rules);
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
		if ($this->has_run === false)
		{
			$this->run();
		}
		
		return count($this->errors) ? $this->get_readable_errors(false) : false;
	}
	
	function valid()
	{
		if ($this->has_run === false)
		{
			$this->run();
		}
		
		return !(bool)count($this->errors);
	}
	
	function run()
	{
		if ($this->has_run === false)
		{
			$this->validated_data = parent::run($this->data);
			$this->has_run = true;
			
			return $this->data();	
		}
	}
	
	function filter_boolean($value, $params = null) 
	{
	    return (bool)$value;
	}
			
	function filter_sanitize_url($value, $params = null) 
	{
	    return filter_var($value, FILTER_SANITIZE_URL);
	}
	
	function validate_matches($field, $input, $param = null) 
	{
		if ($input[$field] != $input[$param])
		{
			return [
				'field'	=> $field,
				'value'	=> $input[$field],
				'rule'	=> __FUNCTION__,
				'param'	=> $param,
			];
		}
	}
	
	function validate_valid_email($field, $input, $param = null)
	{
		if (!filter_var($input[$field], FILTER_VALIDATE_EMAIL) || \Core\Email::is_disposable($input[$field]))
		{
			return [
				'field'	=> $field,
				'value'	=> $input[$field],
				'rule'	=> __FUNCTION__,
				'param'	=> $param,
			];
		}
	}
	
	function validate_valid_token($field, $input, $param = null) 
	{
		if (!Form::is_token_valid($param, $input[$field]))
		{
			return [
				'field'	=> $field,
				'value'	=> $input[$field],
				'rule'	=> __FUNCTION__,
				'param'	=> $param,
			];
		}
	}
	
	function validate_unique_user_realname($field, $input, $param = null) 
	{
		$user = \Model\User::find('realname = ?', $input[$field]);
		
		if ($user->loaded() && $user->id !== (int)$param)
		{
			return [
				'field'	=> $field,
				'value'	=> $input[$field],
				'rule'	=> __FUNCTION__,
				'param'	=> $param,
			];
		}
	}
	
	function validate_unique_user_email($field, $input, $param = null) 
	{
		$user = \Model\User::find('email = ?', $input[$field]);
		
		if ($user->loaded() && $user->id !== (int)$param)
		{
			return [
				'field'	=> $field,
				'value'	=> $input[$field],
				'rule'	=> __FUNCTION__,
				'param'	=> $param,
			];
		}
	}

}