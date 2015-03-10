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
 * To delete a relationship we need to ensure that:
 * - User not anomymous
 * - Source bug exists and is not in read-only state (peer bug could not exist...)
 * - User that update the source bug and at least view the destination bug
 * - Relationship must exist
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'bug_relationship_delete' );

$f_rel_id = \Core\GPC::get_int( 'rel_id' );
$f_bug_id = \Core\GPC::get_int( 'bug_id' );

$t_bug = \Core\Bug::get( $f_bug_id, true );
if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# user has access to update the bug...
\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'update_bug_threshold' ), $f_bug_id );

# bug is not read-only...
if( \Core\Bug::is_readonly( $f_bug_id ) ) {
	\Core\Error::parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

# retrieve the destination bug of the relationship
$t_dest_bug_id = \Core\Relationship::get_linked_bug_id( $f_rel_id, $f_bug_id );

$t_dest_bug = \Core\Bug::get( $t_dest_bug_id, true );

# user can access to the related bug at least as viewer, if it's exist...
if( \Core\Bug::exists( $t_dest_bug_id ) ) {
	if( !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'view_bug_threshold', null, null, $t_dest_bug->project_id ), $t_dest_bug_id ) ) {
		\Core\Error::parameters( $t_dest_bug_id );
		trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
	}
}

\Core\Helper::ensure_confirmed( \Core\Lang::get( 'delete_relationship_sure_msg' ), \Core\Lang::get( 'delete_relationship_button' ) );

$t_bug_relationship_data = \Core\Relationship::get( $f_rel_id );
$t_rel_type = $t_bug_relationship_data->type;

# delete relationship from the DB
\Core\Relationship::delete( $f_rel_id );

# update bug last updated (just for the src bug)
\Core\Bug::update_date( $f_bug_id );

# set the rel_type for both bug and dest_bug based on $t_rel_type and on who is the dest bug
if( $f_bug_id == $t_bug_relationship_data->src_bug_id ) {
	$t_bug_rel_type = $t_rel_type;
	$t_dest_bug_rel_type = \Core\Relationship::get_complementary_type( $t_rel_type );
} else {
	$t_bug_rel_type = \Core\Relationship::get_complementary_type( $t_rel_type );
	$t_dest_bug_rel_type = $t_rel_type;
}

# send email and update the history for the src issue
\Core\History::log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, $t_bug_rel_type, $t_dest_bug_id );
\Core\Email::relationship_deleted( $f_bug_id, $t_dest_bug_id, $t_bug_rel_type );

if( \Core\Bug::exists( $t_dest_bug_id ) ) {
	# send email and update the history for the dest issue
	\Core\History::log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, $t_dest_bug_rel_type, $f_bug_id );
	\Core\Email::relationship_deleted( $t_dest_bug_id, $f_bug_id, $t_dest_bug_rel_type );
}

\Core\Form::security_purge( 'bug_relationship_delete' );

\Core\Print_Util::header_redirect_view( $f_bug_id );
