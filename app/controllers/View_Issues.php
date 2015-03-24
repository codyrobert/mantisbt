<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Auth;
use Core\Category;
use Core\Config;
use Core\Controller\Authenticated_Page;
use Core\Current_User;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Request;
use Core\URL;


class View_Issues extends Authenticated_Page
{
	function __construct($params)
	{
		parent::__construct();
		
		App::queue_css(URL::get('css/status_config.php'));
		Category::get_all_rows(Helper::get_current_project());
	}
	
	function action_my_view()
	{
		$user_id = Auth::get_current_user_id();
		$project_id = Helper::get_current_project();
		
		$boxes = array_filter(Config::get('myview.boxes'));
		asort($boxes);
		
		foreach ($boxes as $label => $priority)
		{
			if (($label == 'assigned' && (Current_User::is_anonymous() || !Access::has_project_level(Config::mantis_get('handle_bug_threshold'), $project_id, $user_id))) ||
				($label == 'monitored' && ( Current_User::is_anonymous() or !Access::has_project_level(Config::mantis_get('monitor_bug_threshold'), $project_id, $user_id))) ||
				(in_array($label, ['reported', 'feedback', 'verify']) && (Current_User::is_anonymous() or !Access::has_project_level(Config::mantis_get('report_bug_threshold'), $project_id, $user_id)))
			)
			{
				unset($boxes[$label]);
			}
		}
		
		$this->set([
			'page_title'		=> Lang::get( 'my_view_link' ),
			'view'				=> 'Pages/My_View',
			
			'section_title'		=> Lang::get('my_view_title_assigned'),
			
			'user_id'			=> $user_id,
			'project_id'		=> $project_id,
			
			'page_number'		=> GPC::get_int('page_number', 1),
			'per_page'			=> Config::get('myview.bugcount'),
			'boxes_position'	=> Config::get('myview.is_fixed_position'),
			
			'boxes'				=> array_keys($boxes),
		]);
	}
}