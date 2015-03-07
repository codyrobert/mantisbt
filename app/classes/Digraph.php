<?php
namespace Flickerbox;

/**
 * Directed graph creation and manipulation.
 */
class Digraph extends Graph {
	/**
	 * Constructor for Digraph objects.
	 * @param string $p_name       Name of the graph.
	 * @param array  $p_attributes Attributes.
	 * @param string $p_tool       Graphviz tool.
	 */
	function Digraph( $p_name = 'G', array $p_attributes = array(), $p_tool = 'dot' ) {
		parent::Graph( $p_name, $p_attributes, $p_tool );
	}

	/**
	 * Generates a directed graph representation (suitable for dot).
	 * @return void
	 */
	function generate() {
		echo 'digraph ' . $this->name . ' {' . "\n";

		$this->_print_graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->_build_attribute_list( $t_attr );
			echo "\t" . $t_src . ' -> ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		echo "};\n";
	}
}
