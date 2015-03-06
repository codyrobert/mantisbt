<?php
namespace Flickerbox;

class Page
{
	protected $options = [
		'view' => 'pages/default',
	];
	
	function __construct(array $options = null)
	{
		$this->options = array_merge($this->options, (array)$options);
		register_shutdown_function(array(&$this, 'shutdown'));
	}
	
	function __get($key)
	{
		return @$this->options[$key];
	}
	
	function __set($key, $val)
	{
		$this->options[$key] = $val;
	}
	
	function shutdown()
	{
		$engine = \Flickerbox\Template::engine();
		
		echo $engine->render(
			$this->options['view'], 
			$this->options + array('content' => ob_get_clean())
		);
	}
}