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
			}
		}
		
		return $this->projects;
	}
	
	function projects_list()
	{
		$projects = $this->projects();
		
		array_walk($projects, function(&$project) 
		{
			$project = $project->name;
		});
		
		asort($projects);
		
		return $projects;
	}
	
	function &tickets()
	{
		if ($this->tickets === null)
		{
			$this->tickets = Ticket::find('(handler_id = ? || reporter_id = ? || id IN (SELECT bug_id FROM mantis_bug_monitor_table WHERE user_id = ?)) AND (status < 90 OR (status = 90 AND last_updated >= DATE(NOW()) - INTERVAL 1 WEEK))', [
				$this->id,
				$this->id,
				$this->id,
			], -1);
		}
		
		return $this->tickets;
	}
}