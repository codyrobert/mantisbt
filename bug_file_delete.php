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
 * Delete a file from a bug and then view the bug
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
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */



\Core\Form::security_validate( 'bug_file_delete' );

$f_file_id = \Core\GPC::get_int( 'file_id' );

$t_bug_id = \Core\File::get_field( $f_file_id, 'bug_id' );

$t_bug = \Core\Bug::get( $t_bug_id, true );
if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

$t_attachment_owner = \Core\File::get_field( $f_file_id, 'user_id' );
$t_current_user_is_attachment_owner = $t_attachment_owner == \Core\Auth::get_current_user_id();
if( !$t_current_user_is_attachment_owner || ( $t_current_user_is_attachment_owner && !\Core\Config::mantis_get( 'allow_delete_own_attachments' ) ) ) {
	\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'delete_attachments_threshold' ), $t_bug_id );
}

\Core\Helper::ensure_confirmed( \Core\Lang::get( 'delete_attachment_sure_msg' ), \Core\Lang::get( 'delete_attachment_button' ) );

\Core\File::delete( $f_file_id, 'bug' );

\Core\Form::security_purge( 'bug_file_delete' );

\Core\Print_Util::header_redirect_view( $t_bug_id );
