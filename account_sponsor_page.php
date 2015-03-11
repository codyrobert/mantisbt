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
 * CALLERS
 *	This page is called from:
 *	- \Core\HTML::print_menu()
 *	- \Core\HTML::print_account_menu()
 *
 * EXPECTED BEHAVIOUR
 *	- Display the user's current sponsorships
 *	- Allow the user to edit the payment flag
 *
 * CALLS
 *	This page calls the following pages:
 *	- account_sponsor_update.php  (to save changes)
 *
 * RESTRICTIONS & PERMISSIONS
 *	- User must be authenticated, and not anonymous
 *   - sponsorship must be enabled
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
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses sponsorship_api.php
 * @uses string_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );

\Core\HTML::require_css( 'status_config.php' );

if( !\Core\Config::mantis_get( 'enable_sponsorship' ) ) {
	trigger_error( ERROR_SPONSORSHIP_NOT_ENABLED, ERROR );
}

# anonymous users are not allowed to sponsor issues
if( \Core\Current_User::is_anonymous() ) {
	\Core\Access::denied();
}

$t_show_all = \Core\GPC::get_bool( 'show_all', false );

# start the page
\Core\HTML::page_top( \Core\Lang::get( 'my_sponsorship' ) );

$t_project = \Core\Helper::get_current_project();
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo \Core\Lang::get( 'my_sponsorship' ) ?>
	</td>
	<td class="right">
		<?php \Core\HTML::print_account_menu( 'account_sponsor_page.php' ) ?>
	</td>
</tr>
</table>
<?php
# get issues user has sponsored
$t_user = \Core\Auth::get_current_user_id();
$t_resolved = \Core\Config::mantis_get( 'bug_resolved_status_threshold' );
$t_payment = \Core\Config::mantis_get( 'payment_enable', 0 );

$t_project_clause = \Core\Helper::project_specific_where( $t_project );

$t_query = 'SELECT b.id as bug, s.id as sponsor, s.paid, b.project_id, b.fixed_in_version, b.status
	FROM {bug} b, {sponsorship} s
	WHERE s.user_id=' . \Core\Database::param() . ' AND s.bug_id = b.id ' .
	( $t_show_all ? '' : 'AND ( b.status < ' . \Core\Database::param() . ' OR s.paid < ' . SPONSORSHIP_PAID . ')' ) . '
	AND ' . $t_project_clause . '
	ORDER BY s.paid ASC, b.project_id ASC, b.fixed_in_version ASC, b.status ASC, b.id DESC';

$t_result = \Core\Database::query( $t_query, $t_show_all ? array( $t_user ) : array( $t_user , $t_resolved ) );

$t_sponsors = array();
while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
	$t_sponsors[] = $t_row;
}

$t_sponsor_count = count( $t_sponsors );
if( $t_sponsor_count === 0 ) {
	echo '<p>' . \Core\Lang::get( 'no_own_sponsored' ) . '</p>';
} else {
?>

<!-- # Edit own sponsorship Form BEGIN -->
<br />
<div>
<table class="width100" cellspacing="1">
	<colgroup>
		<col style="width:10%" />
		<col style="width:8%" />
		<col style="width:7%" />
		<col style="width:10%" />
		<col style="width:10%" />
		<col style="width:30%" />
		<col style="width:8%" />
		<col style="width:7%" />
		<col style="width:10%" />
	</colgroup>
	<!-- Headings -->
	<tr>
		<td class="form-title" colspan="9">
			<?php echo \Core\Lang::get( 'own_sponsored' ) ?>
		</td>
	</tr>
	<tr>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_bug' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_project' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'fixed_in_version' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_status' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_handler' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_summary' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'amount' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'status' ) ?></td>
		<td class="form-title">&#160;</td>
	</tr>
<?php
	$t_total_owing = 0;
	$t_total_paid = 0;
	for( $i = 0; $i < $t_sponsor_count; ++$i ) {
		$t_sponsor_row = $t_sponsors[$i];
		$t_bug = \Core\Bug::get( $t_sponsor_row['bug'] );
		$t_sponsor = \Core\Sponsorship::get( $t_sponsor_row['sponsor'] );

		# describe bug
		$t_status = \Core\String::attribute( \Core\Helper::get_enum_element( 'status', $t_bug->status, \Core\Auth::get_current_user_id(), $t_bug->project_id ) );
		$t_resolution = \Core\String::attribute( \Core\Helper::get_enum_element( 'resolution', $t_bug->resolution, \Core\Auth::get_current_user_id(), $t_bug->project_id ) );
		$t_version_id = \Core\Version::get_id( $t_bug->fixed_in_version, $t_bug->project_id );
		if( ( false !== $t_version_id ) && ( VERSION_RELEASED == \Core\Version::get_field( $t_version_id, 'released' ) ) ) {
			$t_released_label = '<a title="' . \Core\Lang::get( 'released' ) . '">' . $t_bug->fixed_in_version . '</a>';
		} else {
			$t_released_label = $t_bug->fixed_in_version;
		}

		# choose color based on status
		$t_status_label = \Core\HTML::get_status_css_class( $t_bug->status, \Core\Auth::get_current_user_id(), $t_bug->project_id );

		echo '<tr class="' . $t_status_label .  '">';
		echo '<td><a href="' . \Core\String::get_bug_view_url( $t_sponsor_row['bug'] ) . '">' . \Core\Bug::format_id( $t_sponsor_row['bug'] ) . '</a></td>';
		echo '<td>' . \Core\String::display_line( \Core\Project::get_field( $t_bug->project_id, 'name' ) ) . '&#160;</td>';
		echo '<td class="right">' . $t_released_label . '&#160;</td>';
		echo '<td><span class="issue-status" title="' . $t_resolution . '">' . $t_status . '</span></td>';
		echo '<td>';
		\Core\Print_Util::user( $t_bug->handler_id );
		echo '</td>';

		# summary
		echo '<td>' . \Core\String::display_line( $t_bug->summary );
		if( VS_PRIVATE == $t_bug->view_state ) {
			printf( ' <img src="%s" alt="(%s)" title="%s" />', $t_icon_path . 'protected.gif', \Core\Lang::get( 'private' ), \Core\Lang::get( 'private' ) );
		}
		echo '</td>';

		# describe sponsorship amount
		echo '<td class="right">' . \Core\Sponsorship::format_amount( $t_sponsor->amount ) . '</td>';
		echo '<td>' . \Core\Helper::get_enum_element( 'sponsorship', $t_sponsor->paid ) . '</td>';

		if( SPONSORSHIP_PAID == $t_sponsor->paid ) {
			$t_total_paid += $t_sponsor->amount;
		} else {
			$t_total_owing += $t_sponsor->amount;
		}

		echo '<td>';
		if( $t_payment ) {
			echo '(paypal button)';
		} else {
			echo '&#160;';
		}
		echo '</td>';
		echo '</tr>';
	}
?>
<!-- Totals -->
<tr>
	<td colspan="5"></td>
	<td><?php echo \Core\Lang::get( 'total_owing' ) ?></td>
	<td class="right"><?php echo \Core\Sponsorship::format_amount( $t_total_owing ) ?></td>
	<td colspan="2"></td>
</tr>
<tr>
	<td colspan="5"></td>
	<td><?php echo \Core\Lang::get( 'total_paid' ) ?></td>
	<td class="right"><?php echo \Core\Sponsorship::format_amount( $t_total_paid ) ?></td>
	<td colspan="2"></td>
</tr>
</table>
</div>
<?php } # end sponsored issues

$t_query = 'SELECT b.id as bug, s.id as sponsor, s.paid, b.project_id, b.fixed_in_version, b.status
	FROM {bug} b, {sponsorship} s
	WHERE b.handler_id=' . \Core\Database::param() . ' AND s.bug_id = b.id ' .
	( $t_show_all ? '' : 'AND ( b.status < ' . \Core\Database::param() . ' OR s.paid < ' . SPONSORSHIP_PAID . ')' ) . '
	AND ' . $t_project_clause . '
	ORDER BY s.paid ASC, b.project_id ASC, b.fixed_in_version ASC, b.status ASC, b.id DESC';

$t_result = \Core\Database::query( $t_query, $t_show_all ? array( $t_user ) : array( $t_user , $t_resolved ) );

$t_sponsors = array();
while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
	$t_sponsors[] = $t_row;
}

$t_sponsor_count = count( $t_sponsors );
if( $t_sponsor_count === 0 ) {
	echo '<p>' . \Core\Lang::get( 'no_sponsored' ) . '</p>';
} else {
?>

<!-- # Edit sponsorship Form BEGIN -->
<br />
<div>
<form method="post" action="account_sponsor_update.php">
<?php echo \Core\Form::security_field( 'account_sponsor_update' ) ?>
<table class="width100" cellspacing="1">
	<colgroup>
		<col style="width:10%" />
		<col style="width:8%" />
		<col style="width:7%" />
		<col style="width:10%" />
		<col style="width:35%" />
		<col style="width:10%" />
		<col style="width:10%" />
		<col style="width:10%" />
	</colgroup>
	<!-- Headings -->
	<tr>
		<td class="form-title" colspan="8">
			<?php echo \Core\Lang::get( 'issues_handled' ) ?>
		</td>
	</tr>
	<tr>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_bug' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_project' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'fixed_in_version' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_status' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'email_summary' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'sponsor' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'amount' ) ?></td>
		<td class="form-title"><?php echo \Core\Lang::get( 'status' ) ?></td>
	</tr>
<?php
	$t_bug_list = array();
	$t_total_owing = 0;
	$t_total_paid = 0;
	for( $i = 0; $i < $t_sponsor_count; ++$i ) {
		$t_sponsor_row = $t_sponsors[$i];
		$t_bug = \Core\Bug::get( $t_sponsor_row['bug'] );
		$t_sponsor = \Core\Sponsorship::get( $t_sponsor_row['sponsor'] );
		$t_buglist[] = $t_sponsor_row['bug'] . ':' . $t_sponsor_row['sponsor'];

		# describe bug
		$t_status = \Core\String::attribute( \Core\Helper::get_enum_element( 'status', $t_bug->status, \Core\Auth::get_current_user_id(), $t_bug->project_id ) );
		$t_resolution = \Core\String::attribute( \Core\Helper::get_enum_element( 'resolution', $t_bug->resolution, \Core\Auth::get_current_user_id(), $t_bug->project_id ) );
		$t_version_id = \Core\Version::get_id( $t_bug->fixed_in_version, $t_bug->project_id );
		if( ( false !== $t_version_id ) && ( VERSION_RELEASED == \Core\Version::get_field( $t_version_id, 'released' ) ) ) {
			$t_released_label = '<a title="' . \Core\Lang::get( 'released' ) . '">' . $t_bug->fixed_in_version . '</a>';
		} else {
			$t_released_label = $t_bug->fixed_in_version;
		}

		# choose color based on status
		$t_status_label = \Core\HTML::get_status_css_class( $t_bug->status, \Core\Auth::get_current_user_id(), $t_bug->project_id );

		echo '<tr class="' . $t_status_label .  '">';
		echo '<td><a href="' . \Core\String::get_bug_view_url( $t_sponsor_row['bug'] ) . '">' . \Core\Bug::format_id( $t_sponsor_row['bug'] ) . '</a></td>';
		echo '<td>' . \Core\String::display_line( \Core\Project::get_field( $t_bug->project_id, 'name' ) ) . '&#160;</td>';
		echo '<td class="right">' . $t_released_label . '&#160;</td>';
		echo '<td><a title="' . $t_resolution . '"><span class="underline">' . $t_status . '</span>&#160;</a></td>';

		# summary
		echo '<td>' . \Core\String::display_line( $t_bug->summary );
		if( VS_PRIVATE == $t_bug->view_state ) {
			printf( ' <img src="%s" alt="(%s)" title="%s" />', $t_icon_path . 'protected.gif', \Core\Lang::get( 'private' ), \Core\Lang::get( 'private' ) );
		}
		echo '</td>';

		# describe sponsorship amount
		echo '<td>';
		\Core\Print_Util::user( $t_sponsor->user_id );
		echo '</td>';
		echo '<td class="right">' . \Core\Sponsorship::format_amount( $t_sponsor->amount ) . '</td>';
		echo '<td><select name="sponsor_' . $t_row['bug'] . '_' . $t_sponsor->id . '">';
		\Core\Print_Util::enum_string_option_list( 'sponsorship', $t_sponsor->paid );
		echo '</select></td>';

		echo '</tr>';
		if( SPONSORSHIP_PAID == $t_sponsor->paid ) {
			$t_total_paid += $t_sponsor->amount;
		} else {
			$t_total_owing += $t_sponsor->amount;
		}

	}
	$t_hidden_bug_list = implode( ',', $t_buglist );
?>
<!-- Totals -->
<tr>
	<td colspan="5"></td>
	<td><?php echo \Core\Lang::get( 'total_owing' ) ?></td>
	<td class="right"><?php echo \Core\Sponsorship::format_amount( $t_total_owing ) ?></td>
	<td></td>
</tr>
<tr>
	<td colspan="5"></td>
	<td><?php echo \Core\Lang::get( 'total_paid' ) ?></td>
	<td class="right"><?php echo \Core\Sponsorship::format_amount( $t_total_paid ) ?></td>
	<td></td>
</tr>
	<!-- BUTTONS -->
	<tr>
		<td colspan="5">&#160;</td>
		<!-- Update Button -->
		<td colspan="3">
			<input type="hidden" name="buglist" value="<?php echo $t_hidden_bug_list ?>" />
			<input type="submit" class="button" value="<?php echo \Core\Lang::get( 'update_sponsorship_button' ) ?>" />
		</td>
	</tr>
</table>
</form>
</div>
<?php } # end sponsored issues ?>

<br />
<div>
<?php
\Core\HTML::button( 'account_sponsor_page.php',
	\Core\Lang::get( ( $t_show_all ? 'sponsor_hide' : 'sponsor_show' ) ),
	array( 'show_all' => ( $t_show_all ? 0 : 1 ) ) );
?>
</div>

<?php
\Core\HTML::page_bottom();
