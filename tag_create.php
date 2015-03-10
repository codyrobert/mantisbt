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
 * Tag Create
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'tag_create' );

$f_tag_name = \Core\GPC::get_string( 'name' );
$f_tag_description = \Core\GPC::get_string( 'description' );

$t_tag_user = \Core\Auth::get_current_user_id();

if( !is_null( $f_tag_name ) ) {
	$t_tags = \Core\Tag::parse_string( $f_tag_name );
	foreach ( $t_tags as $t_tag_row ) {
		if( -1 == $t_tag_row['id'] ) {
			\Core\Tag::create( $t_tag_row['name'], $t_tag_user, $f_tag_description );
		}
	}
}

\Core\Form::security_purge( 'tag_create' );
\Core\Print_Util::successful_redirect( 'manage_tags_page.php' );

