<?php
namespace Core;

use \Core\Config;
use \Core\Enum;


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
 * Print API
 *
 * @package CoreAPI
 * @subpackage PrintAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_group_action_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses collapse_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses news_api.php
 * @uses prepare_api.php
 * @uses profile_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */




class Print_Util
{

	/**
	 * Print the headers to cause the page to redirect to $p_url
	 * If $p_die is true (default), terminate the execution of the script immediately
	 * If we have handled any errors on this page return false and don't redirect.
	 * $p_sanitize - true/false - true in the case where the URL is extracted from GET/POST or untrusted source.
	 * This would be false if the URL is trusted (e.g. read from config_inc.php).
	 *
	 * @param string  $p_url      The page to redirect: has to be a relative path.
	 * @param boolean $p_die      If true, stop the script after redirecting.
	 * @param boolean $p_sanitize Apply string_sanitize_url to passed URL.
	 * @param boolean $p_absolute Indicate if URL is absolute.
	 * @return boolean
	 */
	static function header_redirect( $p_url, $p_die = true, $p_sanitize = false, $p_absolute = false ) {
		if( ON == Config::get_global( 'stop_on_errors' ) && \Core\Error::handled() ) {
			return false;
		}
	
		# validate the url as part of this site before continuing
		if( $p_absolute ) {
			if( $p_sanitize ) {
				$t_url = \Core\String::sanitize_url( $p_url );
			} else {
				$t_url = $p_url;
			}
		} else {
			if( $p_sanitize ) {
				$t_url = \Core\String::sanitize_url( $p_url, true );
			} else {
				$t_url = Config::mantis_get( 'path' ) . $p_url;
			}
		}
	
		$t_url = \Core\String::prepare_header( $t_url );
	
		# don't send more headers if they have already been sent
		if( !headers_sent() ) {
			header( 'Content-Type: text/html; charset=utf-8' );
			header( 'Location: ' . $t_url );
		} else {
			trigger_error( ERROR_PAGE_REDIRECTION, ERROR );
			return false;
		}
	
		if( $p_die ) {
			die;
	
			# additional output can cause problems so let's just stop output here
		}
	
		return true;
	}
	
	/**
	 * Print a redirect header to view a bug
	 *
	 * @param integer $p_bug_id A bug identifier.
	 * @return void
	 */
	static function header_redirect_view( $p_bug_id ) {
		self::header_redirect( \Core\String::get_bug_view_url( $p_bug_id ) );
	}
	
	/**
	 * Get a view URL for the bug id based on the user's preference and
	 * call self::successful_redirect() with that URL
	 *
	 * @param integer $p_bug_id A bug identifier.
	 * @return void
	 */
	static function successful_redirect_to_bug( $p_bug_id ) {
		$t_url = \Core\String::get_bug_view_url( $p_bug_id, \Core\Auth::get_current_user_id() );
	
		self::successful_redirect( $t_url );
	}
	
	/**
	 * If the show query count is ON, print success and redirect after the configured system wait time.
	 * If the show query count is OFF, redirect right away.
	 *
	 * @param string $p_redirect_to URI to redirect to.
	 * @return void
	 */
	static function successful_redirect( $p_redirect_to ) {
		if( \Core\Helper::log_to_page() ) {
			\Core\HTML::page_top( null, $p_redirect_to );
			echo '<br /><div class="center">';
			echo \Core\Lang::get( 'operation_successful' ) . '<br />';
			self::bracket_link( $p_redirect_to, \Core\Lang::get( 'proceed' ) );
			echo '</div>';
			\Core\HTML::page_bottom();
		} else {
			self::header_redirect( $p_redirect_to );
		}
	}
	
	/**
	 * Print avatar image for the given user ID
	 *
	 * @param integer $p_user_id A user identifier.
	 * @param integer $p_size    Image pixel size.
	 * @return void
	 */
	static function avatar( $p_user_id, $p_size = 80 ) {
		if( OFF === Config::mantis_get( 'show_avatar' ) ) {
			return;
		}
	
		if( !\Core\User::exists( $p_user_id ) ) {
			return;
		}
	
		if( \Core\Access::has_project_level( Config::mantis_get( 'show_avatar_threshold' ), null, $p_user_id ) ) {
			$t_avatar = \Core\User::get_avatar( $p_user_id, $p_size );
			if( !empty( $t_avatar ) ) {
				$t_avatar_url = htmlspecialchars( $t_avatar[0] );
				$t_width = $t_avatar[1];
				$t_height = $t_avatar[2];
				echo '<a rel="nofollow" href="http://site.gravatar.com"><img class="avatar" src="' . $t_avatar_url . '" alt="User avatar" width="' . $t_width . '" height="' . $t_height . '" /></a>';
			}
		}
	}
	
	/**
	 * prints the name of the user given the id.  also makes it an email link.
	 *
	 * @param integer $p_user_id A user identifier.
	 * @return void
	 */
	static function user( $p_user_id ) {
		echo \Core\Prepare::user_name( $p_user_id );
	}
	
	/**
	 * same as echo get_user_name() but fills in the subject with the bug summary
	 *
	 * @param integer $p_user_id A user identifier.
	 * @param integer $p_bug_id  A bug identifier.
	 * @return void
	 */
	static function user_with_subject( $p_user_id, $p_bug_id ) {
		if( NO_USER == $p_user_id ) {
			return;
		}
	
		$t_username = \Core\User::get_name( $p_user_id );
		if( \Core\User::exists( $p_user_id ) && \Core\User::get_field( $p_user_id, 'enabled' ) ) {
			$t_email = \Core\User::get_email( $p_user_id );
			self::email_link_with_subject( $t_email, $t_username, $p_bug_id );
		} else {
			echo '<span class="user" style="text-decoration: line-through">';
			echo $t_username;
			echo '</span>';
		}
	}
	
	/**
	 * print out an email editing input
	 *
	 * @param string $p_field_name Name of input tag.
	 * @param string $p_email      Email address.
	 * @return void
	 */
	static function email_input( $p_field_name, $p_email ) {
		echo '<input id="email-field" type="text" name="' . \Core\String::attribute( $p_field_name ) . '" size="32" maxlength="64" value="' . \Core\String::attribute( $p_email ) . '" />';
	}
	
	/**
	 * print out an email editing input
	 *
	 * @param string $p_field_name Name of input tag.
	 * @return void
	 */
	static function captcha_input( $p_field_name ) {
		echo '<input id="captcha-field" type="text" name="' . $p_field_name . '" size="6" maxlength="6" value="" />';
	}
	
	/**
	 * This populates an option list with the appropriate users by access level
	 * @todo from print_reporter_option_list
	 * @param integer $p_user_id    A user identifier.
	 * @param integer $p_project_id A project identifier.
	 * @param integer $p_access     An access level.
	 * @return void
	 */
	static function user_option_list( $p_user_id, $p_project_id = null, $p_access = ANYBODY ) {
		$t_current_user = \Core\Auth::get_current_user_id();
	
		if( null === $p_project_id ) {
			$p_project_id = \Core\Helper::get_current_project();
		}
	
		if( $p_project_id === ALL_PROJECTS ) {
			$t_projects = \Core\User::get_accessible_projects( $t_current_user );
	
			# Get list of users having access level for all accessible projects
			$t_users = array();
			foreach( $t_projects as $t_project_id ) {
				$t_project_users_list = \Core\Project::get_all_user_rows( $t_project_id, $p_access );
				# Do a 'smart' merge of the project's user list, into an
				# associative array (to remove duplicates)
				# Use a while loop for better performance
				$i = 0;
				while( isset( $t_project_users_list[$i] ) ) {
					$t_users[$t_project_users_list[$i]['id']] = $t_project_users_list[$i];
					$i++;
			}
				unset( $t_project_users_list );
			}
			unset( $t_projects );
		} else {
			$t_users = \Core\Project::get_all_user_rows( $p_project_id, $p_access );
		}
	
		$t_display = array();
		$t_sort = array();
		$t_show_realname = ( ON == Config::mantis_get( 'show_realname' ) );
		$t_sort_by_last_name = ( ON == Config::mantis_get( 'sort_by_last_name' ) );
		foreach( $t_users as $t_key => $t_user ) {
			$t_user_name = \Core\String::attribute( $t_user['username'] );
			$t_sort_name = utf8_strtolower( $t_user_name );
			if( $t_show_realname && ( $t_user['realname'] <> '' ) ) {
				$t_user_name = \Core\String::attribute( $t_user['realname'] );
				if( $t_sort_by_last_name ) {
					$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
					$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
				} else {
					$t_sort_name = utf8_strtolower( $t_user_name );
				}
			}
			$t_display[] = $t_user_name;
			$t_sort[] = $t_sort_name;
		}
		array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
		unset( $t_sort );
		$t_count = count( $t_users );
		for( $i = 0;$i < $t_count;$i++ ) {
			$t_row = $t_users[$i];
			echo '<option value="' . $t_row['id'] . '" ';
			\Core\Helper::check_selected( $p_user_id, (int)$t_row['id'] );
			echo '>' . $t_display[$i] . '</option>';
		}
	}
	
	/**
	 * This populates the reporter option list with the appropriate users
	 *
	 * @todo ugly functions  need to be refactored
	 * @todo This function really ought to print out all the users, I think.
	 *  I just encountered a situation where a project used to be public and
	 *  was made private, so now I can't filter on any of the reporters who
	 *  actually reported the bugs at the time. Maybe we could get all user
	 *  who are listed as the reporter in any bug?  It would probably be a
	 *  faster query actually.
	 * @param integer $p_user_id    A user identifier.
	 * @param integer $p_project_id A project identifier.
	 * @return void
	 */
	static function reporter_option_list( $p_user_id, $p_project_id = null ) {
		self::user_option_list( $p_user_id, $p_project_id, Config::mantis_get( 'report_bug_threshold' ) );
	}
	
	/**
	 * Print the entire form for attaching a tag to a bug.
	 * @param integer $p_bug_id A bug identifier.
	 * @param string  $p_string Default contents of the input box.
	 * @return boolean
	 */
	static function tag_attach_form( $p_bug_id, $p_string = '' ) {
	?>
		<small><?php echo sprintf( \Core\Lang::get( 'tag_separate_by' ), Config::mantis_get( 'tag_separator' ) )?></small>
		<form method="post" action="tag_attach.php">
		<?php echo \Core\Form::security_field( 'tag_attach' )?>
		<input type="hidden" name="bug_id" value="<?php echo $p_bug_id?>" />
		<?php self::tag_input( $p_bug_id, $p_string ); ?>
		<input type="submit" value="<?php echo \Core\Lang::get( 'tag_attach' )?>" class="button" />
		</form>
	<?php
		return true;
	}
	
	/**
	 * Print the separator comment, input box, and existing tag dropdown menu.
	 * @param integer $p_bug_id A bug identifier.
	 * @param string  $p_string Default contents of the input box.
	 * @return void
	 */
	static function tag_input( $p_bug_id = 0, $p_string = '' ) {
	?>
		<input type="hidden" id="tag_separator" value="<?php echo Config::mantis_get( 'tag_separator' )?>" />
		<input type="text" name="tag_string" id="tag_string" size="40" value="<?php echo \Core\String::attribute( $p_string )?>" />
		<select <?php echo \Core\Helper::get_tab_index()?> name="tag_select" id="tag_select">
			<?php self::tag_option_list( $p_bug_id );?>
		</select>
	<?php
	}
	
	/**
	 * Print the drop-down combo-box of existing tags.
	 * When passed a bug ID, the option list will not contain any tags attached to the given bug.
	 * @param integer $p_bug_id A bug identifier.
	 * @return void
	 */
	static function tag_option_list( $p_bug_id = 0 ) {
		$t_rows = \Core\Tag::get_candidates_for_bug( $p_bug_id );
	
		echo '<option value="0">', \Core\String::html_specialchars( \Core\Lang::get( 'tag_existing' ) ), '</option>';
		foreach ( $t_rows as $t_row ) {
			$t_string = $t_row['name'];
			if( !empty( $t_row['description'] ) ) {
				$t_string .= ' - ' . utf8_substr( $t_row['description'], 0, 20 );
			}
			echo '<option value="', $t_row['id'], '" title="', \Core\String::attribute( $t_row['name'] ), '">', \Core\String::attribute( $t_string ), '</option>';
		}
	}
	
	/**
	 * Get current headlines and id  prefix with v_
	 * @return void
	 */
	static function news_item_option_list() {
		$t_project_id = \Core\Helper::get_current_project();
	
		$t_global = \Core\Access::has_global_level( Config::get_global( 'admin_site_threshold' ) );
		if( $t_global ) {
			$t_query = 'SELECT id, headline, announcement, view_state FROM {news} ORDER BY date_posted DESC';
		} else {
			$t_query = 'SELECT id, headline, announcement, view_state FROM {news}
					WHERE project_id=' . \Core\Database::param() . '
					ORDER BY date_posted DESC';
		}
	
		$t_result = \Core\Database::query( $t_query, ($t_global == true ? array() : array( $t_project_id ) ) );
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_headline = \Core\String::display( $t_row['headline'] );
			$t_announcement = $t_row['announcement'];
			$t_view_state = $t_row['view_state'];
			$t_id = $t_row['id'];
	
			$t_notes = array();
			$t_note_string = '';
	
			if( 1 == $t_announcement ) {
				array_push( $t_notes, \Core\Lang::get( 'announcement' ) );
			}
	
			if( VS_PRIVATE == $t_view_state ) {
				array_push( $t_notes, \Core\Lang::get( 'private' ) );
			}
	
			if( count( $t_notes ) > 0 ) {
				$t_note_string = ' [' . implode( ' ', $t_notes ) . ']';
			}
	
			echo '<option value="' . $t_id . '">' . $t_headline . $t_note_string . '</option>';
		}
	}
	
	/**
	 * Constructs the string for one news entry given the row retrieved from the news table.
	 *
	 * @param string  $p_headline     Headline of news article.
	 * @param string  $p_body         Body text of news article.
	 * @param integer $p_poster_id    User ID of author.
	 * @param integer $p_view_state   View State - either VS_PRIVATE or VS_PUBLIC.
	 * @param boolean $p_announcement Flagged if news should be an announcement.
	 * @param integer $p_date_posted  Date associated with news entry.
	 * @return void
	 */
	static function news_entry( $p_headline, $p_body, $p_poster_id, $p_view_state, $p_announcement, $p_date_posted ) {
		$t_headline = \Core\String::display_links( $p_headline );
		$t_body = \Core\String::display_links( $p_body );
		$t_date_posted = date( Config::mantis_get( 'normal_date_format' ), $p_date_posted );
	
		if( VS_PRIVATE == $p_view_state ) {
			$t_news_css = 'news-heading-private';
		} else {
			$t_news_css = 'news-heading-public';
		} ?>
	
		<div class="news-item">
			<h3 class="<?php echo $t_news_css; ?>">
				<span class="news-title"><?php echo $t_headline; ?></span>
				<span class="news-date-posted"><?php echo $t_date_posted; ?></span>
				<span class="news-author"><?php echo \Core\Prepare::user_name( $p_poster_id ); ?></span><?php
	
				if( 1 == $p_announcement ) { ?>
					<span class="news-announcement"><?php echo \Core\Lang::get( 'announcement' ); ?></span><?php
				}
				if( VS_PRIVATE == $p_view_state ) { ?>
					<span class="news-private"><?php echo \Core\Lang::get( 'private' ); ?></span><?php
				} ?>
			</h3>
			<p class="news-body"><?php echo $t_body; ?></p>
		</div><?php
	}
	
	/**
	 * print a news item given a row in the news table.
	 * @param array $p_news_row A news database result.
	 * @return void
	 */
	static function news_entry_from_row( array $p_news_row ) {
		$t_headline = $p_news_row['headline'];
		$t_body = $p_news_row['body'];
		$t_poster_id = $p_news_row['poster_id'];
		$t_view_state = $p_news_row['view_state'];
		$t_announcement = $p_news_row['announcement'];
		$t_date_posted = $p_news_row['date_posted'];
	
		self::news_entry( $t_headline, $t_body, $t_poster_id, $t_view_state, $t_announcement, $t_date_posted );
	}
	
	/**
	 * print a news item
	 *
	 * @param integer $p_news_id A news article identifier.
	 * @return void
	 */
	static function news_string_by_news_id( $p_news_id ) {
		$t_row = \Core\News::get_row( $p_news_id );
	
		# only show VS_PRIVATE posts to configured threshold and above
		if( ( VS_PRIVATE == $t_row['view_state'] ) && !\Core\Access::has_project_level( Config::mantis_get( 'private_news_threshold' ) ) ) {
			return;
		}
	
		self::news_entry_from_row( $t_row );
	}
	
	/**
	 * Print User option list for assigned to field
	 * @param integer|string $p_user_id    A user identifier.
	 * @param integer        $p_project_id A project identifier.
	 * @param integer        $p_threshold  An access level.
	 * @return void
	 */
	static function assign_to_option_list( $p_user_id = '', $p_project_id = null, $p_threshold = null ) {
		if( null === $p_threshold ) {
			$p_threshold = Config::mantis_get( 'handle_bug_threshold' );
		}
	
		self::user_option_list( $p_user_id, $p_project_id, $p_threshold );
	}
	
	/**
	 * Print User option list for bugnote filter field
	 * @param integer|string $p_user_id    A user identifier.
	 * @param integer        $p_project_id A project identifier.
	 * @param integer        $p_threshold  An access level.
	 * @return void
	 */
	static function note_option_list( $p_user_id = '', $p_project_id = null, $p_threshold = null ) {
		if( null === $p_threshold ) {
			$p_threshold = Config::mantis_get( 'add_bugnote_threshold' );
		}
	
		self::user_option_list( $p_user_id, $p_project_id, $p_threshold );
	}
	
	/**
	 * List projects that the current user has access to.
	 *
	 * @param integer        $p_project_id           The current project id or null to use cookie.
	 * @param boolean        $p_include_all_projects True: include "All Projects", otherwise false.
	 * @param integer|null   $p_filter_project_id    The id of a project to exclude or null.
	 * @param string|boolean $p_trace                The current project trace, identifies the sub-project via a path from top to bottom.
	 * @param boolean        $p_can_report_only      If true, disables projects in which user can't report issues; defaults to false (all projects enabled).
	 * @return void
	 */
	static function project_option_list( $p_project_id = null, $p_include_all_projects = true, $p_filter_project_id = null, $p_trace = false, $p_can_report_only = false ) {
		$t_user_id = \Core\Auth::get_current_user_id();
		$t_project_ids = \Core\User::get_accessible_projects( $t_user_id );
		$t_can_report = true;
		\Core\Project::cache_array_rows( $t_project_ids );
	
		if( $p_include_all_projects && $p_filter_project_id !== ALL_PROJECTS ) {
			echo '<option value="' . ALL_PROJECTS . '"';
			if( $p_project_id !== null ) {
				\Core\Helper::check_selected( $p_project_id, ALL_PROJECTS, false );
			}
			echo '>' . \Core\Lang::get( 'all_projects' ) . '</option>' . "\n";
		}
	
		foreach( $t_project_ids as $t_id ) {
			if( $p_can_report_only ) {
				$t_report_bug_threshold = Config::mantis_get( 'report_bug_threshold', null, $t_user_id, $t_id );
				$t_can_report = \Core\Access::has_project_level( $t_report_bug_threshold, $t_id, $t_user_id );
			}
	
			echo '<option value="' . $t_id . '"';
			\Core\Helper::check_selected( $p_project_id, $t_id, false );
			\Core\Helper::check_disabled( $t_id == $p_filter_project_id || !$t_can_report );
			echo '>' . \Core\String::attribute( \Core\Project::get_field( $t_id, 'name' ) ) . '</option>' . "\n";
			self::subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_can_report_only );
		}
	}
	
	/**
	 * List projects that the current user has access to
	 * @param integer $p_parent_id         A parent project identifier.
	 * @param integer $p_project_id        A project identifier.
	 * @param integer $p_filter_project_id A filter project identifier.
	 * @param boolean $p_trace             Whether to trace parent projects.
	 * @param boolean $p_can_report_only   If true, disables projects in which user can't report issues; defaults to false (all projects enabled).
	 * @param array   $p_parents           Array of parent projects.
	 * @return void
	 */
	static function subproject_option_list( $p_parent_id, $p_project_id = null, $p_filter_project_id = null, $p_trace = false, $p_can_report_only = false, array $p_parents = array() ) {
		array_push( $p_parents, $p_parent_id );
		$t_user_id = \Core\Auth::get_current_user_id();
		$t_project_ids = \Core\User::get_accessible_subprojects( $t_user_id, $p_parent_id );
		$t_can_report = true;
	
		foreach( $t_project_ids as $t_id ) {
			if( $p_can_report_only ) {
				$t_report_bug_threshold = Config::mantis_get( 'report_bug_threshold', null, $t_user_id, $t_id );
				$t_can_report = \Core\Access::has_project_level( $t_report_bug_threshold, $t_id, $t_user_id );
			}
	
			if( $p_trace ) {
				$t_full_id = join( $p_parents, ';' ) . ';' . $t_id;
			} else {
				$t_full_id = $t_id;
			}
	
			echo '<option value="' . $t_full_id . '"';
			\Core\Helper::check_selected( $p_project_id, $t_full_id, false );
			\Core\Helper::check_disabled( $t_id == $p_filter_project_id || !$t_can_report );
			echo '>'
				. str_repeat( '&#160;', count( $p_parents ) )
				. str_repeat( '&raquo;', count( $p_parents ) ) . ' '
				. \Core\String::attribute( \Core\Project::get_field( $t_id, 'name' ) )
				. '</option>' . "\n";
			self::subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_can_report_only, $p_parents );
		}
	}
	
	/**
	 * prints the profiles given the user id
	 * @param integer $p_user_id   A user identifier.
	 * @param integer $p_select_id ID to mark as selected; if 0, gets the user's default profile.
	 * @param array   $p_profiles  Array of profiles.
	 * @return void
	 */
	static function profile_option_list( $p_user_id, $p_select_id = 0, array $p_profiles = null ) {
		if( 0 == $p_select_id ) {
			$p_select_id = \Core\Profile::get_default( $p_user_id );
		}
		if( $p_profiles != null ) {
			$t_profiles = $p_profiles;
		} else {
			$t_profiles = \Core\Profile::get_all_for_user( $p_user_id );
		}
		self::profile_option_list_from_profiles( $t_profiles, $p_select_id );
	}
	
	/**
	 * prints the profiles used in a certain project
	 * @param integer $p_project_id A project identifier.
	 * @param integer $p_select_id  ID to mark as selected; if 0, gets the user's default profile.
	 * @param array   $p_profiles   Array of profiles.
	 * @return void
	 */
	static function profile_option_list_for_project( $p_project_id, $p_select_id = 0, array $p_profiles = null ) {
		if( 0 == $p_select_id ) {
			$p_select_id = \Core\Profile::get_default( \Core\Auth::get_current_user_id() );
		}
		if( $p_profiles != null ) {
			$t_profiles = $p_profiles;
		} else {
			$t_profiles = \Core\Profile::get_all_for_project( $p_project_id );
		}
		self::profile_option_list_from_profiles( $t_profiles, $p_select_id );
	}
	
	/**
	 * print the profile option list from profiles array
	 *
	 * @param array   $p_profiles  Array of Operating System Profiles (ID, platform, os, os_build).
	 * @param integer $p_select_id ID to mark as selected.
	 * @return void
	 */
	static function profile_option_list_from_profiles( array $p_profiles, $p_select_id ) {
		echo '<option value="">' . \Core\Lang::get( 'select_option' ) . '</option>';
		foreach( $p_profiles as $t_profile ) {
			extract( $t_profile, EXTR_PREFIX_ALL, 'v' );
	
			$t_platform = \Core\String::attribute( $t_profile['platform'] );
			$t_os = \Core\String::attribute( $t_profile['os'] );
			$t_os_build = \Core\String::attribute( $t_profile['os_build'] );
	
			echo '<option value="' . $t_profile['id'] . '"';
			if( $p_select_id !== false ) {
				\Core\Helper::check_selected( $p_select_id, (int)$t_profile['id'] );
			}
			echo '>' . $t_platform . ' ' . $t_os . ' ' . $t_os_build . '</option>';
		}
	}
	
	/**
	 * Since categories can be orphaned we need to grab all unique instances of category
	 * We check in the project category table and in the bug table
	 * We put them all in one array and make sure the entries are unique
	 *
	 * @param integer $p_category_id A category identifier.
	 * @param integer $p_project_id  A project identifier.
	 * @return void
	 */
	static function category_option_list( $p_category_id = 0, $p_project_id = null ) {
		if( null === $p_project_id ) {
			$t_project_id = \Core\Helper::get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}
	
		$t_cat_arr = \Core\Category::get_all_rows( $t_project_id, null, true );
	
		if( Config::mantis_get( 'allow_no_category' ) ) {
			echo '<option value="0"';
			\Core\Helper::check_selected( $p_category_id, 0 );
			echo '>';
			echo \Core\Category::full_name( 0, false ), '</option>';
		} else {
			if( 0 == $p_category_id ) {
				if( count( $t_cat_arr ) == 1 ) {
					$p_category_id = (int) $t_cat_arr[0]['id'];
				} else {
					echo '<option value="0"';
					echo \Core\Helper::check_selected( $p_category_id, 0 );
					echo '>';
					echo \Core\String::attribute( \Core\Lang::get( 'select_option' ) ) . '</option>';
				}
			}
		}
	
		foreach( $t_cat_arr as $t_category_row ) {
			$t_category_id = (int)$t_category_row['id'];
			echo '<option value="' . $t_category_id . '"';
			\Core\Helper::check_selected( $p_category_id, $t_category_id );
			echo '>' . \Core\String::attribute( \Core\Category::full_name( $t_category_id, $t_category_row['project_id'] != $t_project_id ) ) . '</option>';
		}
	}
	
	/**
	 * Now that categories are identified by numerical ID, we need an old-style name
	 * based option list to keep existing filter functionality.
	 * @param string       $p_category_name The selected category.
	 * @param integer|null $p_project_id    A specific project or null.
	 * @return void
	 */
	static function category_filter_option_list( $p_category_name = '', $p_project_id = null ) {
		$t_cat_arr = \Core\Category::get_filter_list( $p_project_id );
	
		natcasesort( $t_cat_arr );
		foreach( $t_cat_arr as $t_cat ) {
			$t_name = \Core\String::attribute( $t_cat );
			echo '<option value="' . $t_name . '"';
			\Core\Helper::check_selected( $p_category_name, $t_cat );
			echo '>' . $t_name . '</option>';
		}
	}
	
	/**
	 * Print the option list for platforms accessible for the specified user.
	 * @param string  $p_platform The current platform value.
	 * @param integer $p_user_id  A user identifier.
	 * @return void
	 */
	static function platform_option_list( $p_platform, $p_user_id = null ) {
		$t_platforms_array = \Core\Profile::get_field_all_for_user( 'platform', $p_user_id );
	
		foreach( $t_platforms_array as $t_platform_unescaped ) {
			$t_platform = \Core\String::attribute( $t_platform_unescaped );
			echo '<option value="' . $t_platform . '"';
			\Core\Helper::check_selected( $p_platform, $t_platform_unescaped );
			echo '>' . $t_platform . '</option>';
		}
	}
	
	/**
	 * Print the option list for OSes accessible for the specified user.
	 * @param string  $p_os      The current operating system value.
	 * @param integer $p_user_id A user identifier.
	 * @return void
	 */
	static function os_option_list( $p_os, $p_user_id = null ) {
		$t_os_array = \Core\Profile::get_field_all_for_user( 'os', $p_user_id );
	
		foreach( $t_os_array as $t_os_unescaped ) {
			$t_os = \Core\String::attribute( $t_os_unescaped );
			echo '<option value="' . $t_os . '"';
			\Core\Helper::check_selected( $p_os, $t_os_unescaped );
			echo '>' . $t_os . '</option>';
		}
	}
	
	/**
	 * Print the option list for os_build accessible for the specified user.
	 * @param string  $p_os_build The current operating system build value.
	 * @param integer $p_user_id  A user identifier.
	 * @return void
	 */
	static function os_build_option_list( $p_os_build, $p_user_id = null ) {
		$t_os_build_array = \Core\Profile::get_field_all_for_user( 'os_build', $p_user_id );
	
		foreach( $t_os_build_array as $t_os_build_unescaped ) {
			$t_os_build = \Core\String::attribute( $t_os_build_unescaped );
			echo '<option value="' . $t_os_build . '"';
			\Core\Helper::check_selected( $p_os_build, $t_os_build_unescaped );
			echo '>' . $t_os_build . '</option>';
		}
	}
	
	/**
	 * Print the option list for versions
	 * @param string  $p_version       The currently selected version.
	 * @param integer $p_project_id    Project id, otherwise current project will be used.
	 * @param integer $p_released      Null to get all, 1: only released, 0: only future versions.
	 * @param boolean $p_leading_blank Allow selection of no version.
	 * @param boolean $p_with_subs     Whether to include sub-projects.
	 * @return void
	 */
	static function version_option_list( $p_version = '', $p_project_id = null, $p_released = null, $p_leading_blank = true, $p_with_subs = false ) {
		if( null === $p_project_id ) {
			$c_project_id = \Core\Helper::get_current_project();
		} else {
			$c_project_id = (int)$p_project_id;
		}
	
		if( $p_with_subs ) {
			$t_versions = \Core\Version::get_all_rows_with_subs( $c_project_id, $p_released, null );
		} else {
			$t_versions = \Core\Version::get_all_rows( $c_project_id, $p_released, null );
		}
	
		# Ensure the selected version (if specified) is included in the list
		# Note: Filter API specifies selected versions as an array
		if( !is_array( $p_version ) ) {
			if( !empty( $p_version ) ) {
				$t_version_id = \Core\Version::get_id( $p_version, $c_project_id );
				if( $t_version_id !== false ) {
					$t_versions[] = \Core\Version::cache_row( $t_version_id );
				}
			}
		}
	
		if( $p_leading_blank ) {
			echo '<option value=""></option>';
		}
	
		$t_listed = array();
		$t_max_length = Config::mantis_get( 'max_dropdown_length' );
		$t_show_version_dates = \Core\Access::has_project_level( Config::mantis_get( 'show_version_dates_threshold' ) );
		$t_short_date_format = Config::mantis_get( 'short_date_format' );
	
		foreach( $t_versions as $t_version ) {
			# If the current version is obsolete, and current version not equal to $p_version,
			# then skip it.
			if( ( (int)$t_version['obsolete'] ) == 1 ) {
				if( $t_version['version'] != $p_version ) {
					continue;
				}
			}
	
			$t_version_version = \Core\String::attribute( $t_version['version'] );
	
			if( !in_array( $t_version_version, $t_listed, true ) ) {
				$t_listed[] = $t_version_version;
				echo '<option value="' . $t_version_version . '"';
				\Core\Helper::check_selected( $p_version, $t_version['version'] );
	
				$t_version_string = \Core\String::attribute( \Core\Prepare::version_string( $c_project_id, $t_version['id'] ) );
	
				echo '>', \Core\String::shorten( $t_version_string, $t_max_length ), '</option>';
			}
		}
	}
	
	/**
	 * print build option list
	 * @param string $p_build The current build value.
	 * @return void
	 */
	static function build_option_list( $p_build = '' ) {
		$t_overall_build_arr = array();
	
		$t_project_id = \Core\Helper::get_current_project();
	
		$t_project_where = \Core\Helper::project_specific_where( $t_project_id );
	
		# Get the "found in" build list
		$t_query = 'SELECT DISTINCT build
					FROM {bug}
					WHERE ' . $t_project_where . '
					ORDER BY build DESC';
		$t_result = \Core\Database::query( $t_query );
	
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_overall_build_arr[] = $t_row['build'];
		}
	
		$t_max_length = Config::mantis_get( 'max_dropdown_length' );
	
		foreach( $t_overall_build_arr as $t_build_unescaped ) {
			$t_build = \Core\String::attribute( $t_build_unescaped );
			echo '<option value="' . $t_build . '"';
			\Core\Helper::check_selected( $p_build, $t_build_unescaped );
			echo '>' . \Core\String::shorten( $t_build, $t_max_length ) . '</option>';
		}
	}
	
	/**
	 * select the proper enumeration values based on the input parameter
	 * @param string  $p_enum_name Name of enumeration (eg: status).
	 * @param integer $p_val       The current value.
	 * @return void
	 */
	static function enum_string_option_list( $p_enum_name, $p_val = 0 ) {
		$t_config_var_name = $p_enum_name . '_enum_string';
		$t_config_var_value = Config::mantis_get( $t_config_var_name );
	
		$t_enum_values = Enum::getValues( $t_config_var_value );
	
		foreach ( $t_enum_values as $t_key ) {
			$t_elem2 = \Core\Helper::get_enum_element( $p_enum_name, $t_key );
	
			echo '<option value="' . $t_key . '"';
			\Core\Helper::check_selected( (int)$p_val, $t_key );
			echo '>' . \Core\String::html_specialchars( $t_elem2 ) . '</option>';
		}
	}
	
	/**
	 * Select the proper enumeration values for status based on workflow
	 * or the input parameter if workflows are not used
	 * @param integer $p_user_auth     A user identifier.
	 * @param integer $p_current_value The current value.
	 * @param boolean $p_show_current  Whether to show the current status.
	 * @param boolean $p_add_close     Whether to add close option.
	 * @param integer $p_project_id    A project identifier.
	 * @return array
	 */
	static function get_status_option_list( $p_user_auth = 0, $p_current_value = 0, $p_show_current = true, $p_add_close = false, $p_project_id = ALL_PROJECTS ) 
	{
		$t_config_var_value = Config::mantis_get( 'status_enum_string', null, null, $p_project_id );
	
		if(Config::mantis_get( 'status_enum_workflow', null, null, $p_project_id )) 
		{
			# workflow defined - find allowed states
			if (isset($t_enum_workflow[$p_current_value])) 
			{
				$t_enum_values = Enum::getValues($t_enum_workflow[$p_current_value]);
			}
			else
			{
				# workflow was not set for this status, this shouldn't happen
				# caller should be able to handle empty list
				$t_enum_values = array();
			}
		}
		else
		{
			# workflow not defined, use default enumeration
			$t_enum_values = Enum::getValues( $t_config_var_value );
		}
		
		$t_enum_list = array();
	
		foreach ( $t_enum_values as $t_enum_value ) {
			if( ( $p_show_current || $p_current_value != $t_enum_value )
				&& \Core\Access::compare_level( $p_user_auth, \Core\Access::get_status_threshold( $t_enum_value, $p_project_id ) )
			) {
				$t_enum_list[$t_enum_value] = \Core\Helper::get_enum_element( 'status', $t_enum_value );
			}
		}
	
		if( $p_show_current ) {
			$t_enum_list[$p_current_value] = \Core\Helper::get_enum_element( 'status', $p_current_value );
		}
	
		if( $p_add_close && \Core\Access::compare_level( $p_current_value, Config::mantis_get( 'bug_resolved_status_threshold', null, null, $p_project_id ) ) ) {
			$t_closed = Config::mantis_get( 'bug_closed_status_threshold', null, null, $p_project_id );
			if( $p_show_current || $p_current_value != $t_closed ) {
				$t_enum_list[$t_closed] = \Core\Helper::get_enum_element( 'status', $t_closed );
			}
		}
	
		return $t_enum_list;
	}
	
	/**
	 * print the status option list for the bug_update pages
	 * @param string  $p_select_label  The id/name html attribute of the select box.
	 * @param integer $p_current_value The current value.
	 * @param boolean $p_allow_close   Whether to allow close.
	 * @param integer $p_project_id    A project identifier.
	 * @return void
	 */
	static function status_option_list( $p_select_label, $p_current_value = 0, $p_allow_close = false, $p_project_id = ALL_PROJECTS ) {
		$t_current_auth = \Core\Access::get_project_level( $p_project_id );
	
		$t_enum_list = self::get_status_option_list( $t_current_auth, $p_current_value, true, $p_allow_close, $p_project_id );
	
		if( count( $t_enum_list ) > 1 ) {
			# resort the list into ascending order
			ksort( $t_enum_list );
			reset( $t_enum_list );
			echo '<select ' . \Core\Helper::get_tab_index() . ' id="' . $p_select_label . '" name="' . $p_select_label . '">';
			foreach( $t_enum_list as $t_key => $t_val ) {
				echo '<option value="' . $t_key . '"';
				\Core\Helper::check_selected( $t_key, $p_current_value );
				echo '>' . \Core\String::html_specialchars( $t_val ) . '</option>';
			}
			echo '</select>';
		} else if( count( $t_enum_list ) == 1 ) {
			echo array_pop( $t_enum_list );
		} else {
			echo Enum::getLabel( \Core\Lang::get( 'status_enum_string' ), $p_current_value );
		}
	}
	
	/**
	 * prints the list of a project's users
	 * if no project is specified uses the current project
	 * @param integer $p_project_id A project identifier.
	 * @return void
	 */
	static function project_user_option_list( $p_project_id = null ) {
		self::user_option_list( 0, $p_project_id );
	}
	
	/**
	 * prints the list of access levels that are less than or equal to the access level of the
	 * logged in user.  This is used when adding users to projects
	 * @param integer $p_val        The current value.
	 * @param integer $p_project_id A project identifier.
	 * @return void
	 */
	static function project_access_levels_option_list( $p_val, $p_project_id = null ) {
		$t_current_user_access_level = \Core\Access::get_project_level( $p_project_id );
		$t_access_levels_enum_string = Config::mantis_get( 'access_levels_enum_string' );
		$t_enum_values = Enum::getValues( $t_access_levels_enum_string );
		foreach ( $t_enum_values as $t_enum_value ) {
			# a user must not be able to assign another user an access level that is higher than theirs.
			if( $t_enum_value > $t_current_user_access_level ) {
				continue;
			}
			$t_access_level = \Core\Helper::get_enum_element( 'access_levels', $t_enum_value );
			echo '<option value="' . $t_enum_value . '"';
			\Core\Helper::check_selected( $p_val, $t_enum_value );
			echo '>' . \Core\String::html_specialchars( $t_access_level ) . '</option>';
		}
	}
	
	/**
	 * Print option list of available language choices
	 * @param string $p_language The current language.
	 * @return void
	 */
	static function language_option_list( $p_language ) {
		$t_arr = Config::mantis_get( 'language_choices_arr' );
		$t_enum_count = count( $t_arr );
		for( $i = 0;$i < $t_enum_count;$i++ ) {
			$t_language = \Core\String::attribute( $t_arr[$i] );
			echo '<option value="' . $t_language . '"';
			\Core\Helper::check_selected( $t_language, $p_language );
			echo '>' . $t_language . '</option>';
		}
	}
	
	/**
	 * Print a dropdown list of all bug actions available to a user for a specified
	 * set of projects.
	 * @param array $p_project_ids An array containing one or more project IDs.
	 * @return void
	 */
	static function all_bug_action_option_list( array $p_project_ids = null ) {
		$t_commands = \Core\Bug\Group::action_get_commands( $p_project_ids );
		while( list( $t_action_id, $t_action_label ) = each( $t_commands ) ) {
			echo '<option value="' . $t_action_id . '">' . $t_action_label . '</option>';
		}
	}
	
	/**
	 * list of users that are NOT in the specified project and that are enabled
	 * if no project is specified use the current project
	 * also exclude any administrators
	 * @param integer $p_project_id A project identifier.
	 * @return void
	 */
	static function project_user_list_option_list( $p_project_id = null ) {
		$t_users = \Core\User::get_unassigned_by_project_id( $p_project_id );
		foreach( $t_users as $t_id=>$t_name ) {
			echo '<option value="' . $t_id . '">' . $t_name . '</option>';
		}
	}
	
	/**
	 * list of projects that a user is NOT in
	 * @param integer $p_user_id An user identifier.
	 * @return void
	 */
	static function project_user_list_option_list2( $p_user_id ) {
		$t_query = 'SELECT DISTINCT p.id, p.name
					FROM {project} p
					LEFT JOIN {project_user_list} u
					ON p.id=u.project_id AND u.user_id=' . \Core\Database::param() . '
					WHERE p.enabled = ' . \Core\Database::param() . ' AND
						u.user_id IS NULL
					ORDER BY p.name';
		$t_result = \Core\Database::query( $t_query, array( (int)$p_user_id, true ) );
		$t_category_count = \Core\Database::num_rows( $t_result );
		while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
			$t_project_name = \Core\String::attribute( $t_row['name'] );
			$t_user_id = $t_row['id'];
			echo '<option value="' . $t_user_id . '">' . $t_project_name . '</option>';
		}
	}
	
	/**
	 * list of projects that a user is in
	 * @param integer $p_user_id             An user identifier.
	 * @param boolean $p_include_remove_link Whether to display remove link.
	 * @return void
	 */
	static function project_user_list( $p_user_id, $p_include_remove_link = true ) {
		$t_projects = \Core\User::get_assigned_projects( $p_user_id );
	
		foreach( $t_projects as $t_project_id=>$t_project ) {
			$t_project_name = \Core\String::attribute( $t_project['name'] );
			$t_view_state = $t_project['view_state'];
			$t_access_level = $t_project['access_level'];
			$t_access_level = \Core\Helper::get_enum_element( 'access_levels', $t_access_level );
			$t_view_state = \Core\Helper::get_enum_element( 'project_view_state', $t_view_state );
	
			echo $t_project_name . ' [' . $t_access_level . '] (' . $t_view_state . ')';
			if( $p_include_remove_link && \Core\Access::has_project_level( Config::mantis_get( 'project_user_threshold' ), $t_project_id ) ) {
				\Core\HTML::button( 'manage_user_proj_delete.php', \Core\Lang::get( 'remove_link' ), array( 'project_id' => $t_project_id, 'user_id' => $p_user_id ) );
			}
			echo '<br />';
		}
	}
	
	/**
	 * List of projects with which the specified field id is linked.
	 * For every project, the project name is listed and then the list of custom
	 * fields linked in order with their sequence numbers.  The specified field
	 * is always highlighted in italics and project names in bold.
	 *
	 * @param integer $p_field_id The field to list the projects associated with.
	 * @return void
	 */
	static function custom_field_projects_list( $p_field_id ) {
		$c_field_id = (integer)$p_field_id;
		$t_project_ids = custom_field_get_project_ids( $p_field_id );
	
		$t_security_token = \Core\Form::security_param( 'manage_proj_custom_field_remove' );
	
		foreach( $t_project_ids as $t_project_id ) {
			$t_project_name = \Core\Project::get_field( $t_project_id, 'name' );
			$t_sequence = custom_field_get_sequence( $p_field_id, $t_project_id );
			echo '<strong>', \Core\String::display_line( $t_project_name ), '</strong>: ';
			self::bracket_link( 'manage_proj_custom_field_remove.php?field_id=' . $c_field_id . '&project_id=' . $t_project_id . '&return=custom_field' . $t_security_token, \Core\Lang::get( 'remove_link' ) );
			echo '<br />- ';
	
			$t_linked_field_ids = custom_field_get_linked_ids( $t_project_id );
	
			$t_first = true;
			foreach( $t_linked_field_ids as $t_current_field_id ) {
				if( $t_first ) {
					$t_first = false;
				} else {
					echo ', ';
				}
	
				if( $t_current_field_id == $p_field_id ) {
					echo '<em>';
				}
	
				echo \Core\String::display_line( custom_field_get_field( $t_current_field_id, 'name' ) );
				echo ' (', custom_field_get_sequence( $t_current_field_id, $t_project_id ), ')';
	
				if( $t_current_field_id == $p_field_id ) {
					echo '</em>';
				}
			}
	
			echo '<br /><br />';
		}
	}
	
	/**
	 * List of priorities that can be assigned to a plugin.
	 * @param integer $p_priority Current priority.
	 * @return void
	 */
	static function plugin_priority_list( $p_priority ) {
		if( $p_priority < 1 && $p_priority > 5 ) {
			echo '<option value="', $p_priority, '" selected="selected">', $p_priority, '</option>';
		}
	
		for( $i = 5;$i >= 1;$i-- ) {
			echo '<option value="', $i, '" ', \Core\Helper::check_selected( $p_priority, $i ), ' >', $i, '</option>';
		}
	}
	
	/**
	 * prints a link to VIEW a bug given an ID
	 *  account for the user preference and site override
	 * @param integer $p_bug_id      A bug identifier.
	 * @param boolean $p_detail_info Detail info to display with the link.
	 * @return void
	 */
	static function bug_link( $p_bug_id, $p_detail_info = true ) {
		echo \Core\String::get_bug_view_link( $p_bug_id, null, $p_detail_info );
	}
	
	/**
	 * formats the priority given the status
	 * shows the priority in BOLD if the bug is NOT closed and is of significant priority
	 * @param \Core\BugData $p_bug Bug Object.
	 * @return void
	 */
	static function formatted_priority_string( \Core\BugData $p_bug ) {
		$t_pri_str = \Core\Helper::get_enum_element( 'priority', $p_bug->priority, \Core\Auth::get_current_user_id(), $p_bug->project_id );
		$t_priority_threshold = Config::mantis_get( 'priority_significant_threshold' );
	
		if( $t_priority_threshold >= 0 &&
			$p_bug->priority >= $t_priority_threshold &&
			$p_bug->status < Config::mantis_get( 'bug_closed_status_threshold' ) ) {
			echo '<span class="bold">' . $t_pri_str . '</span>';
		} else {
			echo $t_pri_str;
		}
	}
	
	/**
	 * formats the severity given the status
	 * shows the severity in BOLD if the bug is NOT closed and is of significant severity
	 * @param \Core\BugData $p_bug Bug Object.
	 * @return void
	 */
	static function formatted_severity_string( \Core\BugData $p_bug ) {
		$t_sev_str = \Core\Helper::get_enum_element( 'severity', $p_bug->severity, \Core\Auth::get_current_user_id(), $p_bug->project_id );
		$t_severity_threshold = Config::mantis_get( 'severity_significant_threshold' );
	
		if( $t_severity_threshold >= 0 &&
			$p_bug->severity >= $t_severity_threshold &&
			$p_bug->status < Config::mantis_get( 'bug_closed_status_threshold' ) ) {
			echo '<span class="bold">' . $t_sev_str . '</span>';
		} else {
			echo $t_sev_str;
		}
	}
	
	/**
	 * Print view bug sort link
	 * @todo params should be in same order as print_manage_user_sort_link
	 * @param string  $p_string         The displayed text of the link.
	 * @param string  $p_sort_field     The field to sort.
	 * @param string  $p_sort           The field to sort by.
	 * @param string  $p_dir            The sort direction - either ASC or DESC.
	 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
	 * @return void
	 */
	static function view_bug_sort_link( $p_string, $p_sort_field, $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if( $p_columns_target == COLUMNS_TARGET_PRINT_PAGE ) {
			if( $p_sort_field == $p_sort ) {
				# We toggle between ASC and DESC if the user clicks the same sort order
				if( 'ASC' == $p_dir ) {
					$p_dir = 'DESC';
				} else {
					$p_dir = 'ASC';
				}
			} else {
				# Otherwise always start with ascending
				$p_dir = 'ASC';
			}
	
			$t_sort_field = rawurlencode( $p_sort_field );
			self::link( 'view_all_set.php?sort=' . $t_sort_field . '&dir=' . $p_dir . '&type=2&print=1', $p_string );
		} else if( $p_columns_target == COLUMNS_TARGET_VIEW_PAGE ) {
			if( $p_sort_field == $p_sort ) {
	
				# we toggle between ASC and DESC if the user clicks the same sort order
				if( 'ASC' == $p_dir ) {
					$p_dir = 'DESC';
				} else {
					$p_dir = 'ASC';
				}
			} else {
				# Otherwise always start with ascending
				$p_dir = 'ASC';
			}
			$t_sort_field = rawurlencode( $p_sort_field );
			self::link( 'view_all_set.php?sort=' . $t_sort_field . '&dir=' . $p_dir . '&type=2', $p_string );
		} else {
			echo $p_string;
		}
	}
	
	/**
	 * Print manage user sort link
	 * @param string  $p_page          The page within mantis to link to.
	 * @param string  $p_string        The displayed text of the link.
	 * @param string  $p_field         The field to sort.
	 * @param string  $p_dir           The sort direction - either ASC or DESC.
	 * @param string  $p_sort_by       The field to sort by.
	 * @param integer $p_hide_inactive Whether to hide inactive users.
	 * @param integer $p_filter        The filter to use.
	 * @param integer $p_show_disabled Whether to show disabled users.
	 * @return void
	 */
	static function manage_user_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by, $p_hide_inactive = 0, $p_filter = ALL, $p_show_disabled = 0 ) {
		if( $p_sort_by == $p_field ) {
			# If this is the selected field flip the order
			if( 'ASC' == $p_dir || ASCENDING == $p_dir ) {
				$t_dir = 'DESC';
			} else {
				$t_dir = 'ASC';
			}
		} else {
			# Otherwise always start with ASCending
			$t_dir = 'ASC';
		}
	
		$t_field = rawurlencode( $p_field );
		self::link( $p_page . '?sort=' . $t_field . '&dir=' . $t_dir . '&save=1&hideinactive=' . $p_hide_inactive . '&showdisabled=' . $p_show_disabled . '&filter=' . $p_filter, $p_string );
	}
	
	/**
	 * Print manage project sort link
	 * @param string $p_page    The page within mantis to link to.
	 * @param string $p_string  The displayed text of the link.
	 * @param string $p_field   The field to sort.
	 * @param string $p_dir     The sort direction - either ASC or DESC.
	 * @param string $p_sort_by The field to sort by.
	 * @return void
	 */
	static function manage_project_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by ) {
		if( $p_sort_by == $p_field ) {
			# If this is the selected field flip the order
			if( 'ASC' == $p_dir || ASCENDING == $p_dir ) {
				$t_dir = 'DESC';
			} else {
				$t_dir = 'ASC';
			}
		} else {
			# Otherwise always start with ASCending
			$t_dir = 'ASC';
		}
	
		$t_field = rawurlencode( $p_field );
		self::link( $p_page . '?sort=' . $t_field . '&dir=' . $t_dir, $p_string );
	}
	
	/**
	 * Print a button which presents a standalone form.
	 * If $p_security_token is OFF, the button's form will not contain a security
	 * field; this is useful when form does not result in modifications (CSRF is not
	 * needed). If otherwise specified (i.e. not null), the parameter must contain
	 * a valid security token, previously generated by \Core\Form::security_token().
	 * Use this to avoid performance issues when loading pages having many calls to
	 * this function, such as adm_config_report.php.
	 * @param string $p_action_page    The action page.
	 * @param string $p_label          The button label.
	 * @param array  $p_args_to_post   Associative array of arguments to be posted, with
	 *                                 arg name => value, defaults to null (no args).
	 * @param mixed  $p_security_token Optional; null (default), OFF or security token string.
	 * @see \Core\Form::security_token()
	 * @return void
	 */
	static function button( $p_action_page, $p_label, array $p_args_to_post = null, $p_security_token = null ) {
		$t_form_name = explode( '.php', $p_action_page, 2 );
		# TODO: ensure all uses of print_button supply arguments via $p_args_to_post (POST)
		# instead of via $p_action_page (GET). Then only add the CSRF form token if
		# arguments are being sent via the POST method.
		echo '<form method="post" action="', htmlspecialchars( $p_action_page ), '" class="action-button">';
		echo '<fieldset>';
		if( $p_security_token !== OFF ) {
			echo \Core\Form::security_field( $t_form_name[0], $p_security_token );
		}
		echo '<input type="submit" class="button-small" value="', $p_label, '" />';
	
		if( $p_args_to_post !== null ) {
			foreach( $p_args_to_post as $t_var => $t_value ) {
				echo '<input type="hidden" name="' . $t_var .
					'" value="' . htmlentities( $t_value ) . '" />';
			}
		}
	
		echo '</fieldset>';
		echo '</form>';
	}
	
	/**
	 * print brackets around a pre-prepared link (i.e. '<a href' html tag).
	 * @param string $p_link The URL to link to.
	 * @return void
	 */
	static function bracket_link_prepared( $p_link ) {
		echo '<span class="bracket-link">[&#160;' . $p_link . '&#160;]</span> ';
	}
	
	/**
	 * print the bracketed links used near the top
	 * if the $p_link is blank then the text is printed but no link is created
	 * if $p_new_window is true, link will open in a new window, default false.
	 * @param string  $p_link       The URL to link to.
	 * @param string  $p_url_text   The text to display.
	 * @param boolean $p_new_window Whether to open in a new window.
	 * @param string  $p_class      CSS class to use with the link.
	 * @return void
	 */
	static function bracket_link( $p_link, $p_url_text, $p_new_window = false, $p_class = '' ) {
		echo '<span class="bracket-link';
		if( $p_class !== '' ) {
			echo ' bracket-link-',$p_class; # prefix on a container allows styling of whole link, including brackets
		}
		echo '">[&#160;';
		self::link( $p_link, $p_url_text, $p_new_window, $p_class );
		echo '&#160;]</span> ';
	}
	
	/**
	 * print a HTML link
	 * @param string  $p_link       The page URL.
	 * @param string  $p_url_text   The displayed text for the link.
	 * @param boolean $p_new_window Whether to open in a new window.
	 * @param string  $p_class      The CSS class of the link.
	 * @return void
	 */
	static function link( $p_link, $p_url_text, $p_new_window = false, $p_class = '' ) {
		if( \Core\Utility::is_blank( $p_link ) ) {
			echo $p_url_text;
		} else {
			$t_link = htmlspecialchars( $p_link );
			if( $p_new_window === true ) {
				echo '<a class="new-window ' . $p_class . '" href="' . $t_link . '" target="_blank">' . $p_url_text . '</a>';
			} else {
				if( $p_class !== '' ) {
					echo '<a class="' . $p_class . '" href="' . $t_link . '">' . $p_url_text . '</a>';
				} else {
					echo '<a href="' . $t_link . '">' . $p_url_text . '</a>';
				}
			}
		}
	}
	
	/**
	 * print a HTML page link
	 * @param string  $p_page_url       The Page URL.
	 * @param string  $p_text           The displayed text for the link.
	 * @param integer $p_page_no        The page number to link to.
	 * @param integer $p_page_cur       The current page number.
	 * @param integer $p_temp_filter_id Temporary filter id.
	 * @return void
	 */
	static function page_link( $p_page_url, $p_text = '', $p_page_no = 0, $p_page_cur = 0, $p_temp_filter_id = 0 ) {
		if( \Core\Utility::is_blank( $p_text ) ) {
			$p_text = $p_page_no;
		}
	
		if( ( 0 < $p_page_no ) && ( $p_page_no != $p_page_cur ) ) {
			$t_delimiter = ( strpos( $p_page_url, '?' ) ? '&' : '?' );
			if( $p_temp_filter_id !== 0 ) {
				self::link( $p_page_url . $t_delimiter . 'filter=' . $p_temp_filter_id . '&page_number=' . $p_page_no, $p_text );
			} else {
				self::link( $p_page_url . $t_delimiter . 'page_number=' . $p_page_no, $p_text );
			}
		} else {
			echo $p_text;
		}
	}
	
	/**
	 * print a list of page number links (eg [1 2 3])
	 * @param string  $p_page           The Page URL.
	 * @param integer $p_start          The first page number.
	 * @param integer $p_end            The last page number.
	 * @param integer $p_current        The current page number.
	 * @param integer $p_temp_filter_id Temporary filter id.
	 * @return void
	 */
	static function page_links( $p_page, $p_start, $p_end, $p_current, $p_temp_filter_id = 0 ) {
		$t_items = array();
	
		# Check if we have more than one page,
		#  otherwise return without doing anything.
	
		if( $p_end - $p_start < 1 ) {
			return;
		}
	
		# Get localized strings
		$t_first = \Core\Lang::get( 'first' );
		$t_last = \Core\Lang::get( 'last' );
		$t_prev = \Core\Lang::get( 'prev' );
		$t_next = \Core\Lang::get( 'next' );
	
		$t_page_links = 10;
	
		print( '[ ' );
	
		# First and previous links
		self::page_link( $p_page, $t_first, 1, $p_current, $p_temp_filter_id );
		echo '&#160;';
		self::page_link( $p_page, $t_prev, $p_current - 1, $p_current, $p_temp_filter_id );
		echo '&#160;';
	
		# Page numbers ...
	
		$t_first_page = max( $p_start, $p_current - $t_page_links / 2 );
		$t_first_page = min( $t_first_page, $p_end - $t_page_links );
		$t_first_page = max( $t_first_page, $p_start );
	
		if( $t_first_page > 1 ) {
			print( ' ... ' );
		}
	
		$t_last_page = $t_first_page + $t_page_links;
		$t_last_page = min( $t_last_page, $p_end );
	
		for( $i = $t_first_page;$i <= $t_last_page;$i++ ) {
			if( $i == $p_current ) {
				array_push( $t_items, $i );
			} else {
				$t_delimiter = ( strpos( $p_page, '?' ) ? '&' : '?' ) ;
				if( $p_temp_filter_id !== 0 ) {
					array_push( $t_items, '<a href="' . $p_page . $t_delimiter . 'filter=' . $p_temp_filter_id . '&amp;page_number=' . $i . '">' . $i . '</a>' );
				} else {
					array_push( $t_items, '<a href="' . $p_page . $t_delimiter . 'page_number=' . $i . '">' . $i . '</a>' );
				}
			}
		}
		echo implode( '&#160;', $t_items );
	
		if( $t_last_page < $p_end ) {
			print( ' ... ' );
		}
	
		# Next and Last links
		echo '&#160;';
		if( $p_current < $p_end ) {
			self::page_link( $p_page, $t_next, $p_current + 1, $p_current, $p_temp_filter_id );
		} else {
			self::page_link( $p_page, $t_next, null, null, $p_temp_filter_id );
		}
		echo '&#160;';
		self::page_link( $p_page, $t_last, $p_end, $p_current, $p_temp_filter_id );
	
		print( ' ]' );
	}
	
	/**
	 * print a mailto: href link
	 *
	 * @param string $p_email Email Address.
	 * @param string $p_text  Link text to display to user.
	 * @return void
	 */
	static function email_link( $p_email, $p_text ) {
		echo self::get_email_link( $p_email, $p_text );
	}
	
	/**
	 * return the mailto: href string link instead of printing it
	 *
	 * @param string $p_email Email Address.
	 * @param string $p_text  Link text to display to user.
	 * @return string
	 */
	static function get_email_link( $p_email, $p_text ) {
		return \Core\Prepare::email_link( $p_email, $p_text );
	}
	
	/**
	 * print a mailto: href link with subject
	 *
	 * @param string $p_email  Email Address.
	 * @param string $p_text   Link text to display to user.
	 * @param string $p_bug_id The bug identifier.
	 * @return void
	 */
	static function email_link_with_subject( $p_email, $p_text, $p_bug_id ) {
		$t_bug = \Core\Bug::get( $p_bug_id, true );
		if( !\Core\Access::has_project_level( Config::mantis_get( 'show_user_email_threshold', null, null, $t_bug->project_id ), $t_bug->project_id ) ) {
			echo $p_text;
			return;
		}
		$t_subject = \Core\Email::build_subject( $p_bug_id );
		echo self::get_email_link_with_subject( $p_email, $p_text, $t_subject );
	}
	
	/**
	 * return the mailto: href string link instead of printing it
	 * add subject line
	 *
	 * @param string $p_email   Email Address.
	 * @param string $p_text    Link text to display to user.
	 * @param string $p_subject Email subject line.
	 * @return string
	 */
	static function get_email_link_with_subject( $p_email, $p_text, $p_subject ) {
		# If we apply \Core\String::url() to the whole mailto: link then the @
		# gets turned into a %40 and you can't right click in browsers to
		# do Copy Email Address.  If we don't apply \Core\String::url() to the
		# subject text then an ampersand (for example) will truncate the text
		$t_subject = \Core\String::url( $p_subject );
		$t_email = \Core\String::url( $p_email );
		$t_mailto = \Core\String::attribute( 'mailto:' . $t_email . '?subject=' . $t_subject );
		$t_text = \Core\String::display( $p_text );
	
		return '<a class="user" href="' . $t_mailto . '">' . $t_text . '</a>';
	}
	
	/**
	 * Print a hidden input for each name=>value pair in the array
	 *
	 * If a value is an array an input will be created for each item with a name
	 *  that ends with []
	 * The names and values are passed through htmlspecialchars() before being displayed
	 *
	 * @param array $p_assoc_array Array of Name/Value pairs for html input tags.
	 * @return void
	 */
	static function hidden_inputs( array $p_assoc_array ) {
		foreach( $p_assoc_array as $t_key => $t_val ) {
			self::hidden_input( $t_key, $t_val );
		}
	}
	
	/**
	 * Print hidden html input tag <input type=hidden>
	 *
	 * @param string $p_field_key Name parameter.
	 * @param string $p_field_val Value parameter.
	 * @return void
	 */
	static function hidden_input( $p_field_key, $p_field_val ) {
		if( is_array( $p_field_val ) ) {
			foreach( $p_field_val as $t_key => $t_value ) {
				if( is_array( $t_value ) ) {
					$t_key = \Core\String::html_entities( $t_key );
					$t_field_key = $p_field_key . '[' . $t_key . ']';
					self::hidden_input( $t_field_key, $t_value );
				} else {
					$t_field_key = $p_field_key . '[' . $t_key . ']';
					self::hidden_input( $t_field_key, $t_value );
				}
			}
		} else {
			$t_key = \Core\String::html_entities( $p_field_key );
			$t_val = \Core\String::html_entities( $p_field_val );
			echo '<input type="hidden" name="' . $t_key . '" value="' . $t_val . '" />' . "\n";
		}
	}
	
	/**
	 * This prints the little [?] link for user help
	 * @param string $p_a_name The anchor to use when accessing the documentation.
	 * @return void
	 */
	static function documentation_link( $p_a_name = '' ) {
		echo \Core\Lang::get( $p_a_name );
		# @todo Disable documentation links for now.  May be re-enabled if linked to new manual.
		# echo "<a href=\"doc/documentation.html#$p_a_name\" target=\"_info\">[?]</a>";
	}
	
	/**
	 * prints the sign up link
	 * @return void
	 */
	static function signup_link() {
		if( ( ON == Config::get_global( 'allow_signup' ) ) &&
			 ( LDAP != Config::get_global( 'login_method' ) ) &&
			 ( ON == Config::mantis_get( 'enable_email_notification' ) )
		   ) {
			self::bracket_link( 'signup_page.php', \Core\Lang::get( 'signup_link' ) );
		}
	}
	
	/**
	 * prints the login link
	 * @return void
	 */
	static function login_link() {
		self::bracket_link( 'login_page.php', \Core\Lang::get( 'login_title' ) );
	}
	
	/**
	 * prints the lost password link
	 * @return void
	 */
	static function lost_password_link() {
		# lost password feature disabled or reset password via email disabled -> stop here!
		if( ( LDAP != Config::get_global( 'login_method' ) ) &&
			 ( ON == Config::mantis_get( 'lost_password_feature' ) ) &&
			 ( ON == Config::mantis_get( 'send_reset_password' ) ) &&
			 ( ON == Config::mantis_get( 'enable_email_notification' ) ) ) {
			self::bracket_link( 'lost_pwd_page.php', \Core\Lang::get( 'lost_password_link' ) );
		}
	}
	
	/**
	 * Get icon corresponding to the specified filename
	 *
	 * @param string $p_filename Filename for which to retrieve icon link.
	 * @return void
	 */
	static function file_icon( $p_filename ) {
		$t_icon = \Core\File::get_icon_url( $p_filename );
		echo '<img src="' . \Core\String::attribute( $t_icon['url'] ) . '" alt="' . \Core\String::attribute( $t_icon['alt'] ) . ' file icon" width="16" height="16" />';
	}
	
	/**
	 * Prints an RSS image that is hyperlinked to an RSS feed.
	 *
	 * @param string $p_feed_url URI to an RSS feed.
	 * @param string $p_title    Title to use for hyperlink.
	 * @return void
	 */
	static function rss( $p_feed_url, $p_title = '' ) {
		$t_path = Config::mantis_get( 'path' );
		echo '<a class="rss" rel="alternate" href="', htmlspecialchars( $p_feed_url ), '" title="', $p_title, '"><img src="', $t_path, '/images/', 'rss.png" width="16" height="16" alt="', $p_title, '" /></a>';
	}
	
	/**
	 * Prints the recently visited issues.
	 * @return void
	 */
	static function recently_visited() {
		$t_ids = \Core\Last_Visited::get_array();
		
		if( count( $t_ids ) == 0 ) {
			return;
		}
	
		echo '<div class="recently-visited">' . \Core\Lang::get( 'recently_visited' ) . ': ';
		$t_first = true;
	
		foreach( $t_ids as $t_id ) {
			if( !$t_first ) {
				echo ', ';
			} else {
				$t_first = false;
			}
	
			echo \Core\String::get_bug_view_link( $t_id );
		}
		echo '</div>';
	}
	
	/**
	 * print a drop down box from input array
	 * @param array        $p_control_array Array of elements in drop down list (name, description).
	 * @param string       $p_control_name  Name attribute of <select> box.
	 * @param string|array $p_match	        Either a string or an array of selected values.
	 * @param boolean      $p_add_any       Whether to display an '[any]' option in the drop down.
	 * @param boolean      $p_multiple      Whether drop down list allows multiple values to be selected.
	 * @return string
	 */
	static function get_dropdown( array $p_control_array, $p_control_name, $p_match = '', $p_add_any = false, $p_multiple = false ) {
		if( $p_multiple ) {
			$t_size = ' size="5"';
			$t_multiple = ' multiple="multiple"';
		} else {
			$t_size = '';
			$t_multiple = '';
		}
		$t_info = sprintf( '<select %s name="%s" id="%s"%s>', $t_multiple, $p_control_name, $p_control_name, $t_size );
		if( $p_add_any ) {
			array_unshift_assoc( $p_control_array, META_FILTER_ANY, lang_trans( '[any]' ) );
		}
		while( list( $t_name, $t_desc ) = each( $p_control_array ) ) {
			$t_sel = '';
			if( is_array( $p_match ) ) {
				if( in_array( $t_name, array_values( $p_match ) ) || in_array( $t_desc, array_values( $p_match ) ) ) {
					$t_sel = ' selected="selected"';
				}
			} else {
				if( ( $t_name === $p_match ) || ( $t_desc === $p_match ) ) {
					$t_sel = ' selected="selected"';
				}
			}
			$t_info .= sprintf( '<option%s value="%s">%s</option>', $t_sel, $t_name, $t_desc );
		}
		$t_info .= "</select>\n";
		return $t_info;
	}
	
	/**
	 * Prints the list of visible attachments belonging to a given bug.
	 * @param integer $p_bug_id ID of the bug to print attachments list for.
	 * @return void
	 */
	static function bug_attachments_list( $p_bug_id ) {
		$t_attachments = \Core\File::get_visible_attachments( $p_bug_id );
		echo "\n<ul>";
		foreach ( $t_attachments as $t_attachment ) {
			echo "\n<li>";
			self::bug_attachment( $t_attachment );
			echo "\n</li>";
		}
		echo "\n</ul>";
	}
	
	/**
	 * Prints information about a single attachment including download link, file
	 * size, upload timestamp and an expandable preview for text and image file
	 * types.
	 * @param array $p_attachment An attachment array from within the array returned by the \Core\File::get_visible_attachments() function.
	 * @return void
	 */
	static function bug_attachment( array $p_attachment ) {
		$t_show_attachment_preview = $p_attachment['preview'] && $p_attachment['exists'] && ( $p_attachment['type'] == 'text' || $p_attachment['type'] == 'image' );
		if( $t_show_attachment_preview ) {
			$t_collapse_id = 'attachment_preview_' . $p_attachment['id'];
			global $g_collapse_cache_token;
			$g_collapse_cache_token[$t_collapse_id] = false;
			\Core\Collapse::open( $t_collapse_id );
		}
		self::bug_attachment_header( $p_attachment );
		if( $t_show_attachment_preview ) {
			echo \Core\Lang::get( 'word_separator' );
			\Core\Collapse::icon( $t_collapse_id );
			if( $p_attachment['type'] == 'text' ) {
				self::bug_attachment_preview_text( $p_attachment );
			} else if( $p_attachment['type'] === 'image' ) {
				self::bug_attachment_preview_image( $p_attachment );
			}
			\Core\Collapse::closed( $t_collapse_id );
			self::bug_attachment_header( $p_attachment );
			echo \Core\Lang::get( 'word_separator' );
			\Core\Collapse::icon( $t_collapse_id );
			\Core\Collapse::end( $t_collapse_id );
		}
	}
	
	/**
	 * Prints a single textual line of information about an attachment including download link, file
	 * size and upload timestamp.
	 * @param array $p_attachment An attachment array from within the array returned by the \Core\File::get_visible_attachments() function.
	 * @return void
	 */
	static function bug_attachment_header( array $p_attachment ) {
		echo "\n";
		if( $p_attachment['exists'] ) {
			if( $p_attachment['can_download'] ) {
				echo '<a href="' . \Core\String::attribute( $p_attachment['download_url'] ) . '">';
			}
			self::file_icon( $p_attachment['display_name'] );
			if( $p_attachment['can_download'] ) {
				echo '</a>';
			}
			echo \Core\Lang::get( 'word_separator' );
			if( $p_attachment['can_download'] ) {
				echo '<a href="' . \Core\String::attribute( $p_attachment['download_url'] ) . '">';
			}
			echo \Core\String::display_line( $p_attachment['display_name'] );
			if( $p_attachment['can_download'] ) {
				echo '</a>';
			}
			echo \Core\Lang::get( 'word_separator' ) . '(' . number_format( $p_attachment['size'] ) . \Core\Lang::get( 'word_separator' ) . \Core\Lang::get( 'bytes' ) . ')';
			echo \Core\Lang::get( 'word_separator' ) . '<span class="italic">' . date( Config::mantis_get( 'normal_date_format' ), $p_attachment['date_added'] ) . '</span>';
			\Core\Event::signal( 'EVENT_VIEW_BUG_ATTACHMENT', array( $p_attachment ) );
		} else {
			self::file_icon( $p_attachment['display_name'] );
			echo \Core\Lang::get( 'word_separator' ) . '<span class="strike">' . \Core\String::display_line( $p_attachment['display_name'] ) . '</span>' . \Core\Lang::get( 'word_separator' ) . '(' . \Core\Lang::get( 'attachment_missing' ) . ')';
		}
	
		if( $p_attachment['can_delete'] ) {
			echo \Core\Lang::get( 'word_separator' ) . '[';
			self::link( 'bug_file_delete.php?file_id=' . $p_attachment['id'] . \Core\Form::security_param( 'bug_file_delete' ), \Core\Lang::get( 'delete_link' ), false, 'small' );
			echo ']';
		}
	}
	
	/**
	 * Prints the preview of a text file attachment.
	 * @param array $p_attachment An attachment array from within the array returned by the \Core\File::get_visible_attachments() function.
	 * @return void
	 */
	static function bug_attachment_preview_text( array $p_attachment ) {
		if( !$p_attachment['exists'] ) {
			return;
		}
		echo "\n<pre class=\"bug-attachment-preview-text\">";
		switch( Config::mantis_get( 'file_upload_method' ) ) {
			case DISK:
				if( file_exists( $p_attachment['diskfile'] ) ) {
					$t_content = file_get_contents( $p_attachment['diskfile'] );
				}
				break;
			case DATABASE:
				$t_query = 'SELECT * FROM {bug_file} WHERE id=' . \Core\Database::param();
				$t_result = \Core\Database::query( $t_query, array( (int)$p_attachment['id'] ) );
				$t_row = \Core\Database::fetch_array( $t_result );
				$t_content = $t_row['content'];
				break;
			default:
				trigger_error( ERROR_GENERIC, ERROR );
		}
		echo htmlspecialchars( $t_content );
		echo '</pre>';
	}
	
	/**
	 * Prints the preview of an image file attachment.
	 * @param array $p_attachment An attachment array from within the array returned by the \Core\File::get_visible_attachments() function.
	 * @return void
	 */
	static function bug_attachment_preview_image( array $p_attachment ) {
		$t_preview_style = 'border: 0;';
		$t_max_width = Config::mantis_get( 'preview_max_width' );
		if( $t_max_width > 0 ) {
			$t_preview_style .= ' max-width:' . $t_max_width . 'px;';
		}
	
		$t_max_height = Config::mantis_get( 'preview_max_height' );
		if( $t_max_height > 0 ) {
			$t_preview_style .= ' max-height:' . $t_max_height . 'px;';
		}
	
		$t_title = \Core\File::get_field( $p_attachment['id'], 'title' );
		$t_image_url = $p_attachment['download_url'] . '&show_inline=1' . \Core\Form::security_param( 'file_show_inline' );
	
		echo "\n<div class=\"bug-attachment-preview-image\">";
		echo '<a href="' . \Core\String::attribute( $p_attachment['download_url'] ) . '">';
		echo '<img src="' . \Core\String::attribute( $t_image_url ) . '" alt="' . \Core\String::attribute( $t_title ) . '" style="' . \Core\String::attribute( $t_preview_style ) . '" />';
		echo '</a></div>';
	}
	
	/**
	 * Print the option list for time zones
	 * @param string $p_timezone Selected time zone.
	 * @return void
	 */
	static function timezone_option_list( $p_timezone ) {
		$t_identifiers = timezone_identifiers_list( \DateTimeZone::ALL );
	
		foreach( $t_identifiers as $t_identifier ) {
			$t_zone = explode( '/', $t_identifier, 2 );
			if( isset( $t_zone[1] ) ) {
				$t_id = $t_zone[1];
			} else {
				$t_id = $t_identifier;
			}
			$t_locations[$t_zone[0]][$t_identifier] = array(
				str_replace( '_', ' ', $t_id ),
				$t_identifier
			);
		}
	
		foreach( $t_locations as $t_continent => $t_locations ) {
			echo "\t" . '<optgroup label="' . $t_continent . '">' . "\n";
			foreach ( $t_locations as $t_location ) {
				echo "\t\t" . '<option value="' . $t_location[1] . '"';
				\Core\Helper::check_selected( $p_timezone, $t_location[1] );
				echo '>' . $t_location[0] . '</option>' . "\n";
			}
			echo "\t" . '</optgroup>' . "\n";
		}
	}
	
	/**
	 * Return file size information
	 * @param integer $p_size File size.
	 * @param string  $p_unit File size unit.
	 * @return string
	 */
	static function get_filesize_info( $p_size, $p_unit ) {
		return sprintf( \Core\Lang::get( 'file_size_info' ), number_format( $p_size ), $p_unit );
	}
	
	/**
	 * Print maximum file size information
	 * @param integer $p_size    Size in bytes.
	 * @param integer $p_divider Optional divider, defaults to 1000.
	 * @param string  $p_unit    Optional language string of unit, defaults to KB.
	 * @return void
	 */
	static function max_filesize( $p_size, $p_divider = 1000, $p_unit = 'kb' ) {
		echo '<span class="small" title="' . self::get_filesize_info( $p_size, \Core\Lang::get( 'bytes' ) ) . '">';
		echo \Core\Lang::get( 'max_file_size_label' )
			. \Core\Lang::get( 'word_separator' )
			. self::get_filesize_info( $p_size / $p_divider, \Core\Lang::get( $p_unit ) );
		echo '</span>';
	}

}