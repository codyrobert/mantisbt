<?php
namespace Core\Template\Extension;


use Cocur\Slugify\Slugify;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;


class Body_Class implements ExtensionInterface
{
	protected $body_classes = [];
	
    public function register(Engine $engine)
    {
        $engine->registerFunction('body_class', [$this, 'body_class']);
    }
	
	function body_class($append = null)
	{
		if ($append)
		{
			$this->body_classes[] = $append;
		}
		
		$slugify = new Slugify();
		
		$layout = str_replace(array(
			APP.'views/Layouts',
			'.php',
		), array(
			'',
			'',
		), $this->template->path());
		
		$layout = 'layout-'.$slugify->slugify($layout);
		$page = 'page-'.$slugify->slugify($_SERVER['REQUEST_URI']);
		
		$this->body_classes = array_merge([$layout], [$page], $this->body_classes);
		
		array_filter($this->body_classes);
		return implode(' ', $this->body_classes);
	}
}