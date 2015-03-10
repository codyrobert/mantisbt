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
 * Handler to store a filter
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'query_store' );

\Core\Auth::ensure_user_authenticated();
\Core\Compress::enable();

$f_query_name = strip_tags( \Core\GPC::get_string( 'query_name' ) );
$f_is_public = \Core\GPC::get_bool( 'is_public' );
$f_all_projects = \Core\GPC::get_bool( 'all_projects' );

$t_query_redirect_url = 'query_store_page.php';

# We can't have a blank name
if( \Core\Utility::is_blank( $f_query_name ) ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( \Core\Lang::get( 'query_blank_name' ) );
	\Core\Print_Util::header_redirect( $t_query_redirect_url );
}

# mantis_filters_table.name has a length of 64. Not allowing longer.
if( !\Core\Filter::name_valid_length( $f_query_name ) ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( \Core\Lang::get( 'query_name_too_long' ) );
	\Core\Print_Util::header_redirect( $t_query_redirect_url );
}

# Check and make sure they don't already have a
# query with the same name
$t_query_arr = \Core\Filter::db_get_available_queries();
foreach( $t_query_arr as $t_id => $t_name )	{
	if( $f_query_name == $t_name ) {
		$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
			. urlencode( \Core\Lang::get( 'query_dupe_name' ) );
		\Core\Print_Util::header_redirect( $t_query_redirect_url );
		exit;
	}
}

$t_project_id = \Core\Helper::get_current_project();
if( $f_all_projects ) {
	$t_project_id = 0;
}

$t_filter_string = \Core\Filter::db_get_filter( \Core\GPC::get_cookie( \Core\Config::mantis_get( 'view_all_cookie' ), '' ) );

$t_new_row_id = \Core\Filter::db_set_for_current_user( $t_project_id, $f_is_public,
												$f_query_name, $t_filter_string );

\Core\Form::security_purge( 'query_store' );

if( $t_new_row_id == -1 ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( \Core\Lang::get( 'query_store_error' ) );
	\Core\Print_Util::header_redirect( $t_query_redirect_url );
} else {
	\Core\Print_Util::header_redirect( 'view_all_bug_page.php' );
}
