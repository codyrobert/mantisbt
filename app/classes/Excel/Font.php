<?php
namespace Flickerbox;

/**
 * Font
 */
class Font {
	/**
	 * Bold
	 */
	public $bold;

	/**
	 * Colour
	 */
	public $color;

	/**
	 * Font Name
	 */
	public $fontName;

	/**
	 * Italic
	 */
	public $italic;

	/**
	 * Return XML
	 * @return string
	 */
	function asXml() {
		$t_xml = '<ss:Font ';

		if( $this->bold ) {
			$t_xml .= 'ss:Bold="' . $this->bold .'" ';
		}

		if( $this->color ) {
			$t_xml .= 'ss:Color="' . $this->color .'" ';
		}

		if( $this->fontName ) {
			$t_xml .= 'ss:FontName="' . $this->fontName .'" ';
		}

		if( $this->italic ) {
			$t_xml .= 'ss:Italic="' . $this->italic .'" ';
		}

		$t_xml .= '/>';

		return $t_xml;
	}
}