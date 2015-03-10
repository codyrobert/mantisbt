<?php
namespace Core;

/**
 * Alignment
 */
class Alignment {
	/**
	 * Wrap Text
	 */
	public $wrapText;

	/**
	 * Horizontal
	 */
	public $horizontal;

	/**
	 * Vertical
	 */
	public $vertical;

	/**
	 * Return XML
	 * @return string
	 */
	function asXml() {
		$t_xml = '<ss:Alignment ';

		if( $this->wrapText ) {
			$t_xml .= 'ss:WrapText="' . $this->wrapText.'" ';
		}

		if( $this->horizontal ) {
			$t_xml .= 'ss:Horizontal="' . $this->horizontal.'" ';
		}

		if( $this->vertical ) {
			$t_xml .= 'ss:Vertical="' . $this->vertical.'" ';
		}

		$t_xml .= '/>';

		return $t_xml;
	}
}