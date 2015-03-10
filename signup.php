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
 * Sign Up
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

\Flickerbox\Form::security_validate( 'signup' );

$f_username		= strip_tags( \Flickerbox\GPC::get_string( 'username' ) );
$f_email		= strip_tags( \Flickerbox\GPC::get_string( 'email' ) );
$f_captcha		= \Flickerbox\GPC::get_string( 'captcha', '' );

$f_username = trim( $f_username );
$f_email = trim( $f_email );
$f_captcha = utf8_strtolower( trim( $f_captcha ) );

# force logout on the current user if already authenticated
if( auth_is_user_authenticated() ) {
	auth_logout();
}

# Check to see if signup is allowed
if( OFF == \Flickerbox\Config::get_global( 'allow_signup' ) ) {
	\Flickerbox\Print_Util::header_redirect( 'login_page.php' );
	exit;
}

if( ON == \Flickerbox\Config::mantis_get( 'signup_use_captcha' ) && \Flickerbox\Utility::get_gd_version() > 0 	&&
			\Flickerbox\Helper::call_custom_function( 'auth_can_change_password', array() ) ) {
	# captcha image requires GD library and related option to ON
	require_lib( 'securimage/securimage.php' );

	$t_securimage = new Securimage();
	if( $t_securimage->check( $f_captcha ) == false ) {
		trigger_error( ERROR_SIGNUP_NOT_MATCHING_CAPTCHA, ERROR );
	}
}

\Flickerbox\Email::ensure_not_disposable( $f_email );

# notify the selected group a new user has signed-up
if( \Flickerbox\User::signup( $f_username, $f_email ) ) {
	\Flickerbox\Email::notify_new_account( $f_username, $f_email );
}

\Flickerbox\Form::security_purge( 'signup' );

\Flickerbox\HTML::page_top1();
\Flickerbox\HTML::page_top2a();
?>

<br />

<div id="error-msg">
	<div class="center">
		<strong><?php echo \Flickerbox\Lang::get( 'signup_done_title' ) ?></strong><br/>
		<?php echo '[' . $f_username . ' - ' . $f_email . '] ' ?>
	</div>

	<div>
		<br />
		<?php echo \Flickerbox\Lang::get( 'password_emailed_msg' ) ?>
		<br /><br />
		<?php echo \Flickerbox\Lang::get( 'no_reponse_msg' ) ?>
		<br /><br/>
	</div>
</div>

<br />
<div class="center">
	<?php \Flickerbox\Print_Util::bracket_link( 'login_page.php', \Flickerbox\Lang::get( 'proceed' ) ); ?>
</div>

<?php
\Flickerbox\HTML::page_bottom1a( __FILE__ );
