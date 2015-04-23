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
	
	function gravatar(array $options = null)
	{
		$options = array_merge([
			'default'	=> 'identicon',
			'rating'	=> 'G',
			'size'		=> 80,
		], (array)$options);
		
		if (User::current())
		{
			$hash = md5(strtolower(trim(User::current()->email)));
		}
		else
		{
			$hash = md5('generic-avatar-since-user-not-found');
		}
	
		# Build Gravatar URL
		$url = '//secure.gravatar.com/avatar/'.$hash.'?d='.$options['default'].'&r='.$options['rating'].'&s='.$options['size'];
		
		
		return '<img class="avatar" src="'.$url.'" />';
	}
}