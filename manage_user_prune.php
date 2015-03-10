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
 * Prune old/unused users from database
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
 * @uses database_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

\Flickerbox\Form::security_validate( 'manage_user_prune' );

auth_reauthenticate();

\Flickerbox\Access::ensure_global_level( \Flickerbox\Config::mantis_get( 'manage_user_threshold' ) );


# Delete the users who have never logged in and are older than 1 week
$t_days_old = (int)7 * SECONDS_PER_DAY;

$t_query = 'SELECT id, access_level FROM {user}
		WHERE ( login_count = 0 ) AND ( date_created = last_visit ) AND ' . \Flickerbox\Database::helper_compare_time( \Flickerbox\Database::param(), '>', 'date_created', $t_days_old );
$t_result = \Flickerbox\Database::query( $t_query, array( \Flickerbox\Database::now() ) );

if( !$t_result ) {
	trigger_error( ERROR_GENERIC, ERROR );
}

$t_count = \Flickerbox\Database::num_rows( $t_result );

if( $t_count > 0 ) {
	\Flickerbox\Helper::ensure_confirmed( \Flickerbox\Lang::get( 'confirm_account_pruning' ),
							 \Flickerbox\Lang::get( 'prune_accounts_button' ) );
}

for( $i=0; $i < $t_count; $i++ ) {
	$t_row = \Flickerbox\Database::fetch_array( $t_result );
	# Don't prune accounts with a higher global access level than the current user
	if( \Flickerbox\Access::has_global_level( $t_row['access_level'] ) ) {
		\Flickerbox\User::delete( $t_row['id'] );
	}
}

\Flickerbox\Form::security_purge( 'manage_user_prune' );

\Flickerbox\Print_Util::header_redirect( 'manage_user_page.php' );
