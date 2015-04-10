<?php
namespace Core;


use Core\DB;


abstract class Model
{
	protected static $schema = [
		'table_name'	=> null,
		'id_key'		=> 'id',
	];
	
	protected $loaded = false;
	
	protected $data = [];
	protected $changed = [];
	
	static function find($where_string, $parameters, int $limit = null)
	{
		if ($limit === null)
		{
			$limit = 1;
		}
		
		if ($limit > 0)
		{
			$limit_string = ' LIMIT '.$limit;
		}
		
		$class = get_called_class();
		
		if ($result = DB::query('SELECT * FROM '.$class::$schema['table_name'].' WHERE '.$where_string.$limit_string, $parameters))
		{
			foreach ($result as $row)
			{
				$model = new $class();
				$model->inject($row);
				
				$models[] = $model;
			}
			
			return count($models) > 1 ? $models : current($models);
		}
	}
	
	function __construct(int $id = null)
	{
		if ($id !== null)
		{
			$class = get_called_class();
			
			if ($result = DB::query('SELECT * FROM '.$class::$schema['table_name'].' WHERE '.$class::$schema['id_key'].' = ? LIMIT 1', [$id]))
			{
				$this->inject($result);
			}
		}
	}
	
	function inject($data)
	{
		$this->data = $data;
		$this->loaded = true;
	}
	
	function loaded()
	{
		return (bool)$this->loaded;
	}
	
	function save()
	{
		if (count($this->changed))
		{
			$class = get_called_class();
			
			$statement = 'UPDATE '.$class::$schema['table_name'].' SET '.implode(' = ?, ', array_keys($this->changed)).' = ? WHERE '.$class::$schema['id_key'].' = ?';
			$values = array_merge(array_values($this->changed), [$this->data[$class::$schema['id_key']]]);
			
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