<?php
namespace Core;


use Core\DB;


abstract class Model
{
	protected $schema = [
		'table_name'	=> null,
		'id_key'		=> 'id',
	];
	
	protected $loaded = false;
	
	protected $data = [];
	protected $changed = [];
	
	function __construct(int $id = null)
	{
		if ($id !== null)
		{
			$this->load($this->schema['id_key'], $id);
		}
	}
	
	function load($key, $value)
	{
		if ($this->loaded === false)
		{
			if ($limit > 0)
			{
				$limit_string = ' LIMIT '.$limit;
			}
			
			if ($result = DB::query('SELECT * FROM '.$this->schema['table_name'].' WHERE '.$key.' = ? LIMIT 1', [$value]))
			{
				$this->data = $result;
				$this->loaded = true;
			}
		}
	}
	
	static function find($key, $value)
	{
		$class = get_called_class();
		
		$model = new $class;
		$model->load($key, $value);
		
		return $model;
	}
	
	function loaded()
	{
		return (bool)$this->loaded;
	}
	
	function save()
	{
		if (count($this->changed))
		{
			$statement = 'UPDATE '.$this->schema['table_name'].' SET '.implode(' = ?, ', array_keys($this->changed)).' = ? WHERE '.$this->schema['id_key'].' = ?';
			$values = array_merge(array_values($this->changed), [$this->data[$this->schema['id_key']]]);
			
			DB::query($statement, $values);
			$this->changed = [];
		}
	}
	
	function __get($key)
	{
		return $this->data[$key];
	}
	
	function __set($key, $val)
	{
		$this->data[$key] = $val;
		$this->changed[$key] = $val;
	}
}