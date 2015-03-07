<?php
namespace Flickerbox;

/**
 * Interior
 */
class Interior {
	/**
	 * Color
	 */
	public $color;

	/**
	 * Pattern
	 */
	public $pattern;

	/**
	 * Return XML
	 * @return string
	 */
	function asXml() {
		$t_xml = '<ss:Interior ';

		if( $this->color ) {
		   $t_xml .= 'ss:Color="' . $this->color .'" ss:Pattern="'. $this->pattern . '" ';
		}

		$t_xml .= '/>';

		return $t_xml;
	}
}