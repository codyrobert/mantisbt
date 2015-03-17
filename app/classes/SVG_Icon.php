<?php
namespace Core;


class SVG_Icon
{
	protected $file;
	protected $size = [32, 32];
	protected $aspect_ratio;
	
	function __construct($file, array $size = null)
	{
		$this->file = MEDIA.'svgs/'.$file.'.svg';
		
		if ($size)
		{
			$this->size = $size;
		}
		
		$this->get_aspect_ratio();
	}
	
	protected function get_aspect_ratio()
	{
		if ($handle = fopen($this->file, 'r'))
		{
			while ($line = fgets($handle))
			{
				if (strstr($line, 'viewBox="'))
				{
					preg_match('/\bviewBox\b="([^"]*)"/', $line, $matches);
					
					list($x, $y, $width, $height) = explode(' ', $matches[1]);
					$this->aspect_ratio = round($height / $width, 4) * 100;
					
					break;
				}
			}
			
			fclose($handle);
		}
	}
	
	function __toString()
	{
		return
			'<div class="svg-wrap" style="height:'.@$this->size[0].'px;width:'.@$this->size[1].'px;">'.PHP_EOL.
				'<div style="padding-bottom:'.$this->aspect_ratio.'%;">'.PHP_EOL.
					file_get_contents($this->file).PHP_EOL.
				'</div>'.PHP_EOL.
			'</div>'.PHP_EOL;
	}
}