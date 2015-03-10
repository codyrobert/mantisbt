<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Remove Bug Revision
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_revision_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'bug_revision_drop' );

$f_revision_id = \Core\GPC::get_int( 'id' );
$t_revision = \Core\Bug\Revision::get( $f_revision_id );

\Core\Access::ensure_bug_level( \Core\Config::mantis_get( 'bug_revision_drop_threshold' ), $t_revision['bug_id'] );
\Core\Helper::ensure_confirmed( \Core\Lang::get( 'confirm_revision_drop' ), \Core\Lang::get( 'revision_drop' ) );

\Core\Bug\Revision::drop( $f_revision_id );
\Core\Form::security_purge( 'bug_revision_drop' );

\Core\Print_Util::successful_redirect_to_bug( $t_revision['bug_id'] );

