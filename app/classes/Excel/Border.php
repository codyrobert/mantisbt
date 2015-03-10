<?php
namespace Core;

/**
 * Border
 */
class Border {
	/**
	 * Border Positions
	 */
	private $positions = array('Left', 'Top', 'Right', 'Bottom');

	/**
	 * Color
	 */
	public $color;

	/**
	 * Line Style
	 */
	public $lineStyle;

	/**
	 * Border Weight
	 */
	public $weight;

	/**
	 * Return XML
	 * @return string
	 */
	function asXml() {
		$t_xml = '<ss:Borders>';

		foreach ( $this->positions as $p_position ) {
			$t_xml.= '<ss:Border ss:Position="' . $p_position .'" ';

			if( $this->lineStyle ) {
				$t_xml .= 'ss:LineStyle="' . $this->lineStyle .'" ';
			}

			if( $this->color ) {
				$t_xml .= 'ss:Color="' . $this->color .'" ';
			}

			if( $this->weight ) {
				$t_xml .= 'ss:Weight="' . $this->weight .'" ';
			}

			$t_xml.= '/>';
		}

		$t_xml .= '</ss:Borders>';

		return $t_xml;
	}
}