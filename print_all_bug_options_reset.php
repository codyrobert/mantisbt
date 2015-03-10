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
 * Reset prefs to defaults then redirect to account_prefs_page.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

define( 'PRINT_ALL_BUG_OPTIONS_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/print_all_bug_options_inc.php' );

\Core\Form::security_validate( 'print_all_bug_options_reset' );

\Core\Auth::ensure_user_authenticated();

# protected account check
\Core\Current_User::ensure_unprotected();

# get user id
$t_user_id = \Core\Auth::get_current_user_id();

# get the fields list
$t_field_name_arr = get_field_names();
$t_field_name_count = count( $t_field_name_arr );

# create a default array, same size than $t_field_name
for( $i=0; $i<$t_field_name_count; $i++ ) {
	$t_default_arr[$i] = 0 ;
}
$t_default = implode( '', $t_default_arr );

# reset to defaults
$t_query = 'UPDATE {user_print_pref} SET print_pref=' . \Core\Database::param() . ' WHERE user_id=' . \Core\Database::param();

$t_result = \Core\Database::query( $t_query, array( $t_default, $t_user_id ) );

\Core\Form::security_purge( 'print_all_bug_options_reset' );

$t_redirect_url = 'print_all_bug_options_page.php';

\Core\HTML::page_top( null, $t_redirect_url );


if( $t_result ) {
	\Core\HTML::operation_successful( $t_redirect_url );
} else {
	echo '<div class="failure-msg">';
	echo \Core\Error::string( ERROR_GENERIC ) . '<br />';
	\Core\Print_Util::bracket_link( $t_redirect_url, \Core\Lang::get( 'proceed' ) );
	echo '</div>';
}

\Core\HTML::page_bottom();
