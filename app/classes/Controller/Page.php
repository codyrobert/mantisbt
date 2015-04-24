<?php
namespace Core\Controller;


use Core\App;
use Core\Config;
use Core\Controller;
use Core\Template;
use Core\URL;


abstract class Page extends Controller
{
	protected $options = [
		'view' => 'Default',
	];
	
	function __construct($params)
	{
		parent::__construct($params);
		
		App::queue_css(URL::get('css/default.css'));
		App::queue_css(URL::get('media/css/master.css'));
		App::queue_css(URL::get('css/jquery-ui-1.11.2.min.css'));
		App::queue_css(URL::get('css/common_config.php'));
		App::queue_css(URL::get('css/status_config.php'));
		
		App::queue_js('//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js', true);
		App::queue_js(URL::get('media/vendors/webcomponentsjs/webcomponents.min.js'), true);
		App::queue_js(URL::get('media/vendors/polymer/polymer.min.js'), true);
		
		App::queue_js(URL::get('javascript_config.php'), true);
		App::queue_js(URL::get('javascript_translations.php'), true);
		App::queue_js(URL::get('javascript/jquery-ui-1.11.2.min.js'), true);
		App::queue_js(URL::get('javascript/common.js'), true);
	}
	
	function set(array $options = null)
	{
		$this->options = array_merge($this->options, (array)$options);
	}
	
	function __get($key)
	{
		return @$this->options[$key];
	}
	
	function __set($key, $val)
	{
		$this->options[$key] = $val;
	}
	
	function render()
	{
		$engine = Template::engine();
		
		if (@$this->options['page_title'])
		{
			if (is_array($this->options['page_title']))
			{
				Config::set('page_title', array_merge((array)Config::get('page_title'), $this->options['page_title']));
			}
			else
			{
				App::add_page_title($this->options['page_title']);
			}
		}
		
		echo $engine->render(
			$this->options['view'], 
			$this->options + ['content' => ob_get_clean(), 'controller_name' => $this->name()]
		);
	}
}