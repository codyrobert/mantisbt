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
 * This page allows the user to edit his/her profile
 * Changes get POSTed to account_prof_update.php
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
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses profile_api.php
 * @uses string_api.php
 */



if( !\Core\Config::mantis_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

\Core\Auth::ensure_user_authenticated();

\Core\Current_User::ensure_unprotected();

$f_profile_id	= \Core\GPC::get_int( 'profile_id' );

if( \Core\Profile::is_global( $f_profile_id ) ) {
	\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'manage_global_profile_threshold' ) );

	$t_row = \Core\Profile::get_row( ALL_USERS, $f_profile_id );
} else {
	$t_row = \Core\Profile::get_row( \Core\Auth::get_current_user_id(), $f_profile_id );
}

extract( $t_row, EXTR_PREFIX_ALL, 'v' );

\Core\HTML::page_top();

if( \Core\Profile::is_global( $f_profile_id ) ) {
	\Core\HTML::print_manage_menu();
}
?>

<?php # Edit Profile Form BEGIN ?>
<br />
<div>
<form method="post" action="account_prof_update.php">
<?php  echo \Core\Form::security_field( 'profile_update' )?>
<input type="hidden" name="action" value="update" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="profile_id" value="<?php echo $v_id ?>" />
		<?php echo \Core\Lang::get( 'edit_profile_title' ) ?>
	</td>
	<td class="right">
		<?php
			if( !\Core\Profile::is_global( $f_profile_id ) ) {
				\Core\HTML::print_account_menu();
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<th class="category" width="25%">
		<span class="required">*</span><?php echo \Core\Lang::get( 'platform' ) ?>
	</th>
	<td width="75%">
		<input type="text" name="platform" size="32" maxlength="32" value="<?php echo \Core\String::attribute( $v_platform ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<th class="category">
		<span class="required">*</span><?php echo \Core\Lang::get( 'os' ) ?>
	</th>
	<td>
		<input type="text" name="os" size="32" maxlength="32" value="<?php echo \Core\String::attribute( $v_os ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<th class="category">
		<span class="required">*</span><?php echo \Core\Lang::get( 'os_version' ) ?>
	</th>
	<td>
		<input type="text" name="os_build" size="16" maxlength="16" value="<?php echo \Core\String::attribute( $v_os_build ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<th class="category">
		<?php echo \Core\Lang::get( 'additional_description' ) ?>
	</th>
	<td>
		<textarea name="description" cols="60" rows="8"><?php echo \Core\String::textarea( $v_description ) ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo \Core\Lang::get( 'update_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php
\Core\HTML::page_bottom();
