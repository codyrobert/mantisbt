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
 * Check login then redirect to main_page.php or to login_page.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses session_api.php
 * @uses string_api.php
 */



$t_allow_perm_login = ( ON == \Core\Config::mantis_get( 'allow_permanent_cookie' ) );

$f_username		= \Core\GPC::get_string( 'username', '' );
$f_password		= \Core\GPC::get_string( 'password', '' );
$f_perm_login	= $t_allow_perm_login && \Core\GPC::get_bool( 'perm_login' );
$t_return		= \Core\String::url( \Core\String::sanitize_url( \Core\GPC::get_string( 'return', \Core\Config::mantis_get( 'default_home_page' ) ) ) );
$f_from			= \Core\GPC::get_string( 'from', '' );
$f_secure_session = \Core\GPC::get_bool( 'secure_session', false );
$f_install = \Core\GPC::get_bool( 'install' );

# If upgrade required, always redirect to install page.
if( $f_install ) {
	$t_return = 'admin/install.php';
}

$f_username = \Core\Auth::prepare_username( $f_username );
$f_password = \Core\Auth::prepare_password( $f_password );

\Core\GPC::set_cookie( \Core\Config::get_global( 'cookie_prefix' ) . '_secure_session', $f_secure_session ? '1' : '0' );

if( \Core\Auth::attempt_login( $f_username, $f_password, $f_perm_login ) ) {
	\Core\Session::set( 'secure_session', $f_secure_session );

	if( $f_username == 'administrator' && $f_password == 'root' && ( \Core\Utility::is_blank( $t_return ) || $t_return == 'index.php' ) ) {
		$t_return = 'account_page.php';
	}

	$t_redirect_url = 'login_cookie_test.php?return=' . $t_return;

} else {
	$t_redirect_url = 'login_page.php?return=' . $t_return .
		'&error=1&username=' . urlencode( $f_username ) .
		'&secure_session=' . ( $f_secure_session ? 1 : 0 );
	if( $t_allow_perm_login ) {
		$t_redirect_url .= '&perm_login=' . ( $f_perm_login ? 1 : 0 );
	}

	if( HTTP_AUTH == \Core\Config::mantis_get( 'login_method' ) ) {
		auth_http_prompt();
		exit;
	}
}

\Core\Print_Util::header_redirect( $t_redirect_url );
