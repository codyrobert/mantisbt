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
 * Sign Up Page
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */



\Core\HTML::require_css( 'login.css' );

\Core\HTML::require_js( 'login.js' );

# Check for invalid access to signup page
if( OFF == \Core\Config::get_global( 'allow_signup' ) || LDAP == \Core\Config::get_global( 'login_method' ) ) {
	\Core\Print_Util::header_redirect( 'login_page.php' );
}

# signup page shouldn't be indexed by search engines
\Core\HTML::robots_noindex();

\Core\HTML::page_top1();
\Core\HTML::page_top2a();

$t_public_key = \Core\Crypto::generate_uri_safe_nonce( 64 );
?>

<div id="signup-div" class="form-container">
	<form id="signup-form" method="post" action="signup.php">
		<fieldset>
			<legend><span><?php echo \Core\Lang::get( 'signup_title' ) ?></span></legend>
			<?php echo \Core\Form::security_field( 'signup' ); ?>

			<ul id="login-links">
				<li><a href="login_page.php"><?php echo \Core\Lang::get( 'login_link' ); ?></a></li>
<?php
			# lost password feature disabled or reset password via email disabled
			if( ( LDAP != \Core\Config::get_global( 'login_method' ) ) &&
				( ON == \Core\Config::mantis_get( 'lost_password_feature' ) ) &&
				( ON == \Core\Config::mantis_get( 'send_reset_password' ) ) &&
				( ON == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
?>
				<li><a href="lost_pwd_page.php"><?php echo \Core\Lang::get( 'lost_password_link' ); ?></a></li>
<?php
			}
?>
			</ul>

			<div class="field-container">
				<label for="username"><span><?php echo \Core\Lang::get( 'username' ) ?></span></label>
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" class="autofocus" /></span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label for="email-field"><span><?php echo \Core\Lang::get( 'email_label' ) ?></span></label>
				<span class="input"><?php \Core\Print_Util::email_input( 'email', '' ) ?></span>
				<span class="label-style"></span>
			</div>

<?php
			$t_allow_passwd_change = \Core\Helper::call_custom_function( 'auth_can_change_password', array() );
			# captcha image requires GD library and related option to ON
			if( ON == \Core\Config::mantis_get( 'signup_use_captcha' ) && \Core\Utility::get_gd_version() > 0 && $t_allow_passwd_change ) {
				$t_securimage_path = 'library/securimage';
				$t_securimage_show = $t_securimage_path . '/securimage_show.php';
				$t_securimage_play = $t_securimage_path . '/securimage_play.swf?'
					. http_build_query( array(
						'audio_file' => $t_securimage_path . '/securimage_play.php',
						'bgColor1=' => '#fff',
						'bgColor2=' => '#fff',
						'iconColor=' => '#777',
						'borderWidth=' => 1,
						'borderColor=' => '#000',
					) );
?>
			<div class="field-container">
				<label for="captcha-field"><span><?php
					echo \Core\Lang::get( 'signup_captcha_request_label' );
				?></span></label>
				<span id="captcha-input" class="input">
					<?php \Core\Print_Util::captcha_input( 'captcha' ); ?>

					<span id="captcha-image" class="captcha-image" style="padding-right:3px;">
						<img src="<?php echo $t_securimage_show; ?>" alt="visual captcha" />
						<ul id="captcha-refresh"><li><a href="#"><?php
							echo \Core\Lang::get( 'signup_captcha_refresh' );
						?></a></li></ul>
					</span>

					<object type="application/x-shockwave-flash" width="19" height="19"
						data="<?php echo $t_securimage_play; ?>">
						<param name="movie" value="<?php echo $t_securimage_play; ?>" />
					</object>
				</span>

				<span class="label-style"></span>
			</div>
<?php
			}
			if( !$t_allow_passwd_change ) {
				echo '<span id="no-password-msg">';
				echo \Core\Lang::get( 'no_password_request' );
				echo '</span>';
			}
?>

			<span id="signup-info"><?php echo \Core\Lang::get( 'signup_info' ); ?></span>

			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Core\Lang::get( 'signup_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php \Core\HTML::page_bottom1a( __FILE__ );
