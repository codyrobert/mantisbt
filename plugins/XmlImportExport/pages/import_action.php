<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Process XML Import
 */

$t_plugin_path = \Flickerbox\Config::mantis_get( 'plugin_path' );
require_once( $t_plugin_path . 'XmlImportExport/ImportXml.php' );

\Flickerbox\Form::security_validate( 'plugin_xml_import_action' );

auth_reauthenticate( );

$f_file = gpc_get_file( 'file', -1 );
$f_strategy = \Flickerbox\GPC::get_string( 'strategy' );
$f_fallback = \Flickerbox\GPC::get_string( 'fallback' );
$f_project = \Flickerbox\GPC::get_int( 'project_id' ); # not used, but ensures a project is selected
$f_keepcategory = \Flickerbox\GPC::get_bool( 'keepcategory' );
$f_defaultcategory = \Flickerbox\GPC::get_int( 'defaultcategory' );

\Flickerbox\File::ensure_uploaded( $f_file );

$t_importer = new ImportXML( $f_file, $f_strategy, $f_fallback, $f_keepcategory, $f_defaultcategory );

\Flickerbox\Form::security_purge( 'plugin_xml_import_action' );

\Flickerbox\HTML::page_top( \Flickerbox\Plugin::langget( 'import' ) );

\Flickerbox\HTML::print_manage_menu( 'manage_import_issues_page.php' );

?>

<br />

<pre>

<?php
$t_importer->import( );
?>

</pre>

<?php
\Flickerbox\HTML::page_bottom();
