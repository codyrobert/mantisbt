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
 * User Create Page
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
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'print_api.php' );

auth_reauthenticate();

\Flickerbox\Access::ensure_global_level( config_get( 'manage_user_threshold' ) );

$t_ldap = ( LDAP == config_get( 'login_method' ) );

\Flickerbox\HTML::page_top();

\Flickerbox\HTML::print_manage_menu( 'manage_user_create_page.php' );
?>
<div id="manage-user-create-div" class="form-container">
	<form id="manage-user-create-form" method="post" action="manage_user_create.php">
		<fieldset>
			<legend>
				<span><?php echo \Flickerbox\Lang::get( 'create_new_account_title' ) ?></span>
			</legend>
			<?php echo \Flickerbox\Form::security_field( 'manage_user_create' ) ?>
			<div class="field-container">
				<label for="user-username"><span><?php echo \Flickerbox\Lang::get( 'username' ) ?></span></label>
				<span class="input"><input type="text" id="user-username" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" /></span>
				<span class="label-style"></span>
			</div><?php
			if( !$t_ldap || config_get( 'use_ldap_realname' ) == OFF ) { ?>
			<div class="field-container">
				<label for="user-realname"><span><?php echo \Flickerbox\Lang::get( 'realname' ) ?></span></label>
				<span class="input"><input type="text" id="user-realname" name="realname" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" /></span>
				<span class="label-style"></span>
			</div><?php
			}
			if( !$t_ldap || config_get( 'use_ldap_email' ) == OFF ) { ?>
			<div class="field-container">
				<label for="email-field"><span><?php echo \Flickerbox\Lang::get( 'email' ) ?></span></label>
				<span class="input"><?php print_email_input( 'email', '' ) ?></span>
				<span class="label-style"></span>
			</div><?php
			}

			if( OFF == config_get( 'send_reset_password' ) ) { ?>
			<div class="field-container">
				<label for="user-password"><span><?php echo \Flickerbox\Lang::get( 'password' ) ?></span></label>
				<span class="input"><input type="password" id="user-password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="user-verify-password"><span><?php echo \Flickerbox\Lang::get( 'verify_password' ) ?></span></label>
				<span class="input"><input type="password" id="user-verify-password" name="password_verify" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" /></span>
				<span class="label-style"></span>
			</div><?php
			} ?>
			<div class="field-container">
				<label for="user-access-level"><span><?php echo \Flickerbox\Lang::get( 'access_level' ) ?></span></label>
				<span class="select">
					<select id="user-access-level" name="access_level">
						<?php print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="user-enabled"><span><?php echo \Flickerbox\Lang::get( 'enabled' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="user-enabled" name="enabled" checked="checked" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="user-protected"><span><?php echo \Flickerbox\Lang::get( 'protected' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="user-protected" name="protected" /></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'create_user_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
\Flickerbox\HTML::page_bottom();
