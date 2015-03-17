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
}