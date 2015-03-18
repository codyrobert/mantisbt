<?php
namespace Core;


use Core\Config;


class DB
{
	protected static $connection = null;
	
	static function startup()
	{
		if (self::$connection == null)
		{
			if (self::$connection = new \mysqli(Config::get('db')['host'], Config::get('db')['username'], Config::get('db')['password'], Config::get('db')['database']))
			{
				register_shutdown_function(array(__NAMESPACE__.'\DB', 'shutdown'));
			}
		}
	}
	
	static function shutdown()
	{
		self::$connection->close();
	}
	
	static function query($query, array $values = null, $array_or_object = null)
	{
		self::startup();
		
		if($statement = self::$connection->prepare($query))
		{
			if ($values)
			{
				foreach($values as $key => $val)
				{
					$params[] = &$values[$key];
					$types .= is_int($val) ? 'i' : 's';
				}
				
				array_unshift($params, $types);
				call_user_func_array(array(&$statement, 'bind_param'), $params);
			}
			
			$statement->execute();
			
			if ($result = $statement->get_result())
			{
				while ($row = $result->fetch_assoc())
				{
					$rows[] = ($array_or_object === 'OBJECT') ? (object)$row : (array)$row;
				}
				
				$statement->close();
				
				if (count($rows) > 1)
				{
					return $rows;
				}
				if (count($rows) === 1)
				{
					return $rows[0];
				}
			}
		}
		
		return false;
	}
	
	static function find_globally($string)
	{
		if (_IS_LOCAL_ENVIRONMENT)
		{
			ini_set('memory_limit', '2G');
			
			self::startup();
			
			foreach (self::query('SHOW TABLES') as $table_name)
			{
				$table_name = current($table_name);
				$table_structure = self::query('DESCRIBE '.$table_name);
				
				foreach ($table_structure as $column)
				{
					if (strstr($column['Field'], $string))
					{
						$response[] = 'Match found in table: '.$table_name.', column: '.$column['Field'];	
					}
					
					if ($search = self::query('SELECT * FROM '.$table_name.' WHERE '.$column['Field'].' LIKE ?', ['%'.$string.'%']))
					{
						foreach ((array)$search as $row)
						{
							$response[] = 'Match found in table: '.$table_name.', row: '.json_encode($row);
						}
					}
				}
			}
			
			!dd($response);
		}
	}
}