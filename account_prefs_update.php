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
 * Updates prefs then redirect to account_prefs_page.php
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
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'event_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );

\Flickerbox\Form::security_validate( 'account_prefs_update' );

\Flickerbox\Auth::ensure_user_authenticated();

$f_user_id					= \Flickerbox\GPC::get_int( 'user_id' );
$f_redirect_url				= \Flickerbox\GPC::get_string( 'redirect_url' );

user_ensure_exists( $f_user_id );

$t_user = user_get_row( $f_user_id );

# This page is currently called from the manage_* namespace and thus we
# have to allow authorised users to update the accounts of other users.
# TODO: split this functionality into manage_user_prefs_update.php
if( auth_get_current_user_id() != $f_user_id ) {
	\Flickerbox\Access::ensure_global_level( config_get( 'manage_user_threshold' ) );
	\Flickerbox\Access::ensure_global_level( $t_user['access_level'] );
} else {
	# Protected users should not be able to update the preferences of their
	# user account. The anonymous user is always considered a protected
	# user and hence will also not be allowed to update preferences.
	user_ensure_unprotected( $f_user_id );
}

$t_prefs = user_pref_get( $f_user_id );

$t_prefs->redirect_delay	= \Flickerbox\GPC::get_int( 'redirect_delay' );
$t_prefs->refresh_delay		= \Flickerbox\GPC::get_int( 'refresh_delay' );
$t_prefs->default_project	= \Flickerbox\GPC::get_int( 'default_project' );

$t_lang = \Flickerbox\GPC::get_string( 'language' );
if( \Flickerbox\Lang::language_exists( $t_lang ) ) {
	$t_prefs->language = $t_lang;
}

$t_prefs->email_on_new		= \Flickerbox\GPC::get_bool( 'email_on_new' );
$t_prefs->email_on_assigned	= \Flickerbox\GPC::get_bool( 'email_on_assigned' );
$t_prefs->email_on_feedback	= \Flickerbox\GPC::get_bool( 'email_on_feedback' );
$t_prefs->email_on_resolved	= \Flickerbox\GPC::get_bool( 'email_on_resolved' );
$t_prefs->email_on_closed	= \Flickerbox\GPC::get_bool( 'email_on_closed' );
$t_prefs->email_on_reopened	= \Flickerbox\GPC::get_bool( 'email_on_reopened' );
$t_prefs->email_on_bugnote	= \Flickerbox\GPC::get_bool( 'email_on_bugnote' );
$t_prefs->email_on_status	= \Flickerbox\GPC::get_bool( 'email_on_status' );
$t_prefs->email_on_priority	= \Flickerbox\GPC::get_bool( 'email_on_priority' );
$t_prefs->email_on_new_min_severity			= \Flickerbox\GPC::get_int( 'email_on_new_min_severity' );
$t_prefs->email_on_assigned_min_severity	= \Flickerbox\GPC::get_int( 'email_on_assigned_min_severity' );
$t_prefs->email_on_feedback_min_severity	= \Flickerbox\GPC::get_int( 'email_on_feedback_min_severity' );
$t_prefs->email_on_resolved_min_severity	= \Flickerbox\GPC::get_int( 'email_on_resolved_min_severity' );
$t_prefs->email_on_closed_min_severity		= \Flickerbox\GPC::get_int( 'email_on_closed_min_severity' );
$t_prefs->email_on_reopened_min_severity	= \Flickerbox\GPC::get_int( 'email_on_reopened_min_severity' );
$t_prefs->email_on_bugnote_min_severity		= \Flickerbox\GPC::get_int( 'email_on_bugnote_min_severity' );
$t_prefs->email_on_status_min_severity		= \Flickerbox\GPC::get_int( 'email_on_status_min_severity' );
$t_prefs->email_on_priority_min_severity	= \Flickerbox\GPC::get_int( 'email_on_priority_min_severity' );

$t_prefs->bugnote_order = \Flickerbox\GPC::get_string( 'bugnote_order' );
$t_prefs->email_bugnote_limit = \Flickerbox\GPC::get_int( 'email_bugnote_limit' );

# make sure the delay isn't too low
if( ( config_get( 'min_refresh_delay' ) > $t_prefs->refresh_delay )&&
	( $t_prefs->refresh_delay != 0 )) {
	$t_prefs->refresh_delay = config_get( 'min_refresh_delay' );
}

$t_timezone = \Flickerbox\GPC::get_string( 'timezone' );
if( in_array( $t_timezone, timezone_identifiers_list() ) ) {
	if( $t_timezone == config_get_global( 'default_timezone' ) ) {
		$t_prefs->timezone = '';
	} else {
		$t_prefs->timezone = $t_timezone;
	}
}

event_signal( 'EVENT_ACCOUNT_PREF_UPDATE', array( $f_user_id ) );

user_pref_set( $f_user_id, $t_prefs );

\Flickerbox\Form::security_purge( 'account_prefs_update' );

\Flickerbox\HTML::page_top( null, $f_redirect_url );

\Flickerbox\HTML::operation_successful( $f_redirect_url );

\Flickerbox\HTML::page_bottom();
