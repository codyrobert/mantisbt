<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Category;
use Core\Config;
use Core\Controller\Authenticated_Page;
use Core\Current_User;
use Core\Form;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Menu;
use Core\Model;
use Core\Print_Util;
use Core\Request;
use Core\Session;
use Core\String;
use Core\Template;
use Core\URL;
use Core\User;
use Core\Utility;


class Ticket extends Authenticated_Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		App::queue_css(URL::get('media/css/ticket.css'));	
	}
	
	function action_view($id)
	{
		$ticket = new \Model\Ticket($id);
		
		if ($ticket->loaded())
		{
			Access::ensure_bug_level(Config::mantis_get('view_bug_threshold'), $id);
			
			$this->set([
				'page_title'		=> Lang::get( '' ),
				'view'				=> 'Pages/Ticket/View',
				'show'				=> (object)array_fill_keys(Config::get('ticket.view_page_fields'), true),
				'ticket'			=> $ticket,
			]);
		
		}
		else
		{
			!dd('Error! could not find bug!');
		}
		
		/*$t_file = __FILE__;
		$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		$t_show_page_header = true;
		$t_force_readonly = false;
		$t_fields_config_option = 'bug_view_page_fields';
		
		define( 'BUG_VIEW_INC_ALLOW', true );
		include( ROOT.'/bug_view_inc.php' );*/

	}
}