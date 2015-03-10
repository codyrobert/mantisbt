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
 * Delete a tag
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'tag_delete' );

\Core\Access::ensure_global_level( \Core\Config::mantis_get( 'tag_edit_threshold' ) );

$f_tag_id = \Core\GPC::get_int( 'tag_id' );
$t_tag_row = \Core\Tag::get( $f_tag_id );

\Core\Helper::ensure_confirmed( \Core\Lang::get( 'tag_delete_message' ), \Core\Lang::get( 'tag_delete_button' ) );

\Core\Tag::delete( $f_tag_id );

\Core\Form::security_purge( 'tag_delete' );

\Core\Print_Util::successful_redirect( 'manage_tags_page.php' );
