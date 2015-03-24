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
 * This file sticks or unsticks a bug to the top of the view page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 */



\Core\Form::security_validate( 'bug_stick' );

$f_bug_id = \Core\GPC::get_int( 'bug_id' );
$t_bug = \Core\Bug::get( $f_bug_id, true );
$f_action = \Core\GPC::get_string( 'action' );

if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'set_bug_sticky_threshold' ), $f_bug_id );

\Core\Bug::set_field( $f_bug_id, 'sticky', 'stick' == $f_action );

\Core\Form::security_purge( 'bug_stick' );

\Core\Print_Util::successful_redirect_to_bug( $f_bug_id );
