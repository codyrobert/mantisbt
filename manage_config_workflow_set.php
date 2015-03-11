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
 * Manage configuration for workflow Config
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );

\Core\Form::security_validate( 'manage_config_workflow_set' );

\Core\Auth::reauthenticate();

/**
 * Retrieves the value of configuration option for the project's parent
 * (ALL_PROJECTS level if project, or file-level if all projects)
 * @param integer $p_project Project.
 * @param string  $p_option  Configuration option to retrieve.
 * @return mixed configuration option value
 */
function config_get_parent( $p_project, $p_option ) {
	if( $p_project == ALL_PROJECTS ) {
		return \Core\Config::get_global( $p_option );
	} else {
		return \Core\Config::mantis_get( $p_option, null, null, ALL_PROJECTS );
	}
}


$t_can_change_level = min( \Core\Config::get_access( 'notify_flags' ), \Core\Config::get_access( 'default_notify_flags' ) );
\Core\Access::ensure_project_level( $t_can_change_level );

$t_redirect_url = 'manage_config_workflow_page.php';
$t_project = \Core\Helper::get_current_project();
$t_access = \Core\Current_User::get_access_level();

\Core\HTML::page_top( \Core\Lang::get( 'manage_workflow_config' ), $t_redirect_url );

# process the changes to threshold values
$t_valid_thresholds = array(
	'bug_submit_status',
	'bug_resolved_status_threshold',
	'bug_reopen_status',
);

foreach( $t_valid_thresholds as $t_threshold ) {
	$t_access_current = \Core\Config::get_access( $t_threshold );
	if( $t_access >= $t_access_current ) {
		$f_value = \Core\GPC::get( 'threshold_' . $t_threshold );
		$t_value_current = \Core\Config::mantis_get( $t_threshold );
		$f_access = \Core\GPC::get( 'access_' . $t_threshold );
		if( $f_value == $t_value_current && $f_access == $t_access_current ) {
			# If new value is equal to parent and access has not changed
			\Core\Config::delete( $t_threshold, ALL_USERS, $t_project );
		} else if( $f_value != $t_value_current || $f_access != $t_access_current ) {
			# Set config if value or access have changed
			\Core\Config::mantis_set( $t_threshold, $f_value, NO_USER, $t_project, $f_access );
		}
	}
}

$t_enum_status = \Core\MantisEnum::getAssocArrayIndexedByValues( \Core\Config::mantis_get( 'status_enum_string' ) );

# process the workflow by reversing the flags to a matrix and creating the appropriate string
if( \Core\Config::get_access( 'status_enum_workflow' ) <= $t_access ) {
	$f_value = \Core\GPC::get( 'flag', array() );
	$f_access = \Core\GPC::get( 'workflow_access' );
	$t_matrix = array();

	foreach( $f_value as $t_transition ) {
		list( $t_from, $t_to ) = explode( ':', $t_transition );
		$t_matrix[$t_from][$t_to] = '';
	}
	$t_workflow = array();
	foreach( $t_enum_status as $t_state => $t_label ) {
		$t_workflow_row = '';
		$t_default = \Core\GPC::get_int( 'default_' . $t_state );
		if( isset( $t_matrix[$t_state] ) && isset( $t_matrix[$t_state][$t_default] ) ) {
			$t_workflow_row .= $t_default . ':' . \Core\Helper::get_enum_element( 'status', $t_default );
			unset( $t_matrix[$t_state][$t_default] );
			$t_first = false;
		} else {
			# error default state isn't in the matrix
			echo '<p>' . sprintf( \Core\Lang::get( 'default_not_in_flow' ), \Core\Helper::get_enum_element( 'status', $t_default ), \Core\Helper::get_enum_element( 'status', $t_state ) )  . '</p>';
			$t_first = true;
		}
		if( isset( $t_matrix[$t_state] ) ) {
			foreach ( $t_matrix[$t_state] as $t_next_state => $t_junk ) {
				if( false == $t_first ) {
					$t_workflow_row .= ',';
				}
				$t_workflow_row .= $t_next_state . ':' . \Core\Helper::get_enum_element( 'status', $t_next_state );
				$t_first = false;
			}
		}
		if( '' <> $t_workflow_row ) {
			$t_workflow[$t_state] = $t_workflow_row;
		}
	}

	# Get the parent's workflow, if not set default to all transitions
	$t_access_current = \Core\Config::get_access( 'status_enum_workflow' );
	$t_workflow_parent = config_get_parent( $t_project, 'status_enum_workflow' );
	if( 0 == count( $t_workflow_parent ) ) {
		foreach( $t_enum_status as $t_status => $t_label ) {
			$t_temp_workflow = array();
			foreach( $t_enum_status as $t_next => $t_next_label ) {
				if( $t_status != $t_next ) {
					$t_temp_workflow[] = $t_next . ':' . $t_next_label;
				}
			}
			$t_workflow_parent[$t_status] = implode( ',', $t_temp_workflow );
		}
	}

	if( $t_workflow == $t_workflow_parent && $f_access == $t_access_current ) {
		# If new value is equal to parent and access has not changed
		\Core\Config::delete( 'status_enum_workflow', ALL_USERS, $t_project );
	} else if( $t_workflow != \Core\Config::mantis_get( 'status_enum_workflow' ) || $f_access != $t_access_current ) {
		# Set config if value or access have changed
		\Core\Config::mantis_set( 'status_enum_workflow', $t_workflow, NO_USER, $t_project, $f_access );
	}
}

# process the access level changes
if( \Core\Config::get_access( 'status_enum_workflow' ) <= $t_access ) {
	# get changes to access level to change these values
	$f_access = \Core\GPC::get( 'status_access' );
	$t_access_current = \Core\Config::get_access( 'status_enum_workflow' );

	# Build access level reference arrays (parent level and current config)
	$t_set_parent = config_get_parent( $t_project, 'set_status_threshold' );
	$t_set_current = \Core\Config::mantis_get( 'set_status_threshold' );
	$t_bug_submit_status = \Core\Config::mantis_get( 'bug_submit_status' );
	foreach( $t_enum_status as $t_status => $t_status_label ) {
		if( !isset( $t_set_parent[$t_status] ) ) {
			if( $t_bug_submit_status == $t_status ) {
				$t_set_parent[$t_status] = config_get_parent( $t_project, 'report_bug_threshold' );
			} else {
				$t_set_parent[$t_status] = config_get_parent( $t_project, 'update_bug_status_threshold' );
			}
		}
		if( !isset( $t_set_current[$t_status] ) ) {
			if( $t_bug_submit_status == $t_status ) {
				$t_set_current[$t_status] = \Core\Config::mantis_get( 'report_bug_threshold' );
			} else {
				$t_set_current[$t_status] = \Core\Config::mantis_get( 'update_bug_status_threshold' );
			}
		}
	}

	# walk through the status labels to set the status threshold
	$t_set_new = array();
	foreach( $t_enum_status as $t_status_id => $t_status_label ) {
		$f_level = \Core\GPC::get_int( 'access_change_' . $t_status_id );
		if( \Core\Config::mantis_get( 'bug_submit_status' ) == $t_status_id ) {
			if( $f_level != \Core\Config::mantis_get( 'report_bug_threshold' ) ) {
				\Core\Config::mantis_set( 'report_bug_threshold', (int)$f_level, ALL_USERS, $t_project, $f_access );
			} else {
				\Core\Config::delete( 'report_bug_threshold', ALL_USERS, $t_project );
			}
			unset( $t_set_parent[$t_status_id] );
			unset( $t_set_current[$t_status_id] );
		} else {
			$t_set_new[$t_status_id] = $f_level;
		}
	}

	if( $t_set_new == $t_set_parent && $f_access == $t_access_current ) {
		# If new value is equal to parent and access has not changed
		\Core\Config::delete( 'set_status_threshold', ALL_USERS, $t_project );
	} else if( $t_set_new != $t_set_current || $f_access != $t_access_current ) {
		# Set config if value or access have changed
		\Core\Config::mantis_set( 'set_status_threshold', $t_set_new, ALL_USERS, $t_project, $f_access );
	}
}

\Core\Form::security_purge( 'manage_config_workflow_set' );

\Core\HTML::operation_successful( $t_redirect_url );

\Core\HTML::page_bottom();
