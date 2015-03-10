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
 * Generate News Feed RSS
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses project_api.php
 * @uses rss_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses rssbuilder/class.RSSBuilder.inc.php
 */

require_once( 'core.php' );
require_lib( 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.RSSBuilder.inc.php' );

$f_username = \Flickerbox\GPC::get_string( 'username', null );
$f_key = \Flickerbox\GPC::get_string( 'key', null );
$f_project_id = \Flickerbox\GPC::get_int( 'project_id', ALL_PROJECTS );

\Flickerbox\News::ensure_enabled();

# make sure RSS syndication is enabled.
if( OFF == \Flickerbox\Config::mantis_get( 'rss_enabled' ) ) {
	\Flickerbox\Access::denied();
}

# authenticate the user
if( $f_username !== null ) {
	if( !\Flickerbox\RSS::login( $f_username, $f_key ) ) {
		\Flickerbox\Access::denied();
	}
} else {
	if( OFF == \Flickerbox\Config::mantis_get( 'allow_anonymous_login' ) ) {
		\Flickerbox\Access::denied();
	}
}

# Make sure that the current user has access to the selected project (if not ALL PROJECTS).
if( $f_project_id != ALL_PROJECTS ) {
	\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'view_bug_threshold', null, null, $f_project_id ), $f_project_id );
}

# construct rss file

$t_encoding = 'utf-8';
$t_about = \Flickerbox\Config::mantis_get( 'path' );
$t_title = string_rss_links( \Flickerbox\Config::mantis_get( 'window_title' ) . ' - ' . \Flickerbox\Lang::get( 'news' ) );

if( $f_username !== null ) {
	$t_title .= ' - (' . $f_username . ')';
}

$t_description = $t_title;
$t_image_link = \Flickerbox\Config::mantis_get( 'path' ) . 'images/mantis_logo_button.gif';

# only rss 2.0
$t_category = string_rss_links( \Flickerbox\Project::get_name( $f_project_id ) );

# in minutes (only rss 2.0)
$t_cache = '60';

$t_rssfile = new RSSBuilder( $t_encoding, $t_about, $t_title, $t_description,
				$t_image_link, $t_category, $t_cache );

# person, an organization, or a service
$t_publisher = '';

# person, an organization, or a service
$t_creator = '';

$t_date = (string)date( 'r' );
$t_language = \Flickerbox\Lang::get( 'phpmailer_language' );
$t_rights = '';

# spatial location , temporal period or jurisdiction
$t_coverage = (string)'';

# person, an organization, or a service
$t_contributor = (string)'';

$t_rssfile->addDCdata( $t_publisher, $t_creator, $t_date, $t_language, $t_rights, $t_coverage, $t_contributor );

# hourly / daily / weekly / ...
$t_period = (string)'daily';

# every X hours/days/...
$t_frequency = (int)1;

$t_base = (string)date( 'Y-m-d\TH:i:sO' );

# add missing : in the O part of the date.  @todo PHP 5 supports a 'c' format which will output the format
# exactly as we want it.
# 2002-10-02T10:00:00-0500 -> 2002-10-02T10:00:00-05:00
$t_base = utf8_substr( $t_base, 0, 22 ) . ':' . utf8_substr( $t_base, -2 );

$t_rssfile->addSYdata( $t_period, $t_frequency, $t_base );

$t_news_rows = \Flickerbox\News::get_limited_rows( 0, $f_project_id );
$t_news_count = count( $t_news_rows );

# Loop through results
for( $i = 0; $i < $t_news_count; $i++ ) {
	$t_row = $t_news_rows[$i];
	extract( $t_row, EXTR_PREFIX_ALL, 'v' );

	# skip news item if private, or
	# belongs to a private project (will only happen
	if( VS_PRIVATE == $v_view_state ) {
		continue;
	}

	$v_headline 	= string_rss_links( $v_headline );
	$v_body 	= string_rss_links( $v_body );

	$t_about = $t_link = \Flickerbox\Config::mantis_get( 'path' ) . 'news_view_page.php?news_id=' . $v_id;
	$t_title = $v_headline;
	$t_description = $v_body;

	# optional DC value
	$t_subject = $t_title;

	# optional DC value
	$t_date = $v_date_posted;

	# author of item
	$t_author = '';
	if( \Flickerbox\Access::has_global_level( \Flickerbox\Config::mantis_get( 'show_user_email_threshold' ) ) ) {
		$t_author_name = string_rss_links( \Flickerbox\User::get_name( $v_poster_id ) );
		$t_author_email = \Flickerbox\User::get_field( $v_poster_id, 'email' );

		if( !\Flickerbox\Utility::is_blank( $t_author_email ) ) {
			if( !\Flickerbox\Utility::is_blank( $t_author_name ) ) {
				$t_author = $t_author_name . ' &lt;' . $t_author_email . '&gt;';
			} else {
				$t_author = $t_author_email;
			}
		}
	}

	# $comments = 'http://www.example.com/sometext.php?somevariable=somevalue&comments=1';	# url to comment page rss 2.0 value
	$t_comments = '';

	# optional mod_im value for dispaying a different pic for every item
	$t_image = '';

	$t_rssfile->addRSSItem( $t_about, $t_title, $t_link, $t_description, $t_subject, $t_date, $t_author, $t_comments, $t_image );
}

# @todo consider making this a configuration option - 0.91 / 1.0 / 2.0
$t_version = '2.0';

$t_rssfile->outputRSS( $t_version );
