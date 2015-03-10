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
 * Lost Password Requests
 *
 * @package MantisBT
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'lost_pwd' );

# lost password feature disabled or reset password via email disabled -> stop here!
if( OFF == \Core\Config::mantis_get( 'lost_password_feature' ) ||
	OFF == \Core\Config::mantis_get( 'send_reset_password' ) ||
	OFF == \Core\Config::mantis_get( 'enable_email_notification' ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
}

# force logout on the current user if already authenticated
if( auth_is_user_authenticated() ) {
	auth_logout();
}

$f_username = \Core\GPC::get_string( 'username' );
$f_email = \Core\GPC::get_string( 'email' );

\Core\Email::ensure_valid( $f_email );

# @todo Consider moving this query to user_api.php
$t_query = 'SELECT id FROM {user} WHERE username = ' . \Core\Database::param() . ' AND email = ' . \Core\Database::param() . ' AND enabled=' . \Core\Database::param();
$t_result = \Core\Database::query( $t_query, array( $f_username, $f_email, true ) );
$t_row = \Core\Database::fetch_array( $t_result );

if( !$t_row ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_MATCHING_DATA, ERROR );
}

if( \Core\Utility::is_blank( $f_email ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED, ERROR );
}

$t_user_id = $t_row['id'];

if( \Core\User::is_protected( $t_user_id ) ) {
	trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
}

if( !\Core\User::is_lost_password_request_allowed( $t_user_id ) ) {
	trigger_error( ERROR_LOST_PASSWORD_MAX_IN_PROGRESS_ATTEMPTS_REACHED, ERROR );
}

$t_confirm_hash = auth_generate_confirm_hash( $t_user_id );
\Core\Email::send_confirm_hash_url( $t_user_id, $t_confirm_hash );

\Core\User::increment_lost_password_in_progress_count( $t_user_id );

\Core\Form::security_purge( 'lost_pwd' );

$t_redirect_url = 'login_page.php';

\Core\HTML::page_top();
?>

<br />
<div>
<table class="width50" cellspacing="1">
<tr>
	<td class="center">
		<strong><?php echo \Core\Lang::get( 'lost_password_done_title' ) ?></strong>
	</td>
</tr>
<tr>
	<td>
		<br/>
		<?php echo \Core\Lang::get( 'reset_request_in_progress_msg' ) ?>
		<br/><br/>
	</td>
</tr>
</table>
<br />
<?php \Core\Print_Util::bracket_link( 'login_page.php', \Core\Lang::get( 'proceed' ) ); ?>
</div>

<?php
\Core\HTML::page_bottom1a( __FILE__ );
