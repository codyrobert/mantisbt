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
 * Tag Attach
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses utility_api.php
 */



\Core\Form::security_validate( 'tag_attach' );

$f_bug_id = \Core\GPC::get_int( 'bug_id' );
$f_tag_select = \Core\GPC::get_int( 'tag_select' );
$f_tag_string = \Core\GPC::get_string( 'tag_string' );

$t_user_id = \Core\Auth::get_current_user_id();

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'tag_attach_threshold' ), $f_bug_id, $t_user_id );

# @todo The handling of tag strings which can include multiple tags should be moved
#     to the APIs.  This is to allow other clients of the API to support such
#     functionality.  The access level checks should also be moved to the API.
$t_tags = \Core\Tag::parse_string( $f_tag_string );
$t_can_create = \Core\Access::has_global_level( \Core\Config::mantis_get( 'tag_create_threshold' ) );

$t_tags_create = array();
$t_tags_attach = array();
$t_tags_failed = array();

foreach ( $t_tags as $t_tag_row ) {
	if( -1 == $t_tag_row['id'] ) {
		if( $t_can_create ) {
			$t_tags_create[] = $t_tag_row;
		} else {
			$t_tags_failed[] = $t_tag_row;
		}
	} else if( -2 == $t_tag_row['id'] ) {
		$t_tags_failed[] = $t_tag_row;
	} else {
		$t_tags_attach[] = $t_tag_row;
	}
}

if( 0 < $f_tag_select && \Core\Tag::exists( $f_tag_select ) ) {
	$t_tags_attach[] = \Core\Tag::get( $f_tag_select );
}

# failed to attach at least one tag
if( count( $t_tags_failed ) > 0 ) {
	\Core\HTML::page_top( \Core\Lang::get( 'tag_attach_long' ) . ' ' . \Core\Bug::format_summary( $f_bug_id, SUMMARY_CAPTION ) );
?>
<br/>
<table class="width75">
	<tr class="row-category">
	<td colspan="2"><?php echo \Core\Lang::get( 'tag_attach_failed' ) ?></td>
	</tr>
	<tr class="spacer"><td colspan="2"></td></tr>
<?php
	$t_tag_string = '';
	foreach( $t_tags_attach as $t_tag_row ) {
		if( !\Core\Utility::is_blank( $t_tag_string ) ) {
			$t_tag_string .= \Core\Config::mantis_get( 'tag_separator' );
		}
		$t_tag_string .= $t_tag_row['name'];
	}

	foreach( $t_tags_failed as $t_tag_row ) {
		echo '<tr>';
		if( -1 == $t_tag_row['id'] ) {
			echo '<th class="category">', \Core\Lang::get( 'tag_create_denied' ), '</th>';
		} else if( -2 == $t_tag_row['id'] ) {
			echo '<th class="category">', \Core\Lang::get( 'tag_invalid_name' ), '</th>';
		}
		echo '<td>', \Core\String::html_specialchars( $t_tag_row['name'] ), '</td></tr>';

		if( !\Core\Utility::is_blank( $t_tag_string ) ) {
			$t_tag_string .= \Core\Config::mantis_get( 'tag_separator' );
		}
		$t_tag_string .= $t_tag_row['name'];
	}
?>
	<tr class="spacer"><td colspan="2"></td></tr>
	<tr>
	<th class="category"><?php echo \Core\Lang::get( 'tag_attach_long' ) ?></th>
	<td>
<?php
	\Core\Print_Util::tag_attach_form( $f_bug_id, $t_tag_string );
?>
	</td>
	</tr>
</table>
<?php
	\Core\HTML::page_bottom();
	# end failed to attach tag
} else {
	foreach( $t_tags_create as $t_tag_row ) {
		$t_tag_row['id'] = \Core\Tag::create( $t_tag_row['name'], $t_user_id );
		$t_tags_attach[] = $t_tag_row;
	}

	foreach( $t_tags_attach as $t_tag_row ) {
		if( !\Core\Tag::bug_is_attached( $t_tag_row['id'], $f_bug_id ) ) {
			\Core\Tag::bug_attach( $t_tag_row['id'], $f_bug_id, $t_user_id );
		}
	}

	\Core\Event::signal( 'EVENT_TAG_ATTACHED', array( $f_bug_id, $t_tags_attach ) );

	\Core\Form::security_purge( 'tag_attach' );

	\Core\Print_Util::successful_redirect_to_bug( $f_bug_id );
}
