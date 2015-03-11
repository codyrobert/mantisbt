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
 * CALLERS
 *	This page is called from:
 *	- account_page.php
 *
 * EXPECTED BEHAVIOUR
 *	- Delete the currently logged in user account
 *	- Logout the current user
 *	- Redirect to the page specified in the logout_redirect_page config option
 *
 * CALLS
 *	This page conditionally redirects upon completion
 *
 * RESTRICTIONS & PERMISSIONS
 *	- User must be authenticated
 *	- allow_account_delete config option must be enabled
 * @todo review form security tokens for this page
 * @todo should page_top1 be before meta redirect?
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
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'account_delete' );

\Core\Auth::ensure_user_authenticated();

\Core\Current_User::ensure_unprotected();

# Only allow users to delete their own accounts if allow_account_delete = ON or
# the user has permission to manage user accounts.
if( OFF == \Core\Config::mantis_get( 'allow_account_delete' ) &&
	 !\Core\Access::has_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) ) ) {
	\Core\Print_Util::header_redirect( 'account_page.php' );
}

# check that we are not deleting the last administrator account
$t_admin_threshold = \Core\Config::get_global( 'admin_site_threshold' );
if( \Core\Current_User::is_administrator() &&
	 \Core\User::count_level( $t_admin_threshold ) <= 1 ) {
	trigger_error( ERROR_USER_CHANGE_LAST_ADMIN, ERROR );
}

\Core\Helper::ensure_confirmed( \Core\Lang::get( 'confirm_delete_msg' ),
						 \Core\Lang::get( 'delete_account_button' ) );

\Core\Form::security_purge( 'account_delete' );

$t_user_id = \Core\Auth::get_current_user_id();

\Core\Auth::logout();

\Core\User::delete( $t_user_id );

\Core\HTML::page_top1();
\Core\HTML::page_top2a();

?>

<br />
<div>
<?php
echo \Core\Lang::get( 'account_removed_msg' ) . '<br />';
\Core\Print_Util::bracket_link( \Core\Config::mantis_get( 'logout_redirect_page' ), \Core\Lang::get( 'proceed' ) );
?>
</div>

<?php
	\Core\HTML::page_bottom1a();
