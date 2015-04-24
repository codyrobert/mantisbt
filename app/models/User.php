<?php
namespace Model;


use Core\DB;
use Model\Ticket;


class User extends \Core\Model
{
	protected static $_current_user = null;
	
	protected static $schema = [
		'table_name'			=> 'mantis_user_table',
		'projects_table_name'	=> 'mantis_project_user_list_table',
		'id_key'				=> 'id',
	];
	
	protected $preferences = null;
	protected $projects = null;
	protected $tickets = null;
	protected $related_users = null;
	
	static function current()
	{
		if (self::$_current_user === null)
		{
			$user = new User(\Core\Auth::get_current_user_id());
			self::$_current_user = $user->loaded() ? $user : false;
		}
		
		return self::$_current_user;
	}
	
	function &preferences()
	{
		if ($this->preferences === null)
		{
			$this->preferences = User\Preferences::find('user_id = ?', [
				$this->id,
			]);
		}
		
		return $this->preferences;
	}
	
	function &projects()
	{
		if ($this->projects === null)
		{
			if ($result = DB::query('SELECT project_id FROM '.self::$schema['projects_table_name'].' WHERE user_id = ?', [$this->id]))
			{
				foreach ($result as $row)
				{
					$this->projects[$row['project_id']] = new Project($row['project_id']);
				}
		
				usort($this->projects, function($a, $b) {
					return strcasecmp($a->name, $b->name);
				});
			}
		}
		
		return $this->projects;
	}
	
	function projects_list()
	{
		$projects = $this->projects();
		
		return $projects;
	}
	
	function &tickets()
	{
		if ($this->tickets === null)
		{
			$this->tickets = Ticket::find('project_id IN (SELECT project_id FROM '.self::$schema['projects_table_name'].' WHERE user_id = ?) AND (status < 90 OR (status = 90 AND last_updated >= ?))', [
				$this->id,
				strtotime(date('Y-m-d')) - (60 * 60 * 24 * 7), // 1 week ago
			], -1);
		}
		
		return $this->tickets;
	}
	
	function &related_users()
	{
		if ($this->related_users === null)
		{
			$user_result = (array)User::find('enabled = 1 AND id IN (SELECT user_id FROM '.self::$schema['projects_table_name'].' WHERE project_id IN (SELECT project_id FROM '.self::$schema['projects_table_name'].' WHERE user_id = ?))', [
				$this->id,
			], -1);
			
			foreach ($user_result as $row)
			{
				$user_scope[] = $row->id;
			}
			
			$ticket_result = DB::query('SELECT id,handler_id,reporter_id FROM '.Ticket::schema()['table_name'].' WHERE project_id IN (SELECT project_id FROM '.self::$schema['projects_table_name'].' WHERE user_id = ?) AND (status < 90 OR (status = 90 AND last_updated >= ?))', [
				$this->id,
				strtotime(date('Y-m-d')) - (60 * 60 * 24 * 7), // 1 week ago	
			]);
			
			foreach ((array)$ticket_result as $row)
			{
				$ticket_ids[] = $row['id'];
				$active_users[] = $row['handler_id'];
				$active_users[] = $row['reporter_id'];
			}
			
			$monitors_result = DB::query('SELECT user_id FROM '.Ticket::schema()['monitor_table_name'].' WHERE bug_id IN ('.implode(',', $ticket_ids).')');
			
			foreach ((array)$monitors_result as $row)
			{
				$active_users[] = $row['user_id'];
			}
			
			$active_users = array_unique($active_users);
			$active_users = array_intersect($user_scope, $active_users);
			
			$this->related_users = User::find('id IN ('.implode(',', $active_users).') ORDER BY access_level DESC, realname ASC', null, -1);
		}
		
		return $this->related_users;
	}
}