<?php
namespace Core;


class App
{
	protected static $css = null;
	protected static $js = null;

	static function add_page_title($title_part)
	{
		Config::set('page_title', array_merge((array)Config::get('page_title'), array($title_part)));
	}
	
	static function page_title()
	{
		$title_parts = array_merge(array_filter((array)Config::get('page_title')), array(Config::get('app')['page_title']));
		
		foreach ($title_parts as $key => $val)
		{
			$title_parts[$key] = filter_var($val, FILTER_SANITIZE_SPECIAL_CHARS);
		}
		
		return implode(Config::get('delimiters')['page_title'], $title_parts);
	}
	
	static function queue_css($url, $media = null)
	{
		if (!@count(self::$css))
		{
			Action::add('after_head', '\\Core\\App::output_css');
		}
		
		if (!in_array($media, ['screen', 'print']))
		{
			$media = 'all';
		}
		
		self::$css[$url] = $media;
	}
	
	static function dequeue_css($url)
	{
		unset(self::$css[$url]);
		
		if (!@count(self::$css))
		{
			Action::remove('after_head', '\\Core\\App::output_css');
		}
	}
	
	static function output_css()
	{
		foreach (self::$css as $url => $media)
		{
			echo '<link rel="stylesheet" type="text/css" href="'.$url.'" media="'.$media.'">', PHP_EOL;
		}
	}
	
	static function queue_js($url, $in_header = false)
	{
		if (!@count(self::$js))
		{
			Action::add('after_head', '\\Core\\App::output_header_js');
			Action::add('page_bottom', '\\Core\\App::output_footer_js');
		}
		
		self::$js[$url] = (bool)$in_header;
	}
	
	static function dequeue_js($url)
	{
		unset(self::$js[$url]);
		
		if (!@count(self::$js))
		{
			Action::remove('after_head', '\\Core\\App::output_header_js');
			Action::remove('page_bottom', '\\Core\\App::output_footer_js');
		}
	}
	
	static function output_header_js()
	{
		foreach (self::$js as $url => $in_header)
		{
			if ($in_header)
			{
				echo '<script src="'.$url.'"></script>', PHP_EOL;
			}
		}
	}
	
	static function output_footer_js()
	{
		foreach (self::$js as $url => $in_header)
		{
			if (!$in_header)
			{
				echo '<script src="'.$url.'"></script>', PHP_EOL;
			}
		}
	}
}