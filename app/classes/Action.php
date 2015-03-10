<?php
namespace Core;


class Action
{

	protected static $actions = [];
	
	static function add($key, $method, int $priority = null, int $arguments = null)
	{
		if (!$priority)
		{
			$priority = 10;
		}
		
		self::$actions[$key][(int)$priority][] = [
			'method'	=> $method,
			'accepted'	=> (int)$arguments,
		];
	}
	
	static function remove($key, $priority = null)
	{
		if (is_int($priority))
		{
			self::$actions[$key][$priority] = [];
		}
		else
		{
			self::$actions[$key] = [];
		}
	}
	
	static function perform($key, array $arguments = null)
	{
		if (array_key_exists($key, self::$actions))
		{
			ksort(self::$actions[$key], SORT_NUMERIC);
			
			foreach (self::$actions[$key] as $key_actions)
			{
				foreach ($key_actions as $action)
				{
					call_user_func_array($action['method'], (array)@array_slice((array)$arguments, 0, $action['accepted']));
				}
			}
		}
	}

}