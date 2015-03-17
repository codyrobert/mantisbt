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
	
	function __construct($id = null)
	{
		if ($result = DB::query('SELECT * FROM '.$this->schema['table_name'].' WHERE '.$this->schema['id_key'].' = ? LIMIT 1', [(int)$id]))
		{
			$this->data = $result;
			$this->loaded = true;
		}
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