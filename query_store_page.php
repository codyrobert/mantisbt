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
 * Page to allow user to store a stored filter
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */



\Core\Auth::ensure_user_authenticated();

\Core\Compress::enable();

\Core\HTML::page_top();
?>
<br />
<div id="save-filter">
<?php
$t_query_to_store = \Core\Filter::db_get_filter( \Core\GPC::get_cookie( \Core\Config::mantis_get( 'view_all_cookie' ), '' ) );
$t_query_arr = \Core\Filter::db_get_available_queries();

# Let's just see if any of the current filters are the
# same as the one we're about the try and save
foreach( $t_query_arr as $t_id => $t_name ) {
	if( \Core\Filter::db_get_filter( $t_id ) == $t_query_to_store ) {
		print \Core\Lang::get( 'query_exists' ) . ' (' . $t_name . ')<br />';
	}
}

# Check for an error
$t_error_msg = strip_tags( \Core\GPC::get_string( 'error_msg', null ) );
if( $t_error_msg != null ) {
	print '<br />' . $t_error_msg . '<br /><br />';
}

print \Core\Lang::get( 'query_name_label' ) . \Core\Lang::get( 'word_separator' );
?>
<form method="post" action="query_store.php">
<?php echo \Core\Form::security_field( 'query_store' ) ?>
<input type="text" name="query_name" /><br />
<?php
if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'stored_query_create_shared_threshold' ) ) ) {
	print '<input type="checkbox" name="is_public" value="on" /> ';
	print \Core\Lang::get( 'make_public' );
	print '<br />';
}
?>
<input type="checkbox" name="all_projects" value="on" <?php \Core\Helper::check_checked( ALL_PROJECTS == \Core\Helper::get_current_project() ) ?> />
<?php print \Core\Lang::get( 'all_projects' ); ?><br /><br />
<input type="submit" class="button" value="<?php print \Core\Lang::get( 'save_query' ); ?>" />
</form>
<form action="view_all_bug_page.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<input type="submit" class="button" value="<?php print \Core\Lang::get( 'go_back' ); ?>" />
</form>
<?php
echo '</div>';
\Core\HTML::page_bottom();
