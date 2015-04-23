<?php
namespace Core\Template\Extension;


use Model\User;

use Cocur\Slugify\Slugify;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;


class Gravatar implements ExtensionInterface
{
	protected $body_classes = [];
	
    public function register(Engine $engine)
    {
        $engine->registerFunction('gravatar', [$this, 'gravatar']);
    }
	
	function gravatar($options = null)
	{
		if (!is_array($options))
		{
			$options = ['email' => $options];
		}
		
		$options = array_merge([
			'email'		=> 'generic-avatar-since-user-not-found',
			'default'	=> 'identicon',
			'rating'	=> 'G',
			'size'		=> 120,
		], (array)$options);
		
		$hash = md5(strtolower(trim($options['email'])));
		$url = '//secure.gravatar.com/avatar/'.$hash.'?d='.$options['default'].'&r='.$options['rating'].'&s='.$options['size'];
		
		return '<img class="avatar" src="'.$url.'" />';
	}
}