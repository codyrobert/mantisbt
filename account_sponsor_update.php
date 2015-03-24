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
 * This page updates a user's sponsorships
 * If an account is protected then changes are forbidden
 * The page gets redirected back to account_page.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses sponsorship_api.php
 */



if( !\Core\Config::mantis_get( 'enable_sponsorship' ) ) {
	trigger_error( ERROR_SPONSORSHIP_NOT_ENABLED, ERROR );
}

\Core\Form::security_validate( 'account_sponsor_update' );

\Core\Auth::ensure_user_authenticated();

$f_bug_list = \Core\GPC::get_string( 'buglist', '' );
$t_bug_list = explode( ',', $f_bug_list );

foreach( $t_bug_list as $t_bug ) {
	list( $t_bug_id, $t_sponsor_id ) = explode( ':', $t_bug );
	$c_bug_id = (int)$t_bug_id;

	\Core\Bug::ensure_exists( $c_bug_id ); # dies if bug doesn't exist

	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'handle_sponsored_bugs_threshold' ), $c_bug_id ); # dies if user can't handle bug

	$t_bug = \Core\Bug::get( $c_bug_id );
	$t_sponsor = \Core\Sponsorship::get( (int)$t_sponsor_id );

	$t_new_payment = \Core\GPC::get_int( 'sponsor_' . $c_bug_id . '_' . $t_sponsor->id, $t_sponsor->paid );
	if( $t_new_payment != $t_sponsor->paid ) {
		\Core\Sponsorship::update_paid( $t_sponsor_id, $t_new_payment );
	}
}

\Core\Form::security_purge( 'account_sponsor_update' );

$t_redirect_url = 'account_sponsor_page.php';
\Core\HTML::page_top( null, $t_redirect_url );

\Core\HTML::operation_successful( $t_redirect_url, \Core\Lang::get( 'payment_updated' ) );

\Core\HTML::page_bottom();
