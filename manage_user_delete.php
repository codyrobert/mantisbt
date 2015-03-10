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
 * User Delete
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_user_delete' );

auth_reauthenticate();
\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) );

$f_user_id	= \Core\GPC::get_int( 'user_id' );

$t_user = \Core\User::get_row( $f_user_id );

# Ensure that the account to be deleted is of equal or lower access to the
# current user.
\Core\Access::ensure_global_level( $t_user['access_level'] );

# check that we are not deleting the last administrator account
$t_admin_threshold = \Core\Config::get_global( 'admin_site_threshold' );
if( \Core\User::is_administrator( $f_user_id ) &&
	 \Core\User::count_level( $t_admin_threshold ) <= 1 ) {
	trigger_error( ERROR_USER_CHANGE_LAST_ADMIN, ERROR );
}

# If an administrator is trying to delete their own account, use
# account_delete.php instead as it is handles logging out and redirection
# of users who have just deleted their own accounts.
if( auth_get_current_user_id() == $f_user_id ) {
	\Core\Form::security_purge( 'manage_user_delete' );
	\Core\Print_Util::header_redirect( 'account_delete.php?account_delete_token=' . \Core\Form::security_token( 'account_delete' ), true, false );
}

\Core\Helper::ensure_confirmed( \Core\Lang::get( 'delete_account_sure_msg' ) .
	'<br/>' . \Core\Lang::get( 'username_label' ) . \Core\Lang::get( 'word_separator' ) . $t_user['username'],
	\Core\Lang::get( 'delete_account_button' ) );

\Core\User::delete( $f_user_id );

\Core\Form::security_purge( 'manage_user_delete' );

\Core\HTML::page_top( null, 'manage_user_page.php' );

\Core\HTML::operation_successful( 'manage_user_page.php' );

\Core\HTML::page_bottom();
