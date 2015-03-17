<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * View Bug Revisions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses bug_revision_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

$f_bug_id = \Core\GPC::get_int( 'bug_id', 0 );
$f_bugnote_id = \Core\GPC::get_int( 'bugnote_id', 0 );
$f_rev_id = \Core\GPC::get_int( 'rev_id', 0 );

$t_title = '';

if( $f_bug_id ) {
	$t_bug_id = $f_bug_id;
	$t_bug_data = \Core\Bug::get( $t_bug_id, true );
	$t_bug_revisions = array_reverse( \Core\Bug\Revision::list_changes( $t_bug_id ), true );

	$t_title = \Core\Lang::get( 'issue_id' ) . $t_bug_id;

} else if( $f_bugnote_id ) {
	$t_bug_id = \Core\Bug\Note::get_field( $f_bugnote_id, 'bug_id' );
	$t_bug_data = \Core\Bug::get( $t_bug_id, true );

	$t_bug_revisions = \Core\Bug\Revision::list_changes( $t_bug_id, REV_ANY, $f_bugnote_id );

	$t_title = \Core\Lang::get( 'bugnote' ) . ' ' . $f_bugnote_id;

} else if( $f_rev_id ) {
	$t_bug_revisions = \Core\Bug\Revision::like( $f_rev_id );

	if( count( $t_bug_revisions ) < 1 ) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_bug_id = $t_bug_revisions[$f_rev_id]['bug_id'];
	$t_bug_data = \Core\Bug::get( $t_bug_id, true );

	$t_title = \Core\Lang::get( 'issue_id' ) . $t_bug_id;

} else {
	trigger_error( ERROR_GENERIC, ERROR );
}

/**
 * Show Bug revision
 *
 * @param array $p_revision Bug Revision Data.
 * @return null
 */
function show_revision( array $p_revision ) {
	static $s_can_drop = null;
	static $s_drop_token = null;
	static $s_user_access = null;
	if( is_null( $s_can_drop ) ) {
		$s_can_drop = \Core\Access::has_bug_level( \Core\Config::mantis_get( 'bug_revision_drop_threshold' ), $p_revision['bug_id'] );
		$s_drop_token = \Core\Form::security_param( 'bug_revision_drop' );
	}

	switch( $p_revision['type'] ) {
		case REV_DESCRIPTION:
			$t_label = \Core\Lang::get( 'description' );
			break;
		case REV_STEPS_TO_REPRODUCE:
			$t_label = \Core\Lang::get( 'steps_to_reproduce' );
			break;
		case REV_ADDITIONAL_INFO:
			$t_label = \Core\Lang::get( 'additional_information' );
			break;
		case REV_BUGNOTE:
			if( is_null( $s_user_access ) ) {
				$s_user_access = \Core\Access::has_bug_level( \Core\Config::mantis_get( 'private_bugnote_threshold' ), $p_revision['bug_id'] );
			}

			if( !$s_user_access ) {
				return null;
			}

			$t_label = \Core\Lang::get( 'bugnote' );
			break;
		default:
			$t_label = '';
	}

	$t_by_string = sprintf( \Core\Lang::get( 'revision_by' ), \Core\String::display_line( date( \Core\Config::mantis_get( 'normal_date_format' ), $p_revision['timestamp'] ) ), \Core\Prepare::user_name( $p_revision['user_id'] ) );

?>

		<tr class="spacer"><td><a id="revision-<?php echo $p_revision['id'] ?>"></a></td></tr>

		<tr>
			<th class="category"><?php echo \Core\Lang::get( 'revision' ) ?></th>
			<td colspan="2"><?php echo $t_by_string ?></td>
			<td class="center" width="5%">
<?php
	if( $s_can_drop ) {
		\Core\Print_Util::bracket_link( 'bug_revision_drop.php?id=' . $p_revision['id'] . $s_drop_token, \Core\Lang::get( 'revision_drop' ) );
	}
?>
		</tr>

		<tr>
			<th class="category"><?php echo $t_label ?></th>
			<td colspan="3"><?php echo \Core\String::display_links( $p_revision['value'] ) ?></td>
		</tr>
<?php
}

\Core\HTML::page_top( \Core\Bug::format_summary( $t_bug_id, SUMMARY_CAPTION ) );

\Core\Print_Util::recently_visited();

?>


<div id="bug-revision-div" class="table-container">
	<h2><?php echo \Core\Lang::get( 'view_revisions' ), ': ', $t_title ?></h2>
	<div class="section-link">
		<?php
			if( !$f_bug_id && !$f_bugnote_id ) {
				\Core\Print_Util::bracket_link( '?bug_id=' . $t_bug_id, \Core\Lang::get( 'all_revisions' ) );
			}
			\Core\Print_Util::bracket_link( 'view.php?id=' . $t_bug_id, \Core\Lang::get( 'back_to_issue' ) );
		?>
	</div>

	<table>
		<tr>
			<th class="category" width="15%"><?php echo \Core\Lang::get( 'summary' ) ?></th>
			<td colspan="3"><?php echo \Core\Bug::format_summary( $t_bug_id, SUMMARY_FIELD ) ?></td>
		</tr>
		<?php
			foreach( $t_bug_revisions as $t_rev ) {
				show_revision( $t_rev );
			}
		?>
	</table>
</div>

<?php
\Core\HTML::page_bottom();

