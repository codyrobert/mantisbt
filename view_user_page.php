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
 * View User Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */



\Core\Auth::ensure_user_authenticated();

# extracts the user information for the currently logged in user
# and prefixes it with u_
$f_user_id = \Core\GPC::get_int( 'id', \Core\Auth::get_current_user_id() );
$t_row = \Core\User::get_row( $f_user_id );

extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_can_manage = \Core\Access::has_global_level( \Core\Config::mantis_get( 'manage_user_threshold' ) ) &&
	\Core\Access::has_global_level( $u_access_level );
$t_can_see_realname = \Core\Access::has_project_level( \Core\Config::mantis_get( 'show_user_realname_threshold' ) );
$t_can_see_email = \Core\Access::has_project_level( \Core\Config::mantis_get( 'show_user_email_threshold' ) );

# In case we're using LDAP to get the email address... this will pull out
#  that version instead of the one in the DB
$u_email = \Core\User::get_email( $u_id );
$u_realname = \Core\User::get_realname( $u_id );

\Core\HTML::page_top();
?>

<div class="section-container">
	<h2><?php echo \Core\Lang::get( 'view_account_title' ) ?></h2>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Core\Lang::get( 'username' ) ?></span></span>
		<span class="display-value"><span><?php echo \Core\String::display_line( $u_username ) ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Core\Lang::get( 'email' ) ?></span></span>
		<span class="display-value"><span>
			<?php
				if( ! ( $t_can_manage || $t_can_see_email ) ) {
					print \Core\Error::string( ERROR_ACCESS_DENIED );
				} else {
					if( !\Core\Utility::is_blank( $u_email ) ) {
						\Core\Print_Util::email_link( $u_email, $u_email );
					} else {
						echo ' - ';
					}
				} ?>
		</span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo \Core\Lang::get( 'realname' ) ?></span></span>
		<span class="display-value"><span><?php
			if( ! ( $t_can_manage || $t_can_see_realname ) ) {
				print \Core\Error::string( ERROR_ACCESS_DENIED );
			} else {
				echo \Core\String::display_line( $u_realname );
			} ?>
		</span></span>
		<span class="label-style"></span>
	</div>
	<span class="section-links">
	<?php if( $t_can_manage ) { ?>
			<span id="manage-user-link"><a href="<?php echo \Core\String::html_specialchars( 'manage_user_edit_page.php?user_id=' . $f_user_id ); ?>"><?php echo \Core\Lang::get( 'manage_user' ); ?></a></span>
	<?php } ?>
	</span>
</div><?php

\Core\HTML::page_bottom();
