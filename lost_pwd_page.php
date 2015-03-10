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
 * Lost Password Functionality
 *
 * @package MantisBT
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
\Flickerbox\HTML::require_css( 'login.css' );

# lost password feature disabled or reset password via email disabled -> stop here!
if( LDAP == \Flickerbox\Config::get_global( 'login_method' ) ||
	OFF == \Flickerbox\Config::mantis_get( 'lost_password_feature' ) ||
	OFF == \Flickerbox\Config::mantis_get( 'send_reset_password' )  ||
	OFF == \Flickerbox\Config::mantis_get( 'enable_email_notification' ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
}

# don't index lost password page
\Flickerbox\HTML::robots_noindex();

\Flickerbox\HTML::page_top1();
\Flickerbox\HTML::page_top2a();
?>
<div id="lost-password-div" class="form-container">
	<form id="lost-password-form" method="post" action="lost_pwd.php">
		<fieldset>
			<legend><span><?php echo \Flickerbox\Lang::get( 'lost_password_title' ); ?></span></legend>
			 <ul id="login-links">
				<li><a href="login_page.php"><?php echo \Flickerbox\Lang::get( 'login_link' ); ?></a></li>
				<li><a href="signup_page.php"><?php echo \Flickerbox\Lang::get( 'signup_link' ); ?></a></li>
            </ul>
			<?php
			echo \Flickerbox\Form::security_field( 'lost_pwd' );

			$t_allow_passwd = \Flickerbox\Helper::call_custom_function( 'auth_can_change_password', array() );
			if( $t_allow_passwd ) { ?>
			<div class="field-container">
				<label for="username"><span><?php echo \Flickerbox\Lang::get( 'username' ) ?></span></label>
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" class="autofocus" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="email-field"><span><?php echo \Flickerbox\Lang::get( 'email' ) ?></span></label>
				<span class="input"><?php \Flickerbox\Print_Util::email_input( 'email', '' ) ?></span>
				<span class="label-style"></span>
			</div>
			<span id="lost-password-msg"><?php echo \Flickerbox\Lang::get( 'lost_password_info' ); ?></span>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'submit_button' ) ?>" /></span><?php
			} else {
				echo '<span id="no-password-msg">';
				echo \Flickerbox\Lang::get( 'no_password_request' );
				echo '</span>';
			} ?>
		</fieldset>
	</form>
</div><?php

\Flickerbox\HTML::page_bottom1a( __FILE__ );
