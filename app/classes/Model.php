<?php
namespace Core;


use Core\DB;
use Core\Config;


abstract class Model
{
	protected static $schema = [
		'config_name'	=> null,
		'table_name'	=> null,
		'id_key'		=> 'id',
	];
	
	protected $loaded = false;
	
	protected $data = [];
	protected $changed = [];
	
	protected $classes = null;
	protected $tags = null;
	
	static function schema()
	{
		$class = get_called_class();
		return $class::$schema;
	}
	
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
			
			if (count($models) > 1 && $class::$schema['config_name'] !== null)
			{
				$sorts = (array)Config::get($class::$schema['config_name'].'/sort');
				ksort($sorts);
				
				foreach ($sorts as $sort_functions)
				{
					foreach ((array)$sort_functions as $fn)
					{
						usort($models, $fn);
					}
				}
			}
			
			return count($models) > 1 ? $models : current($models);
		}
	}
	
	static function get_col($column, $where_string, $parameters, int $limit = null)
	{
		if ($limit > 0)
		{
			$limit_string = ' LIMIT '.$limit;
		}
		
		if ($where_string)
		{
			$where_string = ' WHERE '.$where_string;
		}
		
		$class = get_called_class();
		
		if ($result = DB::query('SELECT '.$class::$schema['id_key'].','.$column.' FROM '.$class::$schema['table_name'].$where_string.$limit_string, $parameters))
		{
			foreach ($result as $row)
			{
				$return[$row[$class::$schema['id_key']]] = $row[$column];
			}
			
			asort($return, SORT_NATURAL);
		}
		
		return (array)@$return;
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
	
	function tags()
	{
		if ($this->tags === null)
		{
			$this->tags = [];
			$class = get_called_class();
			
			if ($class::$schema['config_name'] !== null)
			{
				$tags_config = (array)Config::get($class::$schema['config_name'].'/tags');
				
				foreach ($tags_config as $tag => $boolean_condition)
				{
					if ($boolean_condition($this))
					{
						$this->tags[] = $tag;
					}
				}
			}
		}
		
		return $this->tags;
	}
	
	function classes($append = null, $echo = false)
	{
		if ($this->classes === null)
		{
			$this->classes = [];
			
			$tags = $this->tags();
			
			array_walk($tags, function(&$tag) 
			{ 
				$tag = 'tag-'.$tag;
			});
			
			$this->classes = array_merge(
				$this->classes, 
				$tags
			);
		}
		
		if ($echo)
		{
			echo trim(implode(' ', array_merge($this->classes, [$append])));
		}
		else
		{
			return array_merge($this->classes, [$append]);
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