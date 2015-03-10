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
 * Tag Update
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses tag_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );

\Flickerbox\Form::security_validate( 'tag_update' );

\Flickerbox\Compress::enable();

$f_tag_id = \Flickerbox\GPC::get_int( 'tag_id' );
$t_tag_row = \Flickerbox\Tag::get( $f_tag_id );

if( !( \Flickerbox\Access::has_global_level( \Flickerbox\Config::mantis_get( 'tag_edit_threshold' ) )
	|| ( auth_get_current_user_id() == $t_tag_row['user_id'] )
		&& \Flickerbox\Access::has_global_level( \Flickerbox\Config::mantis_get( 'tag_edit_own_threshold' ) ) ) ) {
	\Flickerbox\Access::denied();
}

if( \Flickerbox\Access::has_global_level( \Flickerbox\Config::mantis_get( 'tag_edit_threshold' ) ) ) {
	$f_new_user_id = \Flickerbox\GPC::get_int( 'user_id', $t_tag_row['user_id'] );
} else {
	$f_new_user_id = $t_tag_row['user_id'];
}

$f_new_name = \Flickerbox\GPC::get_string( 'name', $t_tag_row['name'] );
$f_new_description = \Flickerbox\GPC::get_string( 'description', $t_tag_row['description'] );

\Flickerbox\Tag::update( $f_tag_id, $f_new_name, $f_new_user_id, $f_new_description );

\Flickerbox\Form::security_purge( 'tag_update' );

$t_url = 'tag_view_page.php?tag_id='.$f_tag_id;
\Flickerbox\Print_Util::successful_redirect( $t_url );
