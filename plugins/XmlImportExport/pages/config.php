<?php
# Copyright (c) 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

\Core\Form::security_validate( 'plugin_XmlImportExport_config' );
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_plugin_threshold' ) );

/**
 * Sets plugin config option if value is different from current/default
 * @param string $p_name  option name
 * @param string $p_value value to set
 * @return void
 */
function config_set_if_needed( $p_name, $p_value ) {
	if ( $p_value != \Core\Plugin::config_get( $p_name ) ) {
		\Core\Plugin::config_set( $p_name, $p_value );
	}
}

$t_redirect_url = \Core\Plugin::page( 'config_page', true );
\Core\HTML::page_top( null, $t_redirect_url );

config_set_if_needed( 'import_threshold' , \Core\GPC::get_int( 'import_threshold' ) );
config_set_if_needed( 'export_threshold' , \Core\GPC::get_int( 'export_threshold' ) );

\Core\Form::security_purge( 'plugin_XmlImportExport_config' );

\Core\HTML::operation_successful( $t_redirect_url );
\Core\HTML::page_bottom();
