<?php
namespace Core;

/**
 * The <tt>ExcelStyle</tt> class is able to render style information
 *
 * <p>For more information regarding the values taken by the parameters of this class' methods
 * please see <a href="http://msdn.microsoft.com/en-us/library/aa140066(v=office.10).aspx#odc_xmlss_ss:style">
 * the ss:Style documentation</a>.</p>
 *
 */
class ExcelStyle {
	/**
	 * Id
	 */
	private $id;

	/**
	 * Parent id
	 */
	private $parent_id;

	/**
	 * Interior
	 */
	private $interior;

	/**
	 * Font
	 */
	private $font;

	/**
	 * Border
	 */
	private $border;

	/**
	 * Alignment
	 */
	private $alignment;

	/**
	 * Default Constructor
	 * @param string $p_id        The unique style id.
	 * @param string $p_parent_id The parent style id.
	 */
	function __construct( $p_id, $p_parent_id = '' ) {
		$this->id = $p_id;
		$this->parent_id = $p_parent_id;
	}

	/**
	 * Return ID
	 * @return integer
	 */
	function getId() {
		return $this->id;
	}

	/**
	 * Set background color
	 * @param string $p_color   The color in #rrggbb format or a named color.
	 * @param string $p_pattern Fill Pattern.
	 * @return void
	 */
	function setBackgroundColor( $p_color, $p_pattern = 'Solid' ) {
		if( ! isset ( $this->interior ) ) {
			$this->interior = new \Core\Excel\Interior();
		}

		$this->interior->color = $p_color;
		$this->interior->pattern = $p_pattern;
	}

	/**
	 * Set Font
	 * @param integer $p_bold   Either 1 for bold, 0 for not bold.
	 * @param string  $p_color  The color in #rrggbb format or a named color.
	 * @param string  $p_name   The name of the font.
	 * @param integer $p_italic Either 1 for italic, 0 for not italic.
	 * @return void
	 */
	function setFont( $p_bold, $p_color = '', $p_name = '', $p_italic = -1 ) {
		if( !isset( $this->font ) ) {
			$this->font = new \Core\Excel\Font();
		}

		if( $p_bold != -1 ) {
			$this->font->bold = $p_bold;
		}
		if( $p_color != '' ) {
			$this->font->color = $p_color;
		}
		if( $p_name != '' ) {
			$this->font->fontName = $p_name;
		}
		if( $p_italic != -1 ) {
			$this->font->italic = $p_italic;
		}
	}

	/**
	 * Sets the border values for the style
	 *
	 * <p>The values are set for the following positions: Left, Top, Right, Bottom. There is no
	 * support for setting individual values.</p>
	 *
	 * @param string  $p_color      The color in #rrggbb format or a named color.
	 * @param string  $p_line_style None, Continuous, Dash, Dot, DashDot, DashDotDot, SlantDashDot, or Double.
	 * @param integer $p_weight     Thickness in points.
	 * @return void
	 */
	function setBorder( $p_color, $p_line_style = 'Continuous', $p_weight = 1 ) {
		if( !isset( $this->border ) ) {
			$this->border = new \Core\Excel\Border();
		}

		if( $p_color != '' ) {
			$this->border->color = $p_color;
		}

		if( $p_line_style != '' ) {
			$this->border->lineStyle = $p_line_style;
		}

		if( $p_weight != -1 ) {
			$this->border->weight = $p_weight;
		}
	}

	/**
	 * Sets the alignment for the style
	 *
	 * @param integer $p_wrap_text  Either 1 to wrap, 0 to not wrap.
	 * @param string  $p_horizontal Automatic, Left, Center, Right, Fill, Justify, CenterAcrossSelection, Distributed, and JustifyDistributed.
	 * @param string  $p_vertical   Automatic, Top, Bottom, Center, Justify, Distributed, and JustifyDistributed.
	 * @return void
	 */
	function setAlignment( $p_wrap_text, $p_horizontal = '', $p_vertical = '' ) {
		if( !isset( $this->alignment ) ) {
			$this->alignment = new \Core\Excel\Alignment();
		}

		if( $p_wrap_text != '' ) {
			$this->alignment->wrapText = $p_wrap_text;
		}

		if( $p_horizontal != '' ) {
			$this->alignment->horizontal = $p_horizontal;
		}

		if( $p_vertical != '' ) {
			$this->alignment->vertical = $p_vertical;
		}
	}

	/**
	 * Return XML
	 * @return string
	 */
	function asXml() {
		$t_xml = '<ss:Style ss:ID="' . $this->id.'" ss:Name="'.$this->id.'" ';
		if( $this->parent_id != '' ) {
			$t_xml .= 'ss:Parent="' . $this->parent_id .'" ';
		}
		$t_xml .= '>';
		if( $this->interior ) {
			$t_xml .= $this->interior->asXml();
		}
		if( $this->font ) {
			$t_xml .= $this->font->asXml();
		}
		if( $this->border ) {
			$t_xml .= $this->border->asXml();
		}
		if( $this->alignment ) {
			$t_xml .= $this->alignment->asXml();
		}
		$t_xml .= '</ss:Style>'."\n";

		return $t_xml;
	}
}