<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Generate Permanent link
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Flickerbox\HTML::page_top();

\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'create_permalink_threshold' ) );

$f_url = \Flickerbox\String::sanitize_url( \Flickerbox\GPC::get_string( 'url' ) );
?>
<div>
	<p>
<?php
echo \Flickerbox\Lang::get( 'filter_permalink' ), '<br />';
$t_safe_url = \Flickerbox\String::display_line( $f_url );
echo '<a href="' . $t_safe_url . '">' . $t_safe_url . '</a></p>';

$t_create_short_url = \Flickerbox\Config::mantis_get( 'create_short_url' );

if( !\Flickerbox\Utility::is_blank( $t_create_short_url ) ) {
	\Flickerbox\Print_Util::bracket_link( sprintf( $t_create_short_url, $f_url ), \Flickerbox\Lang::get( 'create_short_link' ), true );
}
?>
</div>
<?php
\Flickerbox\HTML::page_bottom();
