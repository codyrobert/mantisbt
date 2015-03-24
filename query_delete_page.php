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
 * Displays page to allow a user delete a stored query
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
 * @uses string_api.php
 */



\Core\Auth::ensure_user_authenticated();
\Core\Compress::enable();

$f_query_id = \Core\GPC::get_int( 'source_query_id' );
$t_redirect_url = 'query_view_page.php';
$t_delete_url = 'query_delete.php';

if( !\Core\Filter::db_can_delete_filter( $f_query_id ) ) {
	\Core\Print_Util::header_redirect( $t_redirect_url );
}

\Core\HTML::page_top();
?>
<br />
<div class="center">
<strong><?php print \Core\String::display( \Core\Filter::db_get_name( $f_query_id ) ); ?></strong>
<?php echo \Core\Lang::get( 'query_delete_msg' ); ?>

<form method="post" action="<?php print $t_delete_url; ?>">
<?php echo \Core\Form::security_field( 'query_delete' ) ?>
<br /><br />
<input type="hidden" name="source_query_id" value="<?php print $f_query_id; ?>"/>
<input type="submit" class="button" value="<?php print \Core\Lang::get( 'delete_query' ); ?>"/>
</form>

<form method="post" action="<?php print $t_redirect_url; ?>">
<?php # CSRF protection not required here - form does not result in modifications ?>
<input type="submit" class="button" value="<?php print \Core\Lang::get( 'go_back' ); ?>"/>
</form>

<?php
print '</div>';
\Core\HTML::page_bottom();
