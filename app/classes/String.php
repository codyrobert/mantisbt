<?php
namespace Flickerbox;


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
 * String Processing API
 *
 * @package CoreAPI
 * @subpackage StringProcessingAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses email_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */



class String
{

	/**
	 * Preserve spaces at beginning of lines.
	 * Lines must be separated by \n rather than <br />
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function preserve_spaces_at_bol( $p_string ) {
		$t_lines = explode( "\n", $p_string );
		$t_line_count = count( $t_lines );
		for( $i = 0;$i < $t_line_count;$i++ ) {
			$t_count = 0;
			$t_prefix = '';
	
			$t_char = utf8_substr( $t_lines[$i], $t_count, 1 );
			$t_spaces = 0;
			while( ( $t_char == ' ' ) || ( $t_char == "\t" ) ) {
				if( $t_char == ' ' ) {
					$t_spaces++;
				} else {
					$t_spaces += 4;
				}
	
				# 1 tab = 4 spaces, can be configurable.
	
				$t_count++;
				$t_char = utf8_substr( $t_lines[$i], $t_count, 1 );
			}
	
			for( $j = 0;$j < $t_spaces;$j++ ) {
				$t_prefix .= '&#160;';
			}
	
			$t_lines[$i] = $t_prefix . utf8_substr( $t_lines[$i], $t_count );
		}
		return implode( "\n", $t_lines );
	}
	
	/**
	 * Prepare a string to be printed without being broken into multiple lines
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function no_break( $p_string ) {
		if( strpos( $p_string, ' ' ) !== false ) {
			return '<span class="nowrap">' . $p_string . '</span>';
		} else {
			return $p_string;
		}
	}
	
	/**
	 * Similar to nl2br, but fixes up a problem where new lines are doubled between
	 * html pre tags.
	 * additionally, wrap the text an $p_wrap character intervals if the config is set
	 * @param string  $p_string String to be processed.
	 * @param integer $p_wrap   Number of characters to wrap text at.
	 * @return string
	 */
	static function nl2br( $p_string, $p_wrap = 100 ) {
		$t_output = '';
		$t_pieces = preg_split( '/(<pre[^>]*>.*?<\/pre>)/is', $p_string, -1, PREG_SPLIT_DELIM_CAPTURE );
		if( isset( $t_pieces[1] ) ) {
			foreach( $t_pieces as $t_piece ) {
				if( preg_match( '/(<pre[^>]*>.*?<\/pre>)/is', $t_piece ) ) {
					$t_piece = preg_replace( '/<br[^>]*?>/', '', $t_piece );
	
					# @@@ thraxisp - this may want to be replaced by html_entity_decode (or equivalent)
					#     if other encoded characters are a problem
					$t_piece = preg_replace( '/&#160;/', ' ', $t_piece );
					if( ON == \Flickerbox\Config::mantis_get( 'wrap_in_preformatted_text' ) ) {
						$t_output .= preg_replace( '/([^\n]{' . $p_wrap . ',}?[\s]+)(?!<\/pre>)/', "$1\n", $t_piece );
					} else {
						$t_output .= $t_piece;
					}
				} else {
					$t_output .= nl2br( $t_piece );
				}
			}
			return $t_output;
		} else {
			return nl2br( $p_string );
		}
	}
	
	/**
	 * Prepare a multiple line string for display to HTML
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function display( $p_string ) {
		$t_data = \Flickerbox\Event::signal( 'EVENT_DISPLAY_TEXT', $p_string, true );
		return $t_data;
	}
	
	/**
	 * Prepare a single line string for display to HTML
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function display_line( $p_string ) {
		$t_data = \Flickerbox\Event::signal( 'EVENT_DISPLAY_TEXT', $p_string, false );
		return $t_data;
	}
	
	/**
	 * Prepare a string for display to HTML and add href anchors for URLs, emails
	 * and bug references
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function display_links( $p_string ) {
		$t_data = \Flickerbox\Event::signal( 'EVENT_DISPLAY_FORMATTED', $p_string, true );
		return $t_data;
	}
	
	/**
	 * Prepare a single line string for display to HTML and add href anchors for
	 * URLs, emails and bug references
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function display_line_links( $p_string ) {
		$t_data = \Flickerbox\Event::signal( 'EVENT_DISPLAY_FORMATTED', $p_string, false );
		return $t_data;
	}
	
	/**
	 * Prepare a string for display in rss
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function rss_links( $p_string ) {
		# rss can not start with &#160; which spaces will be replaced into by \Flickerbox\String::display().
		$t_string = trim( $p_string );
	
		$t_string = \Flickerbox\Event::signal( 'EVENT_DISPLAY_RSS', $t_string );
	
		# another escaping to escape the special characters created by the generated links
		return \Flickerbox\String::html_specialchars( $t_string );
	}
	
	/**
	 * Prepare a string for plain text display in email
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function email( $p_string ) {
		return \Flickerbox\String::strip_hrefs( $p_string );
	}
	
	/**
	 * Prepare a string for plain text display in email and add URLs for bug
	 * links
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function email_links( $p_string ) {
		return \Flickerbox\Event::signal( 'EVENT_DISPLAY_EMAIL', $p_string );
	}
	
	/**
	 * Process a string for display in a textarea box
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function textarea( $p_string ) {
		return \Flickerbox\String::html_specialchars( $p_string );
	}
	
	/**
	 * Process a string for display in a text box
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function attribute( $p_string ) {
		return \Flickerbox\String::html_specialchars( $p_string );
	}
	
	/**
	 * Process a string for inclusion in a URL as a GET parameter
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function url( $p_string ) {
		return rawurlencode( $p_string );
	}
	
	/**
	 * validate the url as part of this site before continuing
	 * @param string  $p_url             URL to be processed.
	 * @param boolean $p_return_absolute Whether to return the absolute URL to this Mantis instance.
	 * @return string
	 */
	static function sanitize_url( $p_url, $p_return_absolute = false ) {
		$t_url = strip_tags( urldecode( $p_url ) );
	
		$t_path = rtrim( \Flickerbox\Config::mantis_get( 'path' ), '/' );
		$t_short_path = rtrim( \Flickerbox\Config::mantis_get( 'short_path' ), '/' );
	
		$t_pattern = '(?:/*(?P<script>[^\?#]*))(?:\?(?P<query>[^#]*))?(?:#(?P<anchor>[^#]*))?';
	
		# Break the given URL into pieces for path, script, query, and anchor
		$t_type = 0;
		if( preg_match( '@^(?P<path>' . preg_quote( $t_path, '@' ) . ')' . $t_pattern . '$@', $t_url, $t_matches ) ) {
			$t_type = 1;
		} else if( !empty( $t_short_path )
				&& preg_match( '@^(?P<path>' . preg_quote( $t_short_path, '@' ) . ')' . $t_pattern . '$@', $t_url, $t_matches )
		) {
			$t_type = 2;
		} else if( preg_match( '@^(?P<path>)' . $t_pattern . '$@', $t_url, $t_matches ) ) {
			$t_type = 3;
		}
	
		# Check for URL's pointing to other domains
		if( 0 == $t_type || empty( $t_matches['script'] ) ||
			3 == $t_type && preg_match( '@(?:[^:]*)?:/*@', $t_url ) > 0 ) {
	
			return ( $p_return_absolute ? $t_path . '/' : '' ) . 'index.php';
		}
	
		# Start extracting regex matches
		$t_script = $t_matches['script'];
		$t_script_path = $t_matches['path'];
	
		# Clean/encode query params
		$t_query = '';
		if( isset( $t_matches['query'] ) ) {
			$t_pairs = array();
			parse_str( html_entity_decode( $t_matches['query'] ), $t_pairs );
	
			$t_clean_pairs = array();
			foreach( $t_pairs as $t_key => $t_value ) {
				if( is_array( $t_value ) ) {
					foreach( $t_value as $t_value_each ) {
						$t_clean_pairs[] .= rawurlencode( $t_key ) . '[]=' . rawurlencode( $t_value_each );
					}
				} else {
					$t_clean_pairs[] = rawurlencode( $t_key ) . '=' . rawurlencode( $t_value );
				}
			}
	
			if( !empty( $t_clean_pairs ) ) {
				$t_query = '?' . join( '&', $t_clean_pairs );
			}
		}
	
		# encode link anchor
		$t_anchor = '';
		if( isset( $t_matches['anchor'] ) ) {
			$t_anchor = '#' . rawurlencode( $t_matches['anchor'] );
		}
	
		# Return an appropriate re-combined URL string
		if( $p_return_absolute ) {
			return $t_path . '/' . $t_script . $t_query . $t_anchor;
		} else {
			return ( !empty( $t_script_path ) ? $t_script_path . '/' : '' ) . $t_script . $t_query . $t_anchor;
		}
	}
	
	/**
	 * Process $p_string, looking for bug ID references and creating bug view
	 * links for them.
	 *
	 * Returns the processed string.
	 *
	 * If $p_include_anchor is true, include the href tag, otherwise just insert
	 * the URL
	 *
	 * The bug tag ('#' by default) must be at the beginning of the string or
	 * preceeded by a character that is not a letter, a number or an underscore
	 *
	 * if $p_include_anchor = false, $p_fqdn is ignored and assumed to true.
	 * @param string  $p_string         String to be processed.
	 * @param boolean $p_include_anchor Whether to include the href tag or just the URL.
	 * @param boolean $p_detail_info    Whether to include more detailed information (e.g. title attribute / project) in the returned string.
	 * @param boolean $p_fqdn           Whether to return an absolute or relative link.
	 * @return string
	 */
	static function process_bug_link( $p_string, $p_include_anchor = true, $p_detail_info = true, $p_fqdn = false ) {
		global $g_string_process_bug_link_callback;
	
		$t_tag = \Flickerbox\Config::mantis_get( 'bug_link_tag' );
	
		# bail if the link tag is blank
		if( '' == $t_tag || $p_string == '' ) {
			return $p_string;
		}
	
		if( !isset( $g_string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] ) ) {
			if( $p_include_anchor ) {
				$g_string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function( '$p_array', '
											if( \Flickerbox\Bug::exists( (int)$p_array[2] ) ) {
												$t_project_id = \Flickerbox\Bug::get_field( (int)$p_array[2], \'project_id\' );
												if( \Flickerbox\Access::has_bug_level( \Flickerbox\Config::mantis_get( \'view_bug_threshold\', null, null, $t_project_id ), (int)$p_array[2] ) ) {
													return $p_array[1] . \Flickerbox\String::get_bug_view_link( (int)$p_array[2], null, ' . ( $p_detail_info ? 'true' : 'false' ) . ', ' . ( $p_fqdn ? 'true' : 'false' ) . ');
												}
											}
											return $p_array[0];
											' );
			} else {
				$g_string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] = create_function( '$p_array', '
											# We might as well create the link here even if the bug
											#  doesnt exist.  In the case above we dont want to do
											#  the summary lookup on a non-existant bug.  But here, we
											#  can create the link and by the time it is clicked on, the
											#  bug may exist.
											return $p_array[1] . \Flickerbox\String::get_bug_view_url_with_fqdn( (int)$p_array[2], null );
											' );
			}
		}
	
		$p_string = preg_replace_callback( '/(^|[^\w&])' . preg_quote( $t_tag, '/' ) . '(\d+)\b/', $g_string_process_bug_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn], $p_string );
		return $p_string;
	}
	
	/**
	 * Process $p_string, looking for bugnote ID references and creating bug view
	 * links for them.
	 *
	 * Returns the processed string.
	 *
	 * If $p_include_anchor is true, include the href tag, otherwise just insert
	 * the URL
	 *
	 * The bugnote tag ('~' by default) must be at the beginning of the string or
	 * preceeded by a character that is not a letter, a number or an underscore
	 *
	 * if $p_include_anchor = false, $p_fqdn is ignored and assumed to true.
	 * @param string  $p_string         String to be processed.
	 * @param boolean $p_include_anchor Whether to include the href tag or just the URL.
	 * @param boolean $p_detail_info    Whether to include more detailed information (e.g. title attribute / project) in the returned string.
	 * @param boolean $p_fqdn           Whether to return an absolute or relative link.
	 * @return string
	 */
	static function process_bugnote_link( $p_string, $p_include_anchor = true, $p_detail_info = true, $p_fqdn = false ) {
		global $g_string_process_bugnote_link_callback;
		$t_tag = \Flickerbox\Config::mantis_get( 'bugnote_link_tag' );
	
		# bail if the link tag is blank
		if( '' == $t_tag || $p_string == '' ) {
			return $p_string;
		}
	
		if( !isset( $g_string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] ) ) {
			if( $p_include_anchor ) {
				$g_string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] =
					create_function( '$p_array',
						'
						if( \Flickerbox\Bug\Note::exists( (int)$p_array[2] ) ) {
							$t_bug_id = \Flickerbox\Bug\Note::get_field( (int)$p_array[2], \'bug_id\' );
							if( \Flickerbox\Bug::exists( $t_bug_id ) ) {
								$g_project_override = \Flickerbox\Bug::get_field( $t_bug_id, \'project_id\' );
								if(   \Flickerbox\Access::compare_level(
											\Flickerbox\User::get_access_level( auth_get_current_user_id(),
											\Flickerbox\Bug::get_field( $t_bug_id, \'project_id\' ) ),
											\Flickerbox\Config::mantis_get( \'private_bugnote_threshold\' )
									   )
									|| \Flickerbox\Bug\Note::get_field( (int)$p_array[2], \'reporter_id\' ) == auth_get_current_user_id()
									|| \Flickerbox\Bug\Note::get_field( (int)$p_array[2], \'view_state\' ) == VS_PUBLIC
								) {
									$g_project_override = null;
									return $p_array[1] .
										\Flickerbox\String::get_bugnote_view_link(
											$t_bug_id,
											(int)$p_array[2],
											null,
											' . ( $p_detail_info ? 'true' : 'false' ) . ', ' . ( $p_fqdn ? 'true' : 'false' ) . '
										);
								}
								$g_project_override = null;
							}
						}
						return $p_array[0];
						' );
			} else {
				$g_string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn] =
					create_function(
						'$p_array',
						'
						# We might as well create the link here even if the bug
						#  doesnt exist.  In the case above we dont want to do
						#  the summary lookup on a non-existant bug.  But here, we
						#  can create the link and by the time it is clicked on, the
						#  bug may exist.
						$t_bug_id = \Flickerbox\Bug\Note::get_field( (int)$p_array[2], \'bug_id\' );
						if( \Flickerbox\Bug::exists( $t_bug_id ) ) {
							return $p_array[1] . \Flickerbox\String::get_bugnote_view_url_with_fqdn( $t_bug_id, (int)$p_array[2], null );
						} else {
							return $p_array[0];
						}
						' );
			}
		}
		$p_string = preg_replace_callback( '/(^|[^\w])' . preg_quote( $t_tag, '/' ) . '(\d+)\b/', $g_string_process_bugnote_link_callback[$p_include_anchor][$p_detail_info][$p_fqdn], $p_string );
		return $p_string;
	}
	
	/**
	 * Search email addresses and URLs for a few common protocols in the given
	 * string, and replace occurences with href anchors.
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function insert_hrefs( $p_string ) {
		static $s_url_regex = null;
		static $s_email_regex = null;
		static $s_anchor_regex = '/(<a[^>]*>.*?<\/a>)/is';
	
		if( !\Flickerbox\Config::mantis_get( 'html_make_links' ) ) {
			return $p_string;
		}
	
		# Initialize static variables
		if( is_null( $s_url_regex ) ) {
			# URL protocol. The regex accepts a small subset from the list of valid
			# IANA permanent and provisional schemes defined in
			# http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
			$t_url_protocol = '(?:https?|s?ftp|file|irc[6s]?|ssh|telnet|nntp|git|svn(?:\+ssh)?|cvs):\/\/';
	
			# %2A notation in url's
			$t_url_hex = '%[[:digit:]A-Fa-f]{2}';
	
			# valid set of characters that may occur in url scheme. Note: - should be first (A-F != -AF).
			$t_url_valid_chars       = '-_.,!~*\';\/?%^\\\\:@&={\|}+$#[:alnum:]\pL';
			$t_url_chars             = "(?:${t_url_hex}|[${t_url_valid_chars}\(\)\[\]])";
			$t_url_chars2            = "(?:${t_url_hex}|[${t_url_valid_chars}])";
			$t_url_chars_in_brackets = "(?:${t_url_hex}|[${t_url_valid_chars}\(\)])";
			$t_url_chars_in_parens   = "(?:${t_url_hex}|[${t_url_valid_chars}\[\]])";
	
			$t_url_part1 = $t_url_chars;
			$t_url_part2 = "(?:\(${t_url_chars_in_parens}*\)|\[${t_url_chars_in_brackets}*\]|${t_url_chars2})";
	
			$s_url_regex = "/(${t_url_protocol}(${t_url_part1}*?${t_url_part2}+))/su";
	
			# e-mail regex
			$s_email_regex = substr_replace( \Flickerbox\Email::regex_simple(), '(?:mailto:)?', 1, 0 );
		}
	
		# Find any URL in a string and replace it by a clickable link
		$p_string = preg_replace_callback(
			$s_url_regex,
			function ( $p_match ) {
				$t_url_href = 'href="' . rtrim( $p_match[1], '.' ) . '"';
				return "<a ${t_url_href}>${p_match[1]}</a> [<a ${t_url_href} target=\"_blank\">^</a>]";
			},
			$p_string
		);
	
		# Find any email addresses in the string and replace them with a clickable
		# mailto: link, making sure that we skip processing of any existing anchor
		# tags, to avoid parts of URLs such as https://user@example.com/ or
		# http://user:password@example.com/ to be not treated as an email.
		$t_pieces = preg_split( $s_anchor_regex, $p_string, null, PREG_SPLIT_DELIM_CAPTURE );
		$p_string = '';
		foreach( $t_pieces as $t_piece ) {
			if( preg_match( $s_anchor_regex, $t_piece ) ) {
				$p_string .= $t_piece;
			} else {
				$p_string .= preg_replace( $s_email_regex, '<a href="mailto:\0">\0</a>', $t_piece );
			}
		}
	
		return $p_string;
	}
	
	/**
	 * Detect href anchors in the string and replace them with URLs and email addresses
	 * @param string $p_string String to be processed.
	 * @return string
	 */
	static function strip_hrefs( $p_string ) {
		# First grab mailto: hrefs.  We don't care whether the URL is actually
		# correct - just that it's inside an href attribute.
		$p_string = preg_replace( '/<a\s[^\>]*href="mailto:([^\"]+)"[^\>]*>[^\<]*<\/a>/si', '\1', $p_string );
	
		# Then grab any other href
		$p_string = preg_replace( '/<a\s[^\>]*href="([^\"]+)"[^\>]*>[^\<]*<\/a>/si', '\1', $p_string );
		return $p_string;
	}
	
	/**
	 * This function looks for text with htmlentities
	 * like &lt;b&gt; and converts it into the corresponding
	 * html < b > tag based on the configuration information
	 * @param string  $p_string    String to be processed.
	 * @param boolean $p_multiline Whether the string being processed is a multi-line string.
	 * @return string
	 */
	static function restore_valid_html_tags( $p_string, $p_multiline = true ) {
		global $g_cache_html_valid_tags_single_line, $g_cache_html_valid_tags;
	
		if( \Flickerbox\Utility::is_blank( ( $p_multiline ? $g_cache_html_valid_tags : $g_cache_html_valid_tags_single_line ) ) ) {
			$t_html_valid_tags = \Flickerbox\Config::mantis_get( $p_multiline ? 'html_valid_tags' : 'html_valid_tags_single_line' );
	
			if( OFF === $t_html_valid_tags || \Flickerbox\Utility::is_blank( $t_html_valid_tags ) ) {
				return $p_string;
			}
	
			$t_tags = explode( ',', $t_html_valid_tags );
			foreach( $t_tags as $t_key => $t_value ) {
				if( !\Flickerbox\Utility::is_blank( $t_value ) ) {
					$t_tags[$t_key] = trim( $t_value );
				}
			}
			$t_tags = implode( '|', $t_tags );
			if( $p_multiline ) {
				$g_cache_html_valid_tags = $t_tags;
			} else {
				$g_cache_html_valid_tags_single_line = $t_tags;
			}
		} else {
			$t_tags = ( $p_multiline ? $g_cache_html_valid_tags : $g_cache_html_valid_tags_single_line );
		}
	
		$p_string = preg_replace( '/&lt;(' . $t_tags . ')\s*&gt;/ui', '<\\1>', $p_string );
		$p_string = preg_replace( '/&lt;\/(' . $t_tags . ')\s*&gt;/ui', '</\\1>', $p_string );
		$p_string = preg_replace( '/&lt;(' . $t_tags . ')\s*\/&gt;/ui', '<\\1 />', $p_string );
	
		return $p_string;
	}
	
	/**
	 * return the name of a bug page for the user
	 * account for the user preference and site override
	 * $p_action should be something like 'view', 'update', or 'report'
	 * If $p_user_id is null or not specified, use the current user
	 * @param string  $p_action  A valid action being performed - currently one of view, update or report.
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_page( $p_action, $p_user_id = null ) {
		switch( $p_action ) {
			case 'view':
				return 'bug_view_page.php';
			case 'update':
				return 'bug_update_page.php';
			case 'report':
				return 'bug_report_page.php';
		}
	
		trigger_error( ERROR_GENERIC, ERROR );
	}
	
	/**
	 * return an href anchor that links to a bug VIEW page for the given bug
	 * account for the user preference and site override
	 * @param integer $p_bug_id	     A bug identifier.
	 * @param integer $p_user_id     A valid user identifier.
	 * @param boolean $p_detail_info Whether to include more detailed information (e.g. title attribute / project) in the returned string.
	 * @param boolean $p_fqdn        Whether to return an absolute or relative link.
	 * @return string
	 */
	static function get_bug_view_link( $p_bug_id, $p_user_id = null, $p_detail_info = true, $p_fqdn = false ) {
		if( \Flickerbox\Bug::exists( $p_bug_id ) ) {
			$t_link = '<a href="';
			if( $p_fqdn ) {
				$t_link .= \Flickerbox\Config::get_global( 'path' );
			} else {
				$t_link .= \Flickerbox\Config::get_global( 'short_path' );
			}
			$t_link .= \Flickerbox\String::get_bug_view_url( $p_bug_id, $p_user_id ) . '"';
			if( $p_detail_info ) {
				$t_summary = \Flickerbox\String::attribute( \Flickerbox\Bug::get_field( $p_bug_id, 'summary' ) );
				$t_project_id = \Flickerbox\Bug::get_field( $p_bug_id, 'project_id' );
				$t_status = \Flickerbox\String::attribute( \Flickerbox\Helper::get_enum_element( 'status', \Flickerbox\Bug::get_field( $p_bug_id, 'status' ), $t_project_id ) );
				$t_link .= ' title="[' . $t_status . '] ' . $t_summary . '"';
	
				$t_resolved = \Flickerbox\Bug::get_field( $p_bug_id, 'status' ) >= \Flickerbox\Config::mantis_get( 'bug_resolved_status_threshold', null, null, $t_project_id );
				if( $t_resolved ) {
					$t_link .= ' class="resolved"';
				}
			}
			$t_link .= '>' . \Flickerbox\Bug::format_id( $p_bug_id ) . '</a>';
		} else {
			$t_link = \Flickerbox\Bug::format_id( $p_bug_id );
		}
	
		return $t_link;
	}
	
	/**
	 * return an href anchor that links to a bug VIEW page for the given bug
	 * account for the user preference and site override
	 * @param integer $p_bug_id      A bug identifier.
	 * @param integer $p_bugnote_id  A bugnote identifier.
	 * @param integer $p_user_id     A valid user identifier.
	 * @param boolean $p_detail_info Whether to include more detailed information (e.g. title attribute / project) in the returned string.
	 * @param boolean $p_fqdn        Whether to return an absolute or relative link.
	 * @return string
	 */
	static function get_bugnote_view_link( $p_bug_id, $p_bugnote_id, $p_user_id = null, $p_detail_info = true, $p_fqdn = false ) {
		$t_bug_id = (int)$p_bug_id;
	
		if( \Flickerbox\Bug::exists( $t_bug_id ) && \Flickerbox\Bug\Note::exists( $p_bugnote_id ) ) {
			$t_link = '<a href="';
			if( $p_fqdn ) {
				$t_link .= \Flickerbox\Config::get_global( 'path' );
			} else {
				$t_link .= \Flickerbox\Config::get_global( 'short_path' );
			}
	
			$t_link .= \Flickerbox\String::get_bugnote_view_url( $p_bug_id, $p_bugnote_id, $p_user_id ) . '"';
			if( $p_detail_info ) {
				$t_reporter = \Flickerbox\String::attribute( \Flickerbox\User::get_name( \Flickerbox\Bug\Note::get_field( $p_bugnote_id, 'reporter_id' ) ) );
				$t_update_date = \Flickerbox\String::attribute( date( \Flickerbox\Config::mantis_get( 'normal_date_format' ), ( \Flickerbox\Bug\Note::get_field( $p_bugnote_id, 'last_modified' ) ) ) );
				$t_link .= ' title="' . \Flickerbox\Bug::format_id( $t_bug_id ) . ': [' . $t_update_date . '] ' . $t_reporter . '"';
			}
	
			$t_link .= '>' . \Flickerbox\Bug::format_id( $t_bug_id ) . ':' . \Flickerbox\Bug\Note::format_id( $p_bugnote_id ) . '</a>';
		} else {
			$t_link = \Flickerbox\Bug\Note::format_id( $t_bug_id ) . ':' . \Flickerbox\Bug\Note::format_id( $p_bugnote_id );
		}
	
		return $t_link;
	}
	
	/**
	 * return the name and GET parameters of a bug VIEW page for the given bug
	 * @param integer $p_bug_id A bug identifier.
	 * @return string
	 */
	static function get_bug_view_url( $p_bug_id ) {
		return 'view.php?id=' . $p_bug_id;
	}
	
	/**
	 * return the name and GET parameters of a bug VIEW page for the given bug
	 * @param integer $p_bug_id     A bug identifier.
	 * @param integer $p_bugnote_id A bugnote identifier.
	 * @return string
	 */
	static function get_bugnote_view_url( $p_bug_id, $p_bugnote_id ) {
		return 'view.php?id=' . $p_bug_id . '#c' . $p_bugnote_id;
	}
	
	/**
	 * return the name and GET parameters of a bug VIEW page for the given bug
	 * account for the user preference and site override
	 * The returned url includes the fully qualified domain, hence it is suitable to be included
	 * in emails.
	 * @param integer $p_bug_id     A bug identifier.
	 * @param integer $p_bugnote_id A bug note identifier.
	 * @param integer $p_user_id    A valid user identifier.
	 * @return string
	 */
	static function get_bugnote_view_url_with_fqdn( $p_bug_id, $p_bugnote_id, $p_user_id = null ) {
		return \Flickerbox\Config::mantis_get( 'path' ) . \Flickerbox\String::get_bug_view_url( $p_bug_id, $p_user_id ) . '#c' . $p_bugnote_id;
	}
	
	/**
	 * return the name and GET parameters of a bug VIEW page for the given bug
	 * account for the user preference and site override
	 * The returned url includes the fully qualified domain, hence it is suitable to be included in emails.
	 * @param integer $p_bug_id  A bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_view_url_with_fqdn( $p_bug_id, $p_user_id = null ) {
		return \Flickerbox\Config::mantis_get( 'path' ) . \Flickerbox\String::get_bug_view_url( $p_bug_id, $p_user_id );
	}
	
	/**
	 * return the name of a bug VIEW page for the user
	 * account for the user preference and site override
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_view_page( $p_user_id = null ) {
		return \Flickerbox\String::get_bug_page( 'view', $p_user_id );
	}
	
	/**
	 * return an href anchor that links to a bug UPDATE page for the given bug
	 * account for the user preference and site override
	 * @param integer $p_bug_id  A bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_update_link( $p_bug_id, $p_user_id = null ) {
		$t_summary = \Flickerbox\String::attribute( \Flickerbox\Bug::get_field( $p_bug_id, 'summary' ) );
		return '<a href="' . \Flickerbox\Helper::mantis_url( \Flickerbox\String::get_bug_update_url( $p_bug_id, $p_user_id ) ) . '" title="' . $t_summary . '">' . \Flickerbox\Bug::format_id( $p_bug_id ) . '</a>';
	}
	
	/**
	 * return the name and GET parameters of a bug UPDATE page for the given bug
	 * account for the user preference and site override
	 * @param integer $p_bug_id  A bug identifier.
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_update_url( $p_bug_id, $p_user_id = null ) {
		return \Flickerbox\String::get_bug_update_page( $p_user_id ) . '?bug_id=' . $p_bug_id;
	}
	
	/**
	 * return the name of a bug UPDATE page for the user
	 * account for the user preference and site override
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_update_page( $p_user_id = null ) {
		return \Flickerbox\String::get_bug_page( 'update', $p_user_id );
	}
	
	/**
	 * return an href anchor that links to a bug REPORT page for the given bug
	 * account for the user preference and site override
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_report_link( $p_user_id = null ) {
		return '<a href="' . \Flickerbox\Helper::mantis_url( \Flickerbox\String::get_bug_report_url( $p_user_id ) ) . '">' . \Flickerbox\Lang::get( 'report_bug_link' ) . '</a>';
	}
	
	/**
	 * return the name and GET parameters of a bug REPORT page for the given bug
	 * account for the user preference and site override
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_report_url( $p_user_id = null ) {
		return \Flickerbox\String::get_bug_report_page( $p_user_id );
	}
	
	/**
	 * return the name of a bug REPORT page for the user
	 * account for the user preference and site override
	 * @param integer $p_user_id A valid user identifier.
	 * @return string
	 */
	static function get_bug_report_page( $p_user_id = null ) {
		return \Flickerbox\String::get_bug_page( 'report', $p_user_id );
	}
	
	/**
	 * return the complete URL link to the verify page including the confirmation hash
	 * @param integer $p_user_id      A valid user identifier.
	 * @param string  $p_confirm_hash The confirmation hash value to include in the link.
	 * @return string
	 */
	static function get_confirm_hash_url( $p_user_id, $p_confirm_hash ) {
		$t_path = \Flickerbox\Config::mantis_get( 'path' );
		return $t_path . 'verify.php?id=' . \Flickerbox\String::url( $p_user_id ) . '&confirm_hash=' . \Flickerbox\String::url( $p_confirm_hash );
	}
	
	/**
	 * Format date for display
	 * @param integer $p_date A date value to process.
	 * @return string
	 */
	static function format_complete_date( $p_date ) {
		return date( \Flickerbox\Config::mantis_get( 'complete_date_format' ), $p_date );
	}
	
	/**
	 * Shorten a string for display on a dropdown to prevent the page rendering too wide
	 * ref issues #4630, #5072, #5131
	 * @param string  $p_string The string to process.
	 * @param integer $p_max    The maximum length of the string to use.
	 *                          If not set, defaults to max_dropdown_length configuration variable.
	 * @return string
	 */
	static function shorten( $p_string, $p_max = null ) {
		if( $p_max === null ) {
			$t_max = \Flickerbox\Config::mantis_get( 'max_dropdown_length' );
		} else {
			$t_max = (int)$p_max;
		}
	
		if( ( $t_max > 0 ) && ( utf8_strlen( $p_string ) > $t_max ) ) {
			$t_pattern = '/([\s|.|,|\-|_|\/|\?]+)/';
			$t_bits = preg_split( $t_pattern, $p_string, -1, PREG_SPLIT_DELIM_CAPTURE );
	
			$t_string = '';
			$t_last = $t_bits[count( $t_bits ) - 1];
			$t_last_len = strlen( $t_last );
	
			if( count( $t_bits ) == 1 ) {
				$t_string .= utf8_substr( $t_last, 0, $t_max - 3 );
				$t_string .= '...';
			} else {
				foreach( $t_bits as $t_bit ) {
					if( ( utf8_strlen( $t_string ) + utf8_strlen( $t_bit ) + $t_last_len + 3 <= $t_max ) || ( strpos( $t_bit, '.,-/?' ) > 0 ) ) {
						$t_string .= $t_bit;
					} else {
						break;
					}
				}
				$t_string .= '...' . $t_last;
			}
			return $t_string;
		} else {
			return $p_string;
		}
	}
	
	/**
	 * Normalize a string by removing leading, trailing and excessive internal spaces
	 * note a space is used as the pattern instead of '\s' to make it work with UTF-8 strings
	 * @param string $p_string The string to process.
	 * @return string
	 */
	static function normalize( $p_string ) {
		return preg_replace( '/ +/', ' ', trim( $p_string ) );
	}
	
	/**
	 * remap a field name to a string name (for sort filter)
	 * @param string $p_string The string to process.
	 * @return string
	 */
	static function get_field_name( $p_string ) {
		$t_map = array(
			'attachment_count' => 'attachments',
			'category_id' => 'category',
			'handler_id' => 'assigned_to',
			'id' => 'email_bug',
			'last_updated' => 'updated',
			'project_id' => 'email_project',
			'reporter_id' => 'reporter',
			'view_state' => 'view_status',
		);
	
		$t_string = $p_string;
		if( isset( $t_map[$p_string] ) ) {
			$t_string = $t_map[$p_string];
		}
		return \Flickerbox\Lang::get_defaulted( $t_string );
	}
	
	/**
	 * Calls htmlentities on the specified string, passing along
	 * the current character set.
	 * @param string $p_string The string to process.
	 * @return string
	 */
	static function html_entities( $p_string ) {
		return htmlentities( $p_string, ENT_COMPAT, 'utf-8' );
	}
	
	/**
	 * Calls htmlspecialchars on the specified string, handling utf8
	 * @param string $p_string The string to process.
	 * @return string
	 */
	static function html_specialchars( $p_string ) {
		# Remove any invalid character from the string per XML 1.0 specification
		# http://www.w3.org/TR/2008/REC-xml-20081126/#NT-Char
		$p_string = preg_replace( '/[^\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $p_string );
	
		# achumakov: @ added to avoid warning output in unsupported codepages
		# e.g. 8859-2, windows-1257, Korean, which are treated as 8859-1.
		# This is VERY important for Eastern European, Baltic and Korean languages
		return preg_replace( '/&amp;(#[0-9]+|[a-z]+);/i', '&$1;', @htmlspecialchars( $p_string, ENT_COMPAT, 'utf-8' ) );
	}
	
	/**
	 * Prepares a string to be used as part of header().
	 * @param string $p_string The string to process.
	 * @return string
	 */
	static function prepare_header( $p_string ) {
		$t_string= explode( "\n", $p_string, 2 );
		$t_string= explode( "\r", $t_string[0], 2 );
		return $t_string[0];
	}

}