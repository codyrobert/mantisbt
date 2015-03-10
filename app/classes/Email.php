<?php
namespace Core;


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
 * Email API
 *
 * @package CoreAPI
 * @subpackage EmailAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses email_queue_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses logging_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 *
 * @uses class.phpmailer.php PHPMailer library
 */

require_api( 'custom_field_api.php' );


class Email
{

	/**
	 * Use a simple perl regex for valid email addresses.  This is not a complete regex,
	 * as it does not cover quoted addresses or domain literals, but it is simple and
	 * covers the vast majority of all email addresses without being overly complex.
	 * @return string
	 */
	static function regex_simple() {
		static $s_email_regex = null;
	
		if( is_null( $s_email_regex ) ) {
			$t_recipient = '([a-z0-9!#*+\/=?^_{|}~-]+(?:\.[a-z0-9!#*+\/=?^_{|}~-]+)*)';
	
			# a domain is one or more subdomains
			$t_subdomain = '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';
			$t_domain    = '(' . $t_subdomain . '(?:\.' . $t_subdomain . ')*)';
	
			$s_email_regex = '/' . $t_recipient . '\@' . $t_domain . '/i';
		}
		return $s_email_regex;
	}
	
	/**
	 * check to see that the format is valid and that the mx record exists
	 * @param string $p_email An email address.
	 * @return boolean
	 */
	static function is_valid( $p_email ) {
		# if we don't validate then just accept
		if( OFF == \Core\Config::mantis_get( 'validate_email' ) ||
			ON == \Core\Config::mantis_get( 'use_ldap_email' ) ||
			( \Core\Utility::is_blank( $p_email ) && ON == \Core\Config::mantis_get( 'allow_blank_email' ) )
		) {
			return true;
		}
	
		# check email address is a valid format
		$t_email = filter_var( $p_email, FILTER_SANITIZE_EMAIL );
		if( PHPMailer::ValidateAddress( $t_email ) ) {
			$t_domain = substr( $t_email, strpos( $t_email, '@' ) + 1 );
	
			# see if we're limited to a set of known domains
			$t_limit_email_domains = \Core\Config::mantis_get( 'limit_email_domains' );
			if( !empty( $t_limit_email_domains ) ) {
				foreach( $t_limit_email_domains as $t_email_domain ) {
					if( 0 == strcasecmp( $t_email_domain, $t_domain ) ) {
						return true; # no need to check mx record details (below) if we've explicity allowed the domain
					}
				}
				return false;
			}
	
			if( ON == \Core\Config::mantis_get( 'check_mx_record' ) ) {
				$t_mx = '';
	
				# Check for valid mx records
				if( getmxrr( $t_domain, $t_mx ) ) {
					return true;
				} else {
					$t_host = $t_domain . '.';
	
					# for no mx record... try dns check
					if( checkdnsrr( $t_host, 'ANY' ) ) {
						return true;
					}
				}
			} else {
				# Email format was valid but did't check for valid mx records
				return true;
			}
		}
	
		# Everything failed.  The email is invalid
		return false;
	}
	
	/**
	 * Check if the email address is valid trigger an ERROR if it isn't
	 * @param string $p_email An email address.
	 * @return void
	 */
	static function ensure_valid( $p_email ) {
		if( !\Core\Email::is_valid( $p_email ) ) {
			trigger_error( ERROR_EMAIL_INVALID, ERROR );
		}
	}
	
	/**
	 * Check if the email address is disposable
	 * @param string $p_email An email address.
	 * @return boolean
	 */
	static function is_disposable( $p_email ) {
		if( !class_exists( 'DisposableEmailChecker' ) ) {
			require_lib( 'disposable/disposable.php' );
		}
	
		return DisposableEmailChecker::is_disposable_email( $p_email );
	}
	
	/**
	 * Check if the email address is disposable
	 * trigger an ERROR if it isn't
	 * @param string $p_email An email address.
	 * @return void
	 */
	static function ensure_not_disposable( $p_email ) {
		if( \Core\Email::is_disposable( $p_email ) ) {
			trigger_error( ERROR_EMAIL_DISPOSABLE, ERROR );
		}
	}
	
	/**
	 * email_notify_flag
	 * Get the value associated with the specific action and flag.
	 * For example, you can get the value associated with notifying "admin"
	 * on action "new", i.e. notify administrators on new bugs which can be
	 * ON or OFF.
	 * @param string $p_action Action.
	 * @param string $p_flag   Flag.
	 * @return integer
	 */
	static function notify_flag( $p_action, $p_flag ) {
		$t_notify_flags = \Core\Config::mantis_get( 'notify_flags' );
		$t_default_notify_flags = \Core\Config::mantis_get( 'default_notify_flags' );
		if( isset( $t_notify_flags[$p_action][$p_flag] ) ) {
			return $t_notify_flags[$p_action][$p_flag];
		} else if( isset( $t_default_notify_flags[$p_flag] ) ) {
			return $t_default_notify_flags[$p_flag];
		}
	
		return OFF;
	}
	
	/**
	 * Collect valid email recipients for email notification
	 * @todo yarick123: \Core\Email::collect_recipients(...) will be completely rewritten to provide additional information such as language, user access,..
	 * @todo yarick123:sort recipients list by language to reduce switches between different languages
	 * @param integer $p_bug_id                  A bug identifier.
	 * @param string  $p_notify_type             Notification type.
	 * @param array   $p_extra_user_ids_to_email Array of additional email addresses to notify.
	 * @return array
	 */
	static function collect_recipients( $p_bug_id, $p_notify_type, array $p_extra_user_ids_to_email = array() ) {
		$t_recipients = array();
	
		# add explicitly specified users
		if( ON == \Core\Email::notify_flag( $p_notify_type, 'explicit' ) ) {
			foreach ( $p_extra_user_ids_to_email as $t_user_id ) {
				$t_recipients[$t_user_id] = true;
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, add explicitly specified user = @U%d', $p_bug_id, $t_user_id );
			}
		}
	
		# add Reporter
		if( ON == \Core\Email::notify_flag( $p_notify_type, 'reporter' ) ) {
			$t_reporter_id = \Core\Bug::get_field( $p_bug_id, 'reporter_id' );
			$t_recipients[$t_reporter_id] = true;
			\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, add Reporter = @U%d', $p_bug_id, $t_reporter_id );
		}
	
		# add Handler
		if( ON == \Core\Email::notify_flag( $p_notify_type, 'handler' ) ) {
			$t_handler_id = \Core\Bug::get_field( $p_bug_id, 'handler_id' );
	
			if( $t_handler_id > 0 ) {
				$t_recipients[$t_handler_id] = true;
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, add Handler = @U%d', $p_bug_id, $t_handler_id );
			}
		}
	
		$t_project_id = \Core\Bug::get_field( $p_bug_id, 'project_id' );
	
		# add users monitoring the bug
		if( ON == \Core\Email::notify_flag( $p_notify_type, 'monitor' ) ) {
			$t_query = 'SELECT DISTINCT user_id FROM {bug_monitor} WHERE bug_id=' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( $p_bug_id ) );
	
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$t_user_id = $t_row['user_id'];
				$t_recipients[$t_user_id] = true;
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, add Monitor = @U%d', $p_bug_id, $t_user_id );
			}
		}
	
		# add users who contributed bugnotes
		$t_bugnote_id = \Core\Bug\Note::get_latest_id( $p_bug_id );
		$t_bugnote_view = \Core\Bug\Note::get_field( $t_bugnote_id, 'view_state' );
		$t_bugnote_date = \Core\Bug\Note::get_field( $t_bugnote_id, 'last_modified' );
		$t_bug = \Core\Bug::get( $p_bug_id );
		$t_bug_date = $t_bug->last_updated;
	
		if( ON == \Core\Email::notify_flag( $p_notify_type, 'bugnotes' ) ) {
			$t_query = 'SELECT DISTINCT reporter_id FROM {bugnote} WHERE bug_id = ' . \Core\Database::param();
			$t_result = \Core\Database::query( $t_query, array( $p_bug_id ) );
			while( $t_row = \Core\Database::fetch_array( $t_result ) ) {
				$t_user_id = $t_row['reporter_id'];
				$t_recipients[$t_user_id] = true;
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, add Note Author = @U%d', $p_bug_id, $t_user_id );
			}
		}
	
		# add project users who meet the thresholds
		$t_bug_is_private = \Core\Bug::get_field( $p_bug_id, 'view_state' ) == VS_PRIVATE;
		$t_threshold_min = \Core\Email::notify_flag( $p_notify_type, 'threshold_min' );
		$t_threshold_max = \Core\Email::notify_flag( $p_notify_type, 'threshold_max' );
		$t_threshold_users = \Core\Project::get_all_user_rows( $t_project_id, $t_threshold_min );
		foreach( $t_threshold_users as $t_user ) {
			if( $t_user['access_level'] <= $t_threshold_max ) {
				if( !$t_bug_is_private || \Core\Access::compare_level( $t_user['access_level'], \Core\Config::mantis_get( 'private_bug_threshold' ) ) ) {
					$t_recipients[$t_user['id']] = true;
					\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, add Project User = @U%d', $p_bug_id, $t_user['id'] );
				}
			}
		}
	
		# add users as specified by plugins
		$t_recipients_include_data = \Core\Event::signal( 'EVENT_NOTIFY_USER_INCLUDE', array( $p_bug_id, $p_notify_type ) );
		foreach( $t_recipients_include_data as $t_plugin => $t_recipients_include_data2 ) {
			foreach( $t_recipients_include_data2 as $t_callback => $t_recipients_included ) {
				# only handle if we get an array from the callback
				if( is_array( $t_recipients_included ) ) {
					foreach( $t_recipients_included as $t_user_id ) {
						$t_recipients[$t_user_id] = true;
						\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, %s plugin added user @U%d', $p_bug_id, $t_plugin, $t_user_id );
					}
				}
			}
		}
	
		# FIXME: the value of $p_notify_type could at this stage be either a status
		# or a built-in actions such as 'owner and 'sponsor'. We have absolutely no
		# idea whether 'new' is indicating a new bug has been filed, or if the
		# status of an existing bug has been changed to 'new'. Therefore it is best
		# to just assume built-in actions have precedence over status changes.
		switch( $p_notify_type ) {
			case 'new':
			case 'feedback': # This isn't really a built-in action (delete me!)
			case 'reopened':
			case 'resolved':
			case 'closed':
			case 'bugnote':
				$t_pref_field = 'email_on_' . $p_notify_type;
				break;
			case 'owner':
				# The email_on_assigned notification type is now effectively
				# email_on_change_of_handler.
				$t_pref_field = 'email_on_assigned';
				break;
			case 'deleted':
			case 'updated':
			case 'sponsor':
			case 'relation':
			case 'monitor':
			case 'priority': # This is never used, but exists in the database!
				# FIXME: these notification actions are not actually implemented
				# in the database and therefore aren't adjustable on a per-user
				# basis! The exception is 'monitor' that makes no sense being a
				# customisable per-user preference.
				$t_pref_field = false;
				break;
			default:
				# Anything not built-in is probably going to be a status
				$t_pref_field = 'email_on_status';
				break;
		}
	
		# @@@ we could optimize by modifiying user_cache() to take an array
		#  of user ids so we could pull them all in.  We'll see if it's necessary
		$t_final_recipients = array();
	
		$t_user_ids = array_keys( $t_recipients );
		\Core\User::cache_array_rows( $t_user_ids );
		\Core\User\Pref::cache_array_rows( $t_user_ids );
		\Core\User\Pref::cache_array_rows( $t_user_ids, $t_bug->project_id );
	
		# Check whether users should receive the emails
		# and put email address to $t_recipients[user_id]
		foreach( $t_recipients as $t_id => $t_ignore ) {
			# Possibly eliminate the current user
			if( ( auth_get_current_user_id() == $t_id ) && ( OFF == \Core\Config::mantis_get( 'email_receive_own' ) ) ) {
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, drop @U%d (own)', $p_bug_id, $t_id );
				continue;
			}
	
			# Eliminate users who don't exist anymore or who are disabled
			if( !\Core\User::exists( $t_id ) || !\Core\User::is_enabled( $t_id ) ) {
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, drop @U%d (disabled)', $p_bug_id, $t_id );
				continue;
			}
	
			# Exclude users who have this notification type turned off
			if( $t_pref_field ) {
				$t_notify = \Core\User\Pref::get_pref( $t_id, $t_pref_field );
				if( OFF == $t_notify ) {
					\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, drop @U%d (pref %s off)', $p_bug_id, $t_id, $t_pref_field );
					continue;
				} else {
					# Users can define the severity of an issue before they are emailed for
					# each type of notification
					$t_min_sev_pref_field = $t_pref_field . '_min_severity';
					$t_min_sev_notify = \Core\User\Pref::get_pref( $t_id, $t_min_sev_pref_field );
					$t_bug_severity = \Core\Bug::get_field( $p_bug_id, 'severity' );
	
					if( $t_bug_severity < $t_min_sev_notify ) {
						\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, drop @U%d (pref threshold)', $p_bug_id, $t_id );
						continue;
					}
				}
			}
	
			# exclude users who don't have at least viewer access to the bug,
			# or who can't see bugnotes if the last update included a bugnote
			if( !\Core\Access::has_bug_level( \Core\Config::mantis_get( 'view_bug_threshold', null, $t_id, $t_bug->project_id ), $p_bug_id, $t_id )
			 || $t_bug_date == $t_bugnote_date && !\Core\Access::has_bugnote_level( \Core\Config::mantis_get( 'view_bug_threshold', null, $t_id, $t_bug->project_id ), $t_bugnote_id, $t_id )
			) {
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, drop @U%d (access level)', $p_bug_id, $t_id );
				continue;
			}
	
			# check to exclude users as specified by plugins
			$t_recipient_exclude_data = \Core\Event::signal( 'EVENT_NOTIFY_USER_EXCLUDE', array( $p_bug_id, $p_notify_type, $t_id ) );
			$t_exclude = false;
			foreach( $t_recipient_exclude_data as $t_plugin => $t_recipient_exclude_data2 ) {
				foreach( $t_recipient_exclude_data2 as $t_callback => $t_recipient_excluded ) {
					# exclude if any plugin returns true (excludes the user)
					if( $t_recipient_excluded ) {
						$t_exclude = true;
						\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, %s plugin dropped user @U%d', $p_bug_id, $t_plugin, $t_id );
					}
				}
			}
	
			# user was excluded by a plugin
			if( $t_exclude ) {
				continue;
			}
	
			# Finally, let's get their emails, if they've set one
			$t_email = \Core\User::get_email( $t_id );
			if( \Core\Utility::is_blank( $t_email ) ) {
				\Core\Log::event( LOG_EMAIL_RECIPIENT, 'Issue = #%d, drop @U%d (no email)', $p_bug_id, $t_id );
			} else {
				# @@@ we could check the emails for validity again but I think
				#   it would be too slow
				$t_final_recipients[$t_id] = $t_email;
			}
		}
	
		return $t_final_recipients;
	}
	
	/**
	 * Send password to user
	 * @param integer $p_user_id      A valid user identifier.
	 * @param string  $p_password     A valid password.
	 * @param string  $p_confirm_hash Confirmation hash.
	 * @param string  $p_admin_name   Administrator name.
	 * @return void
	 */
	static function signup( $p_user_id, $p_password, $p_confirm_hash, $p_admin_name = '' ) {
		if( ( OFF == \Core\Config::mantis_get( 'send_reset_password' ) ) || ( OFF == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
			return;
		}
	
		#	@@@ thraxisp - removed to address #6084 - user won't have any settings yet,
		#  use same language as display for the email
		#  \Core\Lang::push( \Core\User\Pref::get_language( $p_user_id ) );
		# retrieve the username and email
		$t_username = \Core\User::get_field( $p_user_id, 'username' );
		$t_email = \Core\User::get_email( $p_user_id );
	
		# Build Welcome Message
		$t_subject = '[' . \Core\Config::mantis_get( 'window_title' ) . '] ' . \Core\Lang::get( 'new_account_subject' );
	
		if( !empty( $p_admin_name ) ) {
			$t_intro_text = sprintf( \Core\Lang::get( 'new_account_greeting_admincreated' ), $p_admin_name, $t_username );
		} else {
			$t_intro_text = sprintf( \Core\Lang::get( 'new_account_greeting' ), $t_username );
		}
	
		$t_message = $t_intro_text . "\n\n" . \Core\String::get_confirm_hash_url( $p_user_id, $p_confirm_hash ) . "\n\n" . \Core\Lang::get( 'new_account_message' ) . "\n\n" . \Core\Lang::get( 'new_account_do_not_reply' );
	
		# Send signup email regardless of mail notification pref
		# or else users won't be able to sign up
		if( !\Core\Utility::is_blank( $t_email ) ) {
			\Core\Email::store( $t_email, $t_subject, $t_message );
			\Core\Log::event( LOG_EMAIL, 'Signup Email = %s, Hash = %s, User = @U%d', $t_email, $p_confirm_hash, $p_user_id );
		}
	
		# \Core\Lang::pop(); # see above
	}
	
	/**
	 * Send confirm_hash URL to user forgets the password
	 * @param integer $p_user_id      A valid user identifier.
	 * @param string  $p_confirm_hash Confirmation hash.
	 * @return void
	 */
	static function send_confirm_hash_url( $p_user_id, $p_confirm_hash ) {
		if( OFF == \Core\Config::mantis_get( 'send_reset_password' ) ||
			OFF == \Core\Config::mantis_get( 'enable_email_notification' ) ) {
			return;
		}
	
		\Core\Lang::push( \Core\User\Pref::get_language( $p_user_id ) );
	
		# retrieve the username and email
		$t_username = \Core\User::get_field( $p_user_id, 'username' );
		$t_email = \Core\User::get_email( $p_user_id );
	
		$t_subject = '[' . \Core\Config::mantis_get( 'window_title' ) . '] ' . \Core\Lang::get( 'lost_password_subject' );
	
		$t_message = \Core\Lang::get( 'reset_request_msg' ) . " \n\n" . \Core\String::get_confirm_hash_url( $p_user_id, $p_confirm_hash ) . " \n\n" . \Core\Lang::get( 'new_account_username' ) . ' ' . $t_username . " \n" . \Core\Lang::get( 'new_account_IP' ) . ' ' . $_SERVER['REMOTE_ADDR'] . " \n\n" . \Core\Lang::get( 'new_account_do_not_reply' );
	
		# Send password reset regardless of mail notification preferences
		# or else users won't be able to receive their reset passwords
		if( !\Core\Utility::is_blank( $t_email ) ) {
			\Core\Email::store( $t_email, $t_subject, $t_message );
			\Core\Log::event( LOG_EMAIL, 'Password reset for user @U%d sent to %s', $p_user_id, $t_email );
		}
	
		\Core\Lang::pop();
	}
	
	/**
	 * notify the selected group a new user has signup
	 * @param string $p_username Username of new user.
	 * @param string $p_email    Email address of new user.
	 * @return void
	 */
	static function notify_new_account( $p_username, $p_email ) {
		$t_threshold_min = \Core\Config::mantis_get( 'notify_new_user_created_threshold_min' );
		$t_threshold_users = \Core\Project::get_all_user_rows( ALL_PROJECTS, $t_threshold_min );
	
		foreach( $t_threshold_users as $t_user ) {
			\Core\Lang::push( \Core\User\Pref::get_language( $t_user['id'] ) );
	
			$t_recipient_email = \Core\User::get_email( $t_user['id'] );
			$t_subject = '[' . \Core\Config::mantis_get( 'window_title' ) . '] ' . \Core\Lang::get( 'new_account_subject' );
	
			$t_message = \Core\Lang::get( 'new_account_signup_msg' ) . "\n\n" . \Core\Lang::get( 'new_account_username' ) . ' ' . $p_username . "\n" . \Core\Lang::get( 'new_account_email' ) . ' ' . $p_email . "\n" . \Core\Lang::get( 'new_account_IP' ) . ' ' . $_SERVER['REMOTE_ADDR'] . "\n" . \Core\Config::get_global( 'path' ) . "\n\n" . \Core\Lang::get( 'new_account_do_not_reply' );
	
			if( !\Core\Utility::is_blank( $t_recipient_email ) ) {
				\Core\Email::store( $t_recipient_email, $t_subject, $t_message );
				\Core\Log::event( LOG_EMAIL, 'New Account Notify for email = \'%s\'', $t_recipient_email );
			}
	
			\Core\Lang::pop();
		}
	}
	
	
	/**
	 * send a generic email
	 * $p_notify_type: use check who she get notified of such event.
	 * $p_message_id: message id to be translated and included at the top of the email message.
	 * Return false if it were problems sending email
	 * @param integer $p_bug_id                  A bug identifier.
	 * @param string  $p_notify_type             Notification type.
	 * @param integer $p_message_id              Message identifier.
	 * @param array   $p_header_optional_params  Optional Parameters (default null).
	 * @param array   $p_extra_user_ids_to_email Array of additional users to email.
	 * @return void
	 */
	static function generic( $p_bug_id, $p_notify_type, $p_message_id = null, array $p_header_optional_params = null, array $p_extra_user_ids_to_email = array() ) {
		# @todo yarick123: \Core\Email::collect_recipients(...) will be completely rewritten to provide additional information such as language, user access,..
		# @todo yarick123:sort recipients list by language to reduce switches between different languages
		$t_recipients = \Core\Email::collect_recipients( $p_bug_id, $p_notify_type, $p_extra_user_ids_to_email );
		\Core\Email::generic_to_recipients( $p_bug_id, $p_notify_type, $t_recipients, $p_message_id, $p_header_optional_params, $p_extra_user_ids_to_email );
	}
	
	/**
	 * Sends a generic email to the specific set of recipients.
	 *
	 * @param integer $p_bug_id                  A bug identifier
	 * @param string  $p_notify_type             Notification type
	 * @param array   $p_recipients              Array of recipients (key: user id, value: email address)
	 * @param integer $p_message_id              Message identifier
	 * @param array   $p_header_optional_params  Optional Parameters (default null)
	 * @return void
	 */
	static function generic_to_recipients( $p_bug_id, $p_notify_type, array $p_recipients, $p_message_id = null, array $p_header_optional_params = null ) {
		if( OFF == \Core\Config::mantis_get( 'enable_email_notification' ) ) {
			return;
		}
	
		ignore_user_abort( true );
	
		\Core\Bug\Note::get_all_bugnotes( $p_bug_id );
	
		$t_project_id = \Core\Bug::get_field( $p_bug_id, 'project_id' );
	
		if( is_array( $p_recipients ) ) {
			# send email to every recipient
			foreach( $p_recipients as $t_user_id => $t_user_email ) {
				\Core\Log::event( LOG_EMAIL, 'Issue = #%d, Type = %s, Msg = \'%s\', User = @U%d, Email = \'%s\'.', $p_bug_id, $p_notify_type, $p_message_id, $t_user_id, $t_user_email );
	
				# load (push) user language here as build_visible_bug_data assumes current language
				\Core\Lang::push( \Core\User\Pref::get_language( $t_user_id, $t_project_id ) );
	
				$t_visible_bug_data = \Core\Email::build_visible_bug_data( $t_user_id, $p_bug_id, $p_message_id );
				\Core\Email::bug_info_to_one_user( $t_visible_bug_data, $p_message_id, $t_project_id, $t_user_id, $p_header_optional_params );
	
				\Core\Lang::pop();
			}
		}
	}
	
	/**
	 * Send notices that a user is now monitoring the bug.  Typically this will only be sent when the added
	 * user is not the logged in user.  This is assuming that receive own notifications is OFF (default).
	 * @param integer $p_bug_id  A valid bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @return void
	 */
	static function monitor_added( $p_bug_id, $p_user_id ) {
		\Core\Log::event( LOG_EMAIL, 'Issue #%d monitored by user @U%d', $p_bug_id, $p_user_id );
	
		$t_opt = array();
		$t_opt[] = \Core\Bug::format_id( $p_bug_id );
		$t_opt[] = \Core\User::get_name( $p_user_id );
	
		\Core\Email::generic( $p_bug_id, 'monitor', 'email_notification_title_for_action_monitor', $t_opt, array( $p_user_id ) );
	}
	
	/**
	 * send notices when a relationship is ADDED
	 * @param integer $p_bug_id         A bug identifier.
	 * @param integer $p_related_bug_id Related bug identifier.
	 * @param integer $p_rel_type       Relationship type.
	 * @return void
	 */
	static function relationship_added( $p_bug_id, $p_related_bug_id, $p_rel_type ) {
		\Core\Log::event( LOG_EMAIL, 'Relationship added: Issue #%d, related issue %d, relationship type %s.', $p_bug_id, $p_related_bug_id, $p_rel_type );
	
		$t_opt = array();
		$t_opt[] = \Core\Bug::format_id( $p_related_bug_id );
		global $g_relationships;
		if( !isset( $g_relationships[$p_rel_type] ) ) {
			trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
		}
	
		$t_recipients = \Core\Email::collect_recipients( $p_bug_id, 'relation' );
	
		# Recipient has to have access to both bugs to get the notification.
	    $t_recipients = \Core\Email::filter_recipients_for_bug( $p_bug_id, $t_recipients );
	    $t_recipients = \Core\Email::filter_recipients_for_bug( $p_related_bug_id, $t_recipients );
	
	    \Core\Email::generic_to_recipients( $p_bug_id, 'relation', $t_recipients, $g_relationships[$p_rel_type]['#notify_added'], $t_opt );
	}
	
	/**
	 * Filter recipients to remove ones that don't have access to the specified bug.
	 *
	 * @param integer $p_bug_id       The bug id
	 * @param array   $p_recipients   The recipients array (key: id, value: email)
	 * @return array The filtered list of recipients in same format
	 * @access private
	 */
	static function filter_recipients_for_bug( $p_bug_id, array $p_recipients ) {
	    $t_view_bug_threshold = \Core\Config::mantis_get( 'view_bug_threshold' );
	
	    $t_authorized_recipients = array();
	
	    foreach( $p_recipients as $t_recipient_id => $t_recipient_email ) {
	        if( \Core\Access::has_bug_level( $t_view_bug_threshold, $p_bug_id, $t_recipient_id ) ) {
	            $t_authorized_recipients[$t_recipient_id] = $t_recipient_email;
	        }
	    }
	
	    return $t_authorized_recipients;
	}
	
	/**
	 * send notices when a relationship is DELETED
	 * @param integer $p_bug_id         A bug identifier.
	 * @param integer $p_related_bug_id Related bug identifier.
	 * @param integer $p_rel_type       Relationship type.
	 * @return void
	 */
	static function relationship_deleted( $p_bug_id, $p_related_bug_id, $p_rel_type ) {
		\Core\Log::event( LOG_EMAIL, 'Relationship deleted: Issue #%d, related issue %d, relationship type %s.', $p_bug_id, $p_related_bug_id, $p_rel_type );
	
		$t_opt = array();
		$t_opt[] = \Core\Bug::format_id( $p_related_bug_id );
		global $g_relationships;
		if( !isset( $g_relationships[$p_rel_type] ) ) {
			trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
		}
	
	    $t_recipients = \Core\Email::collect_recipients( $p_bug_id, 'relation' );
	
	    # Recipient has to have access to both bugs to get the notification.
	    $t_recipients = \Core\Email::filter_recipients_for_bug( $p_bug_id, $t_recipients );
	    $t_recipients = \Core\Email::filter_recipients_for_bug( $p_related_bug_id, $t_recipients );
	
	    \Core\Email::generic_to_recipients( $p_bug_id, 'relation', $t_recipients, $g_relationships[$p_rel_type]['#notify_deleted'], $t_opt );
	}
	
	/**
	 * send notices to all the handlers of the parent bugs when a child bug is RESOLVED
	 * @param integer $p_bug_id A bug identifier.
	 * @return void
	 */
	static function relationship_child_resolved( $p_bug_id ) {
		\Core\Email::relationship_child_resolved_closed( $p_bug_id, 'email_notification_title_for_action_relationship_child_resolved' );
	}
	
	/**
	 * send notices to all the handlers of the parent bugs when a child bug is CLOSED
	 * @param integer $p_bug_id A bug identifier.
	 * @return void
	 */
	static function relationship_child_closed( $p_bug_id ) {
		\Core\Email::relationship_child_resolved_closed( $p_bug_id, 'email_notification_title_for_action_relationship_child_closed' );
	}
	
	/**
	 * send notices to all the handlers of the parent bugs still open when a child bug is resolved/closed
	 *
	 * @param integer $p_bug_id     A bug identifier.
	 * @param integer $p_message_id A message identifier.
	 * @return void
	 */
	static function relationship_child_resolved_closed( $p_bug_id, $p_message_id ) {
		# retrieve all the relationships in which the bug is the destination bug
		$t_relationship = \Core\Relationship::get_all_dest( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		if( $t_relationship_count == 0 ) {
			# no parent bug found
			return;
		}
	
		for( $i = 0;$i < $t_relationship_count;$i++ ) {
			if( $t_relationship[$i]->type == BUG_DEPENDANT ) {
				$t_src_bug_id = $t_relationship[$i]->src_bug_id;
				$t_status = \Core\Bug::get_field( $t_src_bug_id, 'status' );
				if( $t_status < \Core\Config::mantis_get( 'bug_resolved_status_threshold' ) ) {
	
					# sent the notification just for parent bugs not resolved/closed
					$t_opt = array();
					$t_opt[] = \Core\Bug::format_id( $p_bug_id );
					\Core\Email::generic( $t_src_bug_id, 'handler', $p_message_id, $t_opt );
				}
			}
		}
	}
	
	/**
	 * Store email in queue for sending
	 *
	 * @param string $p_recipient Email recipient address.
	 * @param string $p_subject   Subject of email message.
	 * @param string $p_message   Body text of email message.
	 * @param array  $p_headers   Array of additional headers to send with the email.
	 * @return integer|null
	 */
	static function store( $p_recipient, $p_subject, $p_message, array $p_headers = null ) {
		global $g_email_stored;
	
		$t_recipient = trim( $p_recipient );
		$t_subject = \Core\String::email( trim( $p_subject ) );
		$t_message = \Core\String::email_links( trim( $p_message ) );
	
		# short-circuit if no recipient is defined, or email disabled
		# note that this may cause signup messages not to be sent
		if( \Core\Utility::is_blank( $p_recipient ) || ( OFF == \Core\Config::mantis_get( 'enable_email_notification' ) ) ) {
			return null;
		}
	
		$t_email_data = new \Core\Email\Data;
	
		$t_email_data->email = $t_recipient;
		$t_email_data->subject = $t_subject;
		$t_email_data->body = $t_message;
		$t_email_data->metadata = array();
		$t_email_data->metadata['headers'] = $p_headers === null ? array() : $p_headers;
		$t_email_data->metadata['priority'] = \Core\Config::mantis_get( 'mail_priority' );
	
		# Urgent = 1, Not Urgent = 5, Disable = 0
		$t_email_data->metadata['charset'] = 'utf-8';
	
		$t_hostname = '';
		if( isset( $_SERVER['SERVER_NAME'] ) ) {
			$t_hostname = $_SERVER['SERVER_NAME'];
		} else {
			$t_address = explode( '@', \Core\Config::mantis_get( 'from_email' ) );
			if( isset( $t_address[1] ) ) {
				$t_hostname = $t_address[1];
			}
		}
		$t_email_data->metadata['hostname'] = $t_hostname;
	
		$t_email_id = \Core\Email\Queue::add( $t_email_data );
	
		$g_email_stored = true;
	
		return $t_email_id;
	}
	
	/**
	 * This function sends all the emails that are stored in the queue.
	 * It will be called
	 * - immediately after queueing messages in case of synchronous emails
	 * - from a cronjob in case of asynchronous emails
	 * If a failure occurs, then the function exits.
	 * @todo In case of synchronous email sending, we may get a race condition where two requests send the same email.
	 * @param boolean $p_delete_on_failure Indicates whether to remove email from queue on failure (default false).
	 * @return void
	 */
	static function send_all( $p_delete_on_failure = false ) {
		$t_ids = email_queue_get_ids();
	
		\Core\Log::event( LOG_EMAIL, 'Processing e-mail queue (' . count( $t_ids ) . ' messages)' );
	
		foreach( $t_ids as $t_id ) {
			$t_email_data = email_queue_get( $t_id );
			$t_start = microtime( true );
	
			# check if email was not found.  This can happen if another request picks up the email first and sends it.
			if( $t_email_data === false ) {
				$t_email_sent = true;
				\Core\Log::event( LOG_EMAIL, 'Message #$t_id has already been sent' );
			} else {
				\Core\Log::event( LOG_EMAIL, 'Sending message #' . $t_id );
				$t_email_sent = \Core\Email::send( $t_email_data );
			}
	
			if( !$t_email_sent ) {
				if( $p_delete_on_failure ) {
					email_queue_delete( $t_email_data->email_id );
				}
	
				# If unable to place the email in the email server queue and more
				# than 5 seconds have elapsed, then we assume that the server
				# connection is down, hence no point to continue trying with the
				# rest of the emails.
				if( microtime( true ) - $t_start > 5 ) {
					\Core\Log::event( LOG_EMAIL, 'Server not responding for 5 seconds, aborting' );
					break;
				}
			}
		}
	}
	
	/**
	 * This function sends an email message based on the supplied email data.
	 *
	 * @param EmailData $p_email_data Email Data object representing the email to send.
	 * @return boolean
	 */
	static function send( \Core\Email\Data $p_email_data ) {
		global $g_phpMailer;
	
		$t_email_data = $p_email_data;
	
		$t_recipient = trim( $t_email_data->email );
		$t_subject = \Core\String::email( trim( $t_email_data->subject ) );
		$t_message = \Core\String::email_links( trim( $t_email_data->body ) );
	
		$t_debug_email = \Core\Config::get_global( 'debug_email' );
		$t_mailer_method = \Core\Config::mantis_get( 'phpMailer_method' );
	
		$t_log_msg = 'ERROR: Message could not be sent - ';
	
		if( is_null( $g_phpMailer ) ) {
			if( $t_mailer_method == PHPMAILER_METHOD_SMTP ) {
				register_shutdown_function( 'email_smtp_close' );
			}
			$t_mail = new PHPMailer( true );
		} else {
			$t_mail = $g_phpMailer;
		}
	
		if( isset( $t_email_data->metadata['hostname'] ) ) {
			$t_mail->Hostname = $t_email_data->metadata['hostname'];
		}
	
		# @@@ should this be the current language (for the recipient) or the default one (for the user running the command) (thraxisp)
		$t_lang = \Core\Config::mantis_get( 'default_language' );
		if( 'auto' == $t_lang ) {
			$t_lang = \Core\Config::mantis_get( 'fallback_language' );
		}
		$t_mail->SetLanguage( \Core\Lang::get( 'phpmailer_language', $t_lang ) );
	
		# Select the method to send mail
		switch( \Core\Config::mantis_get( 'phpMailer_method' ) ) {
			case PHPMAILER_METHOD_MAIL:
				$t_mail->IsMail();
				break;
	
			case PHPMAILER_METHOD_SENDMAIL:
				$t_mail->IsSendmail();
				break;
	
			case PHPMAILER_METHOD_SMTP:
				$t_mail->IsSMTP();
	
				# SMTP collection is always kept alive
				$t_mail->SMTPKeepAlive = true;
	
				if( !\Core\Utility::is_blank( \Core\Config::mantis_get( 'smtp_username' ) ) ) {
					# Use SMTP Authentication
					$t_mail->SMTPAuth = true;
					$t_mail->Username = \Core\Config::mantis_get( 'smtp_username' );
					$t_mail->Password = \Core\Config::mantis_get( 'smtp_password' );
				}
	
				if( !\Core\Utility::is_blank( \Core\Config::mantis_get( 'smtp_connection_mode' ) ) ) {
					$t_mail->SMTPSecure = \Core\Config::mantis_get( 'smtp_connection_mode' );
				}
	
				$t_mail->Port = \Core\Config::mantis_get( 'smtp_port' );
	
				break;
		}
	
		$t_mail->IsHTML( false );              # set email format to plain text
		$t_mail->WordWrap = 80;              # set word wrap to 50 characters
		$t_mail->Priority = $t_email_data->metadata['priority'];  # Urgent = 1, Not Urgent = 5, Disable = 0
		$t_mail->CharSet = $t_email_data->metadata['charset'];
		$t_mail->Host = \Core\Config::mantis_get( 'smtp_host' );
		$t_mail->From = \Core\Config::mantis_get( 'from_email' );
		$t_mail->Sender = \Core\Config::mantis_get( 'return_path_email' );
		$t_mail->FromName = \Core\Config::mantis_get( 'from_name' );
		$t_mail->AddCustomHeader( 'Auto-Submitted:auto-generated' );
		$t_mail->AddCustomHeader( 'X-Auto-Response-Suppress: All' );
	
		# Setup new line and encoding to avoid extra new lines with some smtp gateways like sendgrid.net
		$t_mail->LE         = "\r\n";
		$t_mail->Encoding   = 'quoted-printable';
	
		if( !empty( $t_debug_email ) ) {
			$t_message = 'To: ' . $t_recipient . "\n\n" . $t_message;
			$t_recipient = $t_debug_email;
		}
	
		try {
			$t_mail->AddAddress( $t_recipient, '' );
		}
		catch ( phpmailerException $e ) {
			\Core\Log::event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			$t_success = false;
			$t_mail->ClearAllRecipients();
			$t_mail->ClearAttachments();
			$t_mail->ClearReplyTos();
			$t_mail->ClearCustomHeaders();
			return $t_success;
		}
	
		$t_mail->Subject = $t_subject;
		$t_mail->Body = \Core\Email::make_lf_crlf( "\n" . $t_message );
	
		if( isset( $t_email_data->metadata['headers'] ) && is_array( $t_email_data->metadata['headers'] ) ) {
			foreach( $t_email_data->metadata['headers'] as $t_key => $t_value ) {
				switch( $t_key ) {
					case 'Message-ID':
						# Note: hostname can never be blank here as we set metadata['hostname']
						# in \Core\Email::store() where mail gets queued.
						if( !strchr( $t_value, '@' ) && !\Core\Utility::is_blank( $t_mail->Hostname ) ) {
							$t_value = $t_value . '@' . $t_mail->Hostname;
						}
						$t_mail->set( 'MessageID', '<' . $t_value . '>' );
						break;
					case 'In-Reply-To':
						$t_mail->AddCustomHeader( $t_key . ': <' . $t_value . '@' . $t_mail->Hostname . '>' );
						break;
					default:
						$t_mail->AddCustomHeader( $t_key . ': ' . $t_value );
						break;
				}
			}
		}
	
		try {
			$t_success = $t_mail->Send();
			if( $t_success ) {
				$t_success = true;
	
				if( $t_email_data->email_id > 0 ) {
					email_queue_delete( $t_email_data->email_id );
				}
			} else {
				# We should never get here, as an exception is thrown after failures
				\Core\Log::event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
				$t_success = false;
			}
		}
		catch ( phpmailerException $e ) {
			\Core\Log::event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			$t_success = false;
		}
	
		$t_mail->ClearAllRecipients();
		$t_mail->ClearAttachments();
		$t_mail->ClearReplyTos();
		$t_mail->ClearCustomHeaders();
	
		return $t_success;
	}
	
	/**
	 * closes opened kept alive SMTP connection (if it was opened)
	 *
	 * @return void
	 */
	static function smtp_close() {
		global $g_phpMailer;
	
		if( !is_null( $g_phpMailer ) ) {
			if( $g_phpMailer->smtp->Connected() ) {
				$g_phpMailer->smtp->Quit();
				$g_phpMailer->smtp->Close();
			}
			$g_phpMailer = null;
		}
	}
	
	/**
	 * formats the subject correctly
	 * we include the project name, bug id, and summary.
	 *
	 * @param integer $p_bug_id A bug identifier.
	 * @return string
	 */
	static function build_subject( $p_bug_id ) {
		# grab the project name
		$p_project_name = \Core\Project::get_field( \Core\Bug::get_field( $p_bug_id, 'project_id' ), 'name' );
	
		# grab the subject (summary)
		$p_subject = \Core\Bug::get_field( $p_bug_id, 'summary' );
	
		# pad the bug id with zeros
		$t_bug_id = \Core\Bug::format_id( $p_bug_id );
	
		# build standard subject string
		$t_email_subject = '[' . $p_project_name . ' ' . $t_bug_id . ']: ' . $p_subject;
	
		# update subject as defined by plugins
		$t_email_subject = \Core\Event::signal( 'EVENT_DISPLAY_EMAIL_BUILD_SUBJECT', $t_email_subject, array( 'bug_id' => $p_bug_id ) );
	
		return $t_email_subject;
	}
	
	/**
	 * clean up LF to CRLF
	 *
	 * @param string $p_string String to convert linefeeds on.
	 * @return string
	 */
	function make_lf_crlf( $p_string ) {
		$t_string = str_replace( "\n", "\r\n", $p_string );
		return str_replace( "\r\r\n", "\r\n", $t_string );
	}
	
	/**
	 * Send a bug reminder to the given user(s), or to each user if the first parameter is an array
	 *
	 * @param integer|array $p_recipients User id or list of user ids array to send reminder to.
	 * @param integer       $p_bug_id     Issue for which the reminder is sent.
	 * @param string        $p_message    Optional message to add to the e-mail.
	 * @return array List of users ids to whom the reminder e-mail was actually sent
	 */
	static function bug_reminder( $p_recipients, $p_bug_id, $p_message ) {
		if( OFF == \Core\Config::mantis_get( 'enable_email_notification' ) ) {
			return array();
		}
	
		if( !is_array( $p_recipients ) ) {
			$p_recipients = array(
				$p_recipients,
			);
		}
	
		$t_project_id = \Core\Bug::get_field( $p_bug_id, 'project_id' );
		$t_sender_id = \Core\Auth::get_current_user_id();
		$t_sender = \Core\User::get_name( $t_sender_id );
	
		$t_subject = \Core\Email::build_subject( $p_bug_id );
		$t_date = date( \Core\Config::mantis_get( 'normal_date_format' ) );
	
		$t_result = array();
		foreach( $p_recipients as $t_recipient ) {
			\Core\Lang::push( \Core\User\Pref::get_language( $t_recipient, $t_project_id ) );
	
			$t_email = \Core\User::get_email( $t_recipient );
	
			if( \Core\Access::has_project_level( \Core\Config::mantis_get( 'show_user_email_threshold' ), $t_project_id, $t_recipient ) ) {
				$t_sender_email = ' <' . \Core\User::get_email( $t_sender_id ) . '>';
			} else {
				$t_sender_email = '';
			}
			$t_header = "\n" . \Core\Lang::get( 'on_date' ) . ' ' . $t_date . ', ' . $t_sender . ' ' . $t_sender_email . \Core\Lang::get( 'sent_you_this_reminder_about' ) . ': ' . "\n\n";
			$t_contents = $t_header . \Core\String::get_bug_view_url_with_fqdn( $p_bug_id, $t_recipient ) . " \n\n" . $p_message;
	
			$t_id = \Core\Email::store( $t_email, $t_subject, $t_contents );
			if( $t_id !== null ) {
				$t_result[] = $t_recipient;
			}
			\Core\Log::event( LOG_EMAIL, 'queued reminder email #' . $t_id . ' for U' . $t_recipient );
	
			\Core\Lang::pop();
		}
	
		return $t_result;
	}
	
	/**
	 * Send bug info to given user
	 * return true on success
	 * @param array   $p_visible_bug_data       Array of bug data information.
	 * @param string  $p_message_id             A message identifier.
	 * @param integer $p_project_id             A project identifier.
	 * @param integer $p_user_id                A valid user identifier.
	 * @param array   $p_header_optional_params Array of additional email headers.
	 * @return void
	 */
	static function bug_info_to_one_user( array $p_visible_bug_data, $p_message_id, $p_project_id, $p_user_id, array $p_header_optional_params = null ) {
		$t_user_email = \Core\User::get_email( $p_user_id );
	
		# check whether email should be sent
		# @@@ can be email field empty? if yes - then it should be handled here
		if( ON !== \Core\Config::mantis_get( 'enable_email_notification' ) || \Core\Utility::is_blank( $t_user_email ) ) {
			return;
		}
	
		# build subject
		$t_subject = \Core\Email::build_subject( $p_visible_bug_data['email_bug'] );
	
		# build message
	
		$t_message = \Core\Lang::get_defaulted( $p_message_id, null );
	
		if( is_array( $p_header_optional_params ) ) {
			$t_message = vsprintf( $t_message, $p_header_optional_params );
		}
	
		if( ( $t_message !== null ) && ( !\Core\Utility::is_blank( $t_message ) ) ) {
			$t_message .= " \n";
		}
	
		$t_message .= \Core\Email::format_bug_message( $p_visible_bug_data );
	
		# build headers
		$t_bug_id = $p_visible_bug_data['email_bug'];
		$t_message_md5 = md5( $t_bug_id . $p_visible_bug_data['email_date_submitted'] );
		$t_mail_headers = array(
			'keywords' => $p_visible_bug_data['set_category'],
		);
		if( $p_message_id == 'email_notification_title_for_action_bug_submitted' ) {
			$t_mail_headers['Message-ID'] = $t_message_md5;
		} else {
			$t_mail_headers['In-Reply-To'] = $t_message_md5;
		}
	
		# send mail
		\Core\Email::store( $t_user_email, $t_subject, $t_message, $t_mail_headers );
	
		return;
	}
	
	/**
	 * Build the bug info part of the message
	 * @param array $p_visible_bug_data Bug data array to format.
	 * @return string
	 */
	static function format_bug_message( array $p_visible_bug_data ) {
		$t_normal_date_format = \Core\Config::mantis_get( 'normal_date_format' );
		$t_complete_date_format = \Core\Config::mantis_get( 'complete_date_format' );
	
		$t_email_separator1 = \Core\Config::mantis_get( 'email_separator1' );
		$t_email_separator2 = \Core\Config::mantis_get( 'email_separator2' );
		$t_email_padding_length = \Core\Config::mantis_get( 'email_padding_length' );
	
		$t_status = $p_visible_bug_data['email_status'];
	
		$p_visible_bug_data['email_date_submitted'] = date( $t_complete_date_format, $p_visible_bug_data['email_date_submitted'] );
		$p_visible_bug_data['email_last_modified'] = date( $t_complete_date_format, $p_visible_bug_data['email_last_modified'] );
	
		$p_visible_bug_data['email_status'] = \Core\Helper::get_enum_element( 'status', $t_status );
		$p_visible_bug_data['email_severity'] = \Core\Helper::get_enum_element( 'severity', $p_visible_bug_data['email_severity'] );
		$p_visible_bug_data['email_priority'] = \Core\Helper::get_enum_element( 'priority', $p_visible_bug_data['email_priority'] );
		$p_visible_bug_data['email_reproducibility'] = \Core\Helper::get_enum_element( 'reproducibility', $p_visible_bug_data['email_reproducibility'] );
	
		$t_message = $t_email_separator1 . " \n";
	
		if( isset( $p_visible_bug_data['email_bug_view_url'] ) ) {
			$t_message .= $p_visible_bug_data['email_bug_view_url'] . " \n";
			$t_message .= $t_email_separator1 . " \n";
		}
	
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_reporter' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_handler' );
		$t_message .= $t_email_separator1 . " \n";
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_project' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_bug' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_category' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_reproducibility' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_severity' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_priority' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_status' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_target_version' );
	
		# custom fields formatting
		foreach( $p_visible_bug_data['custom_fields'] as $t_custom_field_name => $t_custom_field_data ) {
			$t_message .= utf8_str_pad( \Core\Lang::get_defaulted( $t_custom_field_name, null ) . ': ', $t_email_padding_length, ' ', STR_PAD_RIGHT );
			$t_message .= string_custom_field_value_for_email( $t_custom_field_data['value'], $t_custom_field_data['type'] );
			$t_message .= " \n";
		}
	
		# end foreach custom field
	
		if( \Core\Config::mantis_get( 'bug_resolved_status_threshold' ) <= $t_status ) {
			$p_visible_bug_data['email_resolution'] = \Core\Helper::get_enum_element( 'resolution', $p_visible_bug_data['email_resolution'] );
			$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_resolution' );
			$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_fixed_in_version' );
		}
		$t_message .= $t_email_separator1 . " \n";
	
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_date_submitted' );
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_last_modified' );
		$t_message .= $t_email_separator1 . " \n";
	
		$t_message .= \Core\Email::format_attribute( $p_visible_bug_data, 'email_summary' );
	
		$t_message .= \Core\Lang::get( 'email_description' ) . ": \n" . $p_visible_bug_data['email_description'] . "\n";
	
		if( !\Core\Utility::is_blank( $p_visible_bug_data['email_steps_to_reproduce'] ) ) {
			$t_message .= "\n" . \Core\Lang::get( 'email_steps_to_reproduce' ) . ": \n" . $p_visible_bug_data['email_steps_to_reproduce'] . "\n";
		}
	
		if( !\Core\Utility::is_blank( $p_visible_bug_data['email_additional_information'] ) ) {
			$t_message .= "\n" . \Core\Lang::get( 'email_additional_information' ) . ": \n" . $p_visible_bug_data['email_additional_information'] . "\n";
		}
	
		if( isset( $p_visible_bug_data['relations'] ) ) {
			if( $p_visible_bug_data['relations'] != '' ) {
				$t_message .= $t_email_separator1 . "\n" . str_pad( \Core\Lang::get( 'bug_relationships' ), 20 ) . str_pad( \Core\Lang::get( 'id' ), 8 ) . \Core\Lang::get( 'summary' ) . "\n" . $t_email_separator2 . "\n" . $p_visible_bug_data['relations'];
			}
		}
	
		# Sponsorship
		if( isset( $p_visible_bug_data['sponsorship_total'] ) && ( $p_visible_bug_data['sponsorship_total'] > 0 ) ) {
			$t_message .= $t_email_separator1 . " \n";
			$t_message .= sprintf( \Core\Lang::get( 'total_sponsorship_amount' ), \Core\Sponsorship::format_amount( $p_visible_bug_data['sponsorship_total'] ) ) . "\n\n";
	
			if( isset( $p_visible_bug_data['sponsorships'] ) ) {
				foreach( $p_visible_bug_data['sponsorships'] as $t_sponsorship ) {
					$t_date_added = date( \Core\Config::mantis_get( 'normal_date_format' ), $t_sponsorship->date_submitted );
	
					$t_message .= $t_date_added . ': ';
					$t_message .= \Core\User::get_name( $t_sponsorship->user_id );
					$t_message .= ' (' . \Core\Sponsorship::format_amount( $t_sponsorship->amount ) . ')' . " \n";
				}
			}
		}
	
		$t_message .= $t_email_separator1 . " \n\n";
	
		# format bugnotes
		foreach( $p_visible_bug_data['bugnotes'] as $t_bugnote ) {
			$t_last_modified = date( $t_normal_date_format, $t_bugnote->last_modified );
	
			$t_formatted_bugnote_id = \Core\Bug\Note::format_id( $t_bugnote->id );
			$t_bugnote_link = \Core\String::process_bugnote_link( \Core\Config::mantis_get( 'bugnote_link_tag' ) . $t_bugnote->id, false, false, true );
	
			if( $t_bugnote->time_tracking > 0 ) {
				$t_time_tracking = ' ' . \Core\Lang::get( 'time_tracking' ) . ' ' . \Core\Database::minutes_to_hhmm( $t_bugnote->time_tracking ) . "\n";
			} else {
				$t_time_tracking = '';
			}
	
			if( \Core\User::exists( $t_bugnote->reporter_id ) ) {
				$t_access_level = \Core\Access::get_project_level( $p_visible_bug_data['email_project_id'], $t_bugnote->reporter_id );
				$t_access_level_string = ' (' . \Core\Helper::get_enum_element( 'access_levels', $t_access_level ) . ') - ';
			} else {
				$t_access_level_string = '';
			}
	
			$t_string = ' (' . $t_formatted_bugnote_id . ') ' . \Core\User::get_name( $t_bugnote->reporter_id ) . $t_access_level_string . $t_last_modified . "\n" . $t_time_tracking . ' ' . $t_bugnote_link;
	
			$t_message .= $t_email_separator2 . " \n";
			$t_message .= $t_string . " \n";
			$t_message .= $t_email_separator2 . " \n";
			$t_message .= $t_bugnote->note . " \n\n";
		}
	
		# format history
		if( array_key_exists( 'history', $p_visible_bug_data ) ) {
			$t_message .= \Core\Lang::get( 'bug_history' ) . " \n";
			$t_message .= utf8_str_pad( \Core\Lang::get( 'date_modified' ), 17 ) . utf8_str_pad( \Core\Lang::get( 'username' ), 15 ) . utf8_str_pad( \Core\Lang::get( 'field' ), 25 ) . utf8_str_pad( \Core\Lang::get( 'change' ), 20 ) . " \n";
	
			$t_message .= $t_email_separator1 . " \n";
	
			foreach( $p_visible_bug_data['history'] as $t_raw_history_item ) {
				$t_localized_item = \Core\History::localize_item( $t_raw_history_item['field'], $t_raw_history_item['type'], $t_raw_history_item['old_value'], $t_raw_history_item['new_value'], false );
	
				$t_message .= utf8_str_pad( date( $t_normal_date_format, $t_raw_history_item['date'] ), 17 ) . utf8_str_pad( $t_raw_history_item['username'], 15 ) . utf8_str_pad( $t_localized_item['note'], 25 ) . utf8_str_pad( $t_localized_item['change'], 20 ) . "\n";
			}
			$t_message .= $t_email_separator1 . " \n\n";
		}
	
		return $t_message;
	}
	
	/**
	 * if $p_visible_bug_data contains specified attribute the function
	 * returns concatenated translated attribute name and original
	 * attribute value. Else return empty string.
	 * @param array  $p_visible_bug_data Visible Bug Data array.
	 * @param string $p_attribute_id     Attribute ID.
	 * @return string
	 */
	static function format_attribute( array $p_visible_bug_data, $p_attribute_id ) {
		if( array_key_exists( $p_attribute_id, $p_visible_bug_data ) ) {
			return utf8_str_pad( \Core\Lang::get( $p_attribute_id ) . ': ', \Core\Config::mantis_get( 'email_padding_length' ), ' ', STR_PAD_RIGHT ) . $p_visible_bug_data[$p_attribute_id] . "\n";
		}
		return '';
	}
	
	/**
	 * Build the bug raw data visible for specified user to be translated and sent by email to the user
	 * (Filter the bug data according to user access level)
	 * return array with bug data. See usage in \Core\Email::format_bug_message(...)
	 * @param integer $p_user_id    A user identifier.
	 * @param integer $p_bug_id     A bug identifier.
	 * @param string  $p_message_id A message identifier.
	 * @return array
	 */
	static function build_visible_bug_data( $p_user_id, $p_bug_id, $p_message_id ) {
		# Override current user with user to construct bug data for.
		# This is to make sure that APIs that check against current user (e.g. relationship) work correctly.
		$t_current_user_id = \Core\Current_User::set( $p_user_id );
	
		$t_project_id = \Core\Bug::get_field( $p_bug_id, 'project_id' );
		$t_user_access_level = \Core\User::get_access_level( $p_user_id, $t_project_id );
		$t_user_bugnote_order = \Core\User\Pref::get_pref( $p_user_id, 'bugnote_order' );
		$t_user_bugnote_limit = \Core\User\Pref::get_pref( $p_user_id, 'email_bugnote_limit' );
	
		$t_row = \Core\Bug::get_extended_row( $p_bug_id );
		$t_bug_data = array();
	
		$t_bug_data['email_bug'] = $p_bug_id;
	
		if( $p_message_id !== 'email_notification_title_for_action_bug_deleted' ) {
			$t_bug_data['email_bug_view_url'] = \Core\String::get_bug_view_url_with_fqdn( $p_bug_id );
		}
	
		if( \Core\Access::compare_level( $t_user_access_level, \Core\Config::mantis_get( 'view_handler_threshold' ) ) ) {
			if( 0 != $t_row['handler_id'] ) {
				$t_bug_data['email_handler'] = \Core\User::get_name( $t_row['handler_id'] );
			} else {
				$t_bug_data['email_handler'] = '';
			}
		}
	
		$t_bug_data['email_reporter'] = \Core\User::get_name( $t_row['reporter_id'] );
		$t_bug_data['email_project_id'] = $t_row['project_id'];
		$t_bug_data['email_project'] = \Core\Project::get_field( $t_row['project_id'], 'name' );
	
		$t_category_name = \Core\Category::full_name( $t_row['category_id'], false );
		$t_bug_data['email_category'] = $t_category_name;
	
		$t_bug_data['email_date_submitted'] = $t_row['date_submitted'];
		$t_bug_data['email_last_modified'] = $t_row['last_updated'];
	
		$t_bug_data['email_status'] = $t_row['status'];
		$t_bug_data['email_severity'] = $t_row['severity'];
		$t_bug_data['email_priority'] = $t_row['priority'];
		$t_bug_data['email_reproducibility'] = $t_row['reproducibility'];
	
		$t_bug_data['email_resolution'] = $t_row['resolution'];
		$t_bug_data['email_fixed_in_version'] = $t_row['fixed_in_version'];
	
		if( !\Core\Utility::is_blank( $t_row['target_version'] ) && \Core\Access::compare_level( $t_user_access_level, \Core\Config::mantis_get( 'roadmap_view_threshold' ) ) ) {
			$t_bug_data['email_target_version'] = $t_row['target_version'];
		}
	
		$t_bug_data['email_summary'] = $t_row['summary'];
		$t_bug_data['email_description'] = $t_row['description'];
		$t_bug_data['email_additional_information'] = $t_row['additional_information'];
		$t_bug_data['email_steps_to_reproduce'] = $t_row['steps_to_reproduce'];
	
		$t_bug_data['set_category'] = '[' . $t_bug_data['email_project'] . '] ' . $t_category_name;
	
		$t_bug_data['custom_fields'] = custom_field_get_linked_fields( $p_bug_id, $t_user_access_level );
		$t_bug_data['bugnotes'] = \Core\Bug\Note::get_all_visible_bugnotes( $p_bug_id, $t_user_bugnote_order, $t_user_bugnote_limit, $p_user_id );
	
		# put history data
		if( ( ON == \Core\Config::mantis_get( 'history_default_visible' ) ) && \Core\Access::compare_level( $t_user_access_level, \Core\Config::mantis_get( 'view_history_threshold' ) ) ) {
			$t_bug_data['history'] = \Core\History::get_raw_events_array( $p_bug_id, $p_user_id );
		}
	
		# Sponsorship Information
		if( ( \Core\Config::mantis_get( 'enable_sponsorship' ) == ON ) && ( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'view_sponsorship_total_threshold' ), $p_bug_id, $p_user_id ) ) ) {
			$t_sponsorship_ids = \Core\Sponsorship::get_all_ids( $p_bug_id );
			$t_bug_data['sponsorship_total'] = \Core\Sponsorship::get_amount( $t_sponsorship_ids );
	
			if( \Core\Access::has_bug_level( \Core\Config::mantis_get( 'view_sponsorship_details_threshold' ), $p_bug_id, $p_user_id ) ) {
				$t_bug_data['sponsorships'] = array();
				foreach( $t_sponsorship_ids as $t_id ) {
					$t_bug_data['sponsorships'][] = \Core\Sponsorship::get( $t_id );
				}
			}
		}
	
		$t_bug_data['relations'] = \Core\Relationship::get_summary_text( $p_bug_id );
	
		\Core\Current_User::set( $t_current_user_id );
	
		return $t_bug_data;
	}

}