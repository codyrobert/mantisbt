<?php
namespace Flickerbox;




/**
 * Basic WikkaWiki support with old-style wiki integration.
 */
class MantisCoreWikkaWikiPlugin extends MantisCoreWikiPlugin {
	/**
	 * Plugin Registration
	 * @return void
	 */
	function register() {
		$this->name = 'MantisBT WikkaWiki Integration';
		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.3.0',
		);
	}

	/**
	 * Wiki base url
	 *
	 * @param integer $p_project_id A project identifier.
	 * @return string
	 */
	function base_url( $p_project_id = null ) {
		$t_base = \Flickerbox\Plugin::config_get( 'engine_url' ) . 'wikka.php?wakka=';

		$t_namespace = ucfirst( \Flickerbox\Plugin::config_get( 'root_namespace' ) );
		if( !\Flickerbox\Utility::is_blank( $t_namespace ) ) {
			$t_base .= urlencode( $t_namespace );
		}

		if( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( \Flickerbox\Project::get_name( $p_project_id ) );
		}
		return $t_base;
	}

	/**
	 * Wiki link to a bug
	 *
	 * @param integer $p_event  Event.
	 * @param integer $p_bug_id A bug identifier.
	 * @return string
	 */
	function link_bug( $p_event, $p_bug_id ) {
		return $this->base_url( \Flickerbox\Bug::get_field( $p_bug_id, 'project_id' ) ) . 'Issue' . (int)$p_bug_id;
	}

	/**
	 * Wiki link to a project
	 *
	 * @param integer $p_event      Event.
	 * @param integer $p_project_id A project identifier.
	 * @return string
	 */
	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id ) . 'Start';
	}
}