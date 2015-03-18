<?php
use Core\Lang;


$f_bug_id = $ticket->id;
$t_bugnote_order = $order;

$t_user_id = \Core\Auth::get_current_user_id();

#precache access levels
\Core\Access::cache_matrix_project( \Core\Helper::get_current_project() );

# get the bugnote data
$t_bugnote_order = \Core\Current_User::get_pref( 'bugnote_order' );
$t_bugnotes = \Core\Bug\Note::get_all_visible_bugnotes( $f_bug_id, $t_bugnote_order, 0, $t_user_id );
$t_show_time_tracking = \Core\Access::has_bug_level( \Core\Config::mantis_get( 'time_tracking_view_threshold' ), $f_bug_id );

#precache users
$t_bugnote_users = array();
foreach( $t_bugnotes as $t_bugnote ) {
	$t_bugnote_users[] = $t_bugnote->reporter_id;
}
\Core\User::cache_array_rows( $t_bugnote_users );

$t_num_notes = count( $t_bugnotes );
?>

<details id="bug_notes" open>

<summary><?php echo Lang::get( 'bug_notes_title' ) ?></summary>

<?php
	# no bugnotes
	if( 0 == $t_num_notes ) {
?>
<p><?php echo Lang::get( 'no_bugnotes_msg' ) ?></p>
<?php }

	$t_normal_date_format = \Core\Config::mantis_get( 'normal_date_format' );
	$t_total_time = 0;

	$t_bugnote_user_edit_threshold = \Core\Config::mantis_get( 'bugnote_user_edit_threshold' );
	$t_bugnote_user_delete_threshold = \Core\Config::mantis_get( 'bugnote_user_delete_threshold' );
	$t_bugnote_user_change_view_state_threshold = \Core\Config::mantis_get( 'bugnote_user_change_view_state_threshold' );
	$t_can_edit_all_bugnotes = \Core\Access::has_bug_level( \Core\Config::mantis_get( 'update_bugnote_threshold' ), $f_bug_id );
	$t_can_delete_all_bugnotes = \Core\Access::has_bug_level( \Core\Config::mantis_get( 'delete_bugnote_threshold' ), $f_bug_id );
	$t_can_change_view_state_all_bugnotes = $t_can_edit_all_bugnotes && \Core\Access::has_bug_level( \Core\Config::mantis_get( 'change_view_status_threshold' ), $f_bug_id );

	for( $i=0; $i < $t_num_notes; $i++ ) {
		$t_bugnote = $t_bugnotes[$i];

		if( $t_bugnote->date_submitted != $t_bugnote->last_modified ) {
			$t_bugnote_modified = true;
		} else {
			$t_bugnote_modified = false;
		}

		$t_bugnote_id_formatted = \Core\Bug\Note::format_id( $t_bugnote->id );

		if( $t_bugnote->time_tracking != 0 ) {
			$t_time_tracking_hhmm = \Core\Database::minutes_to_hhmm( $t_bugnote->time_tracking );
			$t_total_time += $t_bugnote->time_tracking;
		} else {
			$t_time_tracking_hhmm = '';
		}

		if( VS_PRIVATE == $t_bugnote->view_state ) {
			$t_bugnote_css		= 'bugnote-private';
		} else {
			$t_bugnote_css		= 'bugnote-public';
		}

		if( TIME_TRACKING == $t_bugnote->note_type ) {
		    $t_bugnote_css    .= ' bugnote-time-tracking';
	    } else if( REMINDER == $t_bugnote->note_type ) {
	        $t_bugnote_css    .= ' bugnote-reminder';
		}
?>
<div class="bug-note <?php echo $t_bugnote_css ?>" id="bug_note_<?php echo $t_bugnote->id ?>">
		<div class="bugnote-meta">
		<?php Print_Util::avatar( $t_bugnote->reporter_id, 120 ); ?>
		<p class="compact"><span class="small bugnote-permalink"><a rel="bookmark" href="<?php echo \Core\String::get_bugnote_view_url( $t_bugnote->bug_id, $t_bugnote->id ) ?>" title="<?php echo \Core\Lang::get( 'bugnote_link_title' ) ?>"><?php echo htmlentities( \Core\Config::get_global( 'bugnote_link_tag' ) ) . $t_bugnote_id_formatted ?></a></span></p>

		<p class="compact">
		<span class="bugnote-reporter">
		<?php
			\Core\Print_Util::user( $t_bugnote->reporter_id );
		?>
		<span class="small access-level"><?php
			if( \Core\User::exists( $t_bugnote->reporter_id ) ) {
				$t_access_level = \Core\Access::get_project_level( null, (int)$t_bugnote->reporter_id );
				# Only display access level when higher than 0 (ANYBODY)
				if( $t_access_level > ANYBODY ) {
					echo '(', \Core\Helper::get_enum_element( 'access_levels', $t_access_level ), ')';
				}
			}
		?></span>
		</span>

		<?php if( VS_PRIVATE == $t_bugnote->view_state ) { ?>
		<span class="small bugnote-view-state">[ <?php echo \Core\Lang::get( 'private' ) ?> ]</span>
		<?php } ?>
		</p>
		<p class="compact"><span class="small bugnote-date-submitted"><?php echo date( $t_normal_date_format, $t_bugnote->date_submitted ); ?></span></p>
		<?php
		if( $t_bugnote_modified ) {
			echo '<p class="compact"><span class="small bugnote-last-modified">' . \Core\Lang::get( 'last_edited' ) . \Core\Lang::get( 'word_separator' ) . date( $t_normal_date_format, $t_bugnote->last_modified ) . '</span></p>';
			$t_revision_count = \Core\Bug\Revision::count( $f_bug_id, REV_BUGNOTE, $t_bugnote->id );
			if( $t_revision_count >= 1 ) {
				$t_view_num_revisions_text = sprintf( \Core\Lang::get( 'view_num_revisions' ), $t_revision_count );
				echo '<p class="compact"><span class="small bugnote-revisions-link"><a href="bug_revision_view_page.php?bugnote_id=' . $t_bugnote->id . '">' . $t_view_num_revisions_text . '</a></span></p>';
			}
		}
		?>
		<div class="small bugnote-buttons">
		<?php
			# bug must be open to be editable
			if( !\Core\Bug::is_readonly( $f_bug_id ) ) {

				# check if the user can edit this bugnote
				if( $t_user_id == $t_bugnote->reporter_id ) {
					$t_can_edit_bugnote = \Core\Access::has_bugnote_level( $t_bugnote_user_edit_threshold, $t_bugnote->id );
				} else {
					$t_can_edit_bugnote = $t_can_edit_all_bugnotes;
				}

				# check if the user can delete this bugnote
				if( $t_user_id == $t_bugnote->reporter_id ) {
					$t_can_delete_bugnote = \Core\Access::has_bugnote_level( $t_bugnote_user_delete_threshold, $t_bugnote->id );
				} else {
					$t_can_delete_bugnote = $t_can_delete_all_bugnotes;
				}

				# check if the user can make this bugnote private
				if( $t_user_id == $t_bugnote->reporter_id ) {
					$t_can_change_view_state = \Core\Access::has_bugnote_level( $t_bugnote_user_change_view_state_threshold, $t_bugnote->id );
				} else {
					$t_can_change_view_state = $t_can_change_view_state_all_bugnotes;
				}

				# show edit button if the user is allowed to edit this bugnote
				if( $t_can_edit_bugnote ) {
					\Core\Print_Util::button( 'bugnote_edit_page.php?bugnote_id='.$t_bugnote->id, \Core\Lang::get( 'bugnote_edit_link' ) );
				}

				# show delete button if the user is allowed to delete this bugnote
				if( $t_can_delete_bugnote ) {
					\Core\Print_Util::button( 'bugnote_delete.php?bugnote_id='.$t_bugnote->id, \Core\Lang::get( 'delete_link' ) );
				}

				# show make public or make private button if the user is allowed to change the view state of this bugnote
				if( $t_can_change_view_state ) {
					if( VS_PRIVATE == $t_bugnote->view_state ) {
						\Core\Print_Util::button( 'bugnote_set_view_state.php?private=0&bugnote_id=' . $t_bugnote->id, \Core\Lang::get( 'make_public' ) );
					} else {
						\Core\Print_Util::button( 'bugnote_set_view_state.php?private=1&bugnote_id=' . $t_bugnote->id, \Core\Lang::get( 'make_private' ) );
					}
				}
			}
		?>
		</div>
	</div>
	<div class="bugnote-note">
		<?php
			switch( $t_bugnote->note_type ) {
				case REMINDER:
					echo '<strong>';

					# List of recipients; remove surrounding delimiters
					$t_recipients = trim( $t_bugnote->note_attr, '|' );

					if( empty( $t_recipients ) ) {
						echo \Core\Lang::get( 'reminder_sent_none' );
					} else {
						# If recipients list's last char is not a delimiter, it was truncated
						$t_truncated = ( '|' != utf8_substr( $t_bugnote->note_attr, utf8_strlen( $t_bugnote->note_attr ) - 1 ) );

						# Build recipients list for display
						$t_to = array();
						foreach ( explode( '|', $t_recipients ) as $t_recipient ) {
							$t_to[] = \Core\Prepare::user_name( $t_recipient );
						}

						echo \Core\Lang::get( 'reminder_sent_to' ) . ': '
							. implode( ', ', $t_to )
							. ( $t_truncated ? ' (' . \Core\Lang::get( 'reminder_list_truncated' ) . ')' : '' );
					}

					echo '</strong><br /><br />';
					break;

				case TIME_TRACKING:
					if( $t_show_time_tracking ) {
						echo '<div class="time-tracked">', \Core\Lang::get( 'time_tracking_time_spent' ) . ' ' . $t_time_tracking_hhmm, '</div>';
					}
					break;
			}

			echo \Core\String::display_links( $t_bugnote->note );
		?>
	</div>
</div>
<?php
	} # end for loop
?>
<?php

if( $t_total_time > 0 && $t_show_time_tracking ) {
	echo '<p class="time-tracking-total">', sprintf( \Core\Lang::get( 'total_time_for_issue' ), '<span class="time-tracked">' . \Core\Database::minutes_to_hhmm( $t_total_time ) . '</span>' ), '</p>';
}
?>

</details>