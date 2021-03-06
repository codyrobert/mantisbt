<?php
namespace Model;


use Core\Access;
use Core\Auth;
use Core\Bug;
use Core\Config;
use Core\DB;
use Core\Model;
use Core\Print_Util;

use Model\Project;


class Ticket extends Model
{
	protected static $schema = [
		'config_name'			=> 'ticket',
		'table_name'			=> 'mantis_bug_table',
		'text_table_name'		=> 'mantis_bug_text_table',
		'monitor_table_name'	=> 'mantis_bug_monitor_table',
		'id_key'				=> 'id',
	];
	
	protected $project = null;
	
	function __construct($id = null)
	{
		parent::__construct($id);
		
		if ($this->loaded())
		{
			if ($result = DB::query('SELECT * FROM '.self::$schema['text_table_name'].' WHERE id = ? LIMIT 1', [$this->id]))
			{
				$this->data = array_merge($result, $this->data);
			}
		}
	}
	
	function get_status_list()
	{
		$status_list = Print_Util::get_status_option_list(
		
			Access::get_project_level($this->project_id),
			$this->status,
			false,
			# Add close if user is bug's reporter, still has rights to report issues
			# (to prevent users downgraded to viewers from updating issues) and
			# reporters are allowed to close their own issues
			(Bug::is_user_reporter($this->id, Auth::get_current_user_id()) && Access::has_bug_level(Config::mantis_get('report_bug_threshold'), $this->id) && Config::mantis_get('allow_reporter_close') === ON),
			$this->project_id
		
		);
	
		ksort( $status_list );
		
		return (array)$status_list;
	}
	
	function project()
	{
		if ($this->project === null)
		{
			$this->project = new Project($this->project_id);
		} 
		
		return $this->project;
	}
	
	function priority()
	{
		return 'P';//$this->priority;
	}
	
	function on_client()
	{
		return false;
	}
}