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
 * This page allows an authorized user to send a reminder by email to another user
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
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

$f_bug_id = \Core\GPC::get_int( 'bug_id' );

$t_bug = \Core\Bug::get( $f_bug_id, true );
if( $t_bug->project_id != \Core\Helper::get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( \Core\Bug::is_readonly( $f_bug_id ) ) {
	\Core\Error::parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'bug_reminder_threshold' ), $f_bug_id );

\Core\HTML::page_top( \Core\Bug::format_summary( $f_bug_id, SUMMARY_CAPTION ) );
?>

<?php # Send reminder Form BEGIN ?>
<br />
<form method="post" action="bug_reminder.php">
<?php echo \Core\Form::security_field( 'bug_reminder' ) ?>
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
<div class="width75 form-container">
<table cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo \Core\Lang::get( 'bug_reminder' ) ?>
	</td>
</tr>
<tr>
	<th class="category">
		<?php echo \Core\Lang::get( 'to' ) ?>
	</th>
	<td>
		<select name="to[]" multiple="multiple" size="12" class="width20">
			<?php
			$t_project_id = \Core\Bug::get_field( $f_bug_id, 'project_id' );
			$t_access_level = \Core\Config::mantis_get( 'reminder_receive_threshold' );
			if( $t_bug->view_state === VS_PRIVATE ) {
				$t_private_bug_threshold = \Core\Config::mantis_get( 'private_bug_threshold' );
				if( $t_private_bug_threshold > $t_access_level ) {
					$t_access_level = $t_private_bug_threshold;
				}
			}
			$t_selected_user_id = 0;
			\Core\Print_Util::user_option_list( $t_selected_user_id, $t_project_id, $t_access_level );
			?>
		</select>
	</td>
</tr>
<tr>
	<th class="category">
		<?php echo \Core\Lang::get( 'reminder' ) ?>
	</th>
	<td>
		<textarea name="body" cols="85" rows="10" class="width100"></textarea>
	</td>
</tr>
</table>
<div class="center">
	<input type="submit" class="button" value="<?php echo \Core\Lang::get( 'bug_send_button' ) ?>" />
</div>
</div>
</form>
<br/>
<table class="width75" cellspacing="1">
<tr>
	<td>
		<?php
			echo \Core\Lang::get( 'reminder_explain' ) . ' ';
			if( ON == \Core\Config::mantis_get( 'reminder_recipients_monitor_bug' ) ) {
				echo \Core\Lang::get( 'reminder_monitor' ) . ' ';
			}
			if( ON == \Core\Config::mantis_get( 'store_reminders' ) ) {
				echo \Core\Lang::get( 'reminder_store' );
			}
		?>
	</td>
</tr>
</table>

<br />
<?php
$_GET['id'] = $f_bug_id;
$t_fields_config_option = 'bug_view_page_fields';
$t_show_page_header = false;
$t_force_readonly = true;
$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$t_file = __FILE__;

define( 'BUG_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bug_view_inc.php' );
