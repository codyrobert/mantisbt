<?php
namespace Core;




/**
 * Basic Twiki support with old-style wiki integration.
 */
class MantisCoreTwikiPlugin extends MantisCoreWikiPlugin {
	/**
	 * Plugin Registration
	 * @return void
	 */
	function register() {
		$this->name = 'MantisBT Twiki Integration';
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
		$t_base = \Core\Plugin::config_get( 'engine_url' );

		$t_namespace = \Core\Plugin::config_get( 'root_namespace' );
		if( !\Core\Utility::is_blank( $t_namespace ) ) {
			$t_base .= urlencode( $t_namespace ) . '/';
		}

		if( !is_null( $p_project_id ) && $p_project_id != ALL_PROJECTS ) {
			$t_base .= urlencode( \Core\Project::get_name( $p_project_id ) ) . '/';
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
		return $this->base_url( \Core\Bug::get_field( $p_bug_id, 'project_id' ) ) . 'IssueNumber' . (int)$p_bug_id;
	}

	/**
	 * Wiki link to a project
	 *
	 * @param integer $p_event 	    Event.
	 * @param integer $p_project_id A project identifier.
	 * @return string
	 */
	function link_project( $p_event, $p_project_id ) {
		return $this->base_url( $p_project_id );
	}
}