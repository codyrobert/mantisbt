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
 * Handler to delete a stored query.
 *
 * Takes source_query_id as a parameter
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Flickerbox\Form::security_validate( 'query_delete' );

\Flickerbox\Auth::ensure_user_authenticated();
\Flickerbox\Compress::enable();

$f_query_id = \Flickerbox\GPC::get_int( 'source_query_id' );
$t_redirect_url = 'query_view_page.php';

if( !\Flickerbox\Filter::db_can_delete_filter( $f_query_id ) ) {
	\Flickerbox\Print_Util::header_redirect( $t_redirect_url );
} else {
	\Flickerbox\HTML::page_top();
	filter_db_delete_filter( $f_query_id );
	\Flickerbox\Form::security_purge( 'query_delete' );
	?>
	<br />
	<div class="center">
		<strong>
			<?php print \Flickerbox\Filter::db_get_name( $f_query_id ) . ' ' . \Flickerbox\Lang::get( 'query_deleted' ); ?>
		</strong>
		<form method="post" action="<?php print $t_redirect_url; ?>">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" class="button" value="<?php print \Flickerbox\Lang::get( 'go_back' ); ?>"/>
		</form>
	</div>
	<?php
	\Flickerbox\HTML::page_bottom();
}
