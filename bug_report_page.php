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
 * This file POSTs data to report_bug.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses profile_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once( 'core.php' );
require_api( 'custom_field_api.php' );

$f_master_bug_id = \Flickerbox\GPC::get_int( 'm_id', 0 );

if( $f_master_bug_id > 0 ) {
	# master bug exists...
	\Flickerbox\Bug::ensure_exists( $f_master_bug_id );

	# master bug is not read-only...
	if( \Flickerbox\Bug::is_readonly( $f_master_bug_id ) ) {
		\Flickerbox\Error::parameters( $f_master_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$t_bug = \Flickerbox\Bug::get( $f_master_bug_id, true );

	#@@@ (thraxisp) Note that the master bug is cloned into the same project as the master, independent of
	#       what the current project is set to.
	if( $t_bug->project_id != \Flickerbox\Helper::get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
		$t_changed_project = true;
	} else {
		$t_changed_project = false;
	}

	\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'report_bug_threshold' ) );

	$f_build				= $t_bug->build;
	$f_platform				= $t_bug->platform;
	$f_os					= $t_bug->os;
	$f_os_build				= $t_bug->os_build;
	$f_product_version		= $t_bug->version;
	$f_target_version		= $t_bug->target_version;
	$f_profile_id			= 0;
	$f_handler_id			= $t_bug->handler_id;

	$f_category_id			= $t_bug->category_id;
	$f_reproducibility		= $t_bug->reproducibility;
	$f_eta					= $t_bug->eta;
	$f_severity				= $t_bug->severity;
	$f_priority				= $t_bug->priority;
	$f_summary				= $t_bug->summary;
	$f_description			= $t_bug->description;
	$f_steps_to_reproduce	= $t_bug->steps_to_reproduce;
	$f_additional_info		= $t_bug->additional_information;
	$f_view_state			= (int)$t_bug->view_state;
	$f_due_date				= $t_bug->due_date;

	$t_project_id			= $t_bug->project_id;
} else {
	# Get Project Id and set it as current
	$t_current_project = \Flickerbox\Helper::get_current_project();
	$t_project_id = \Flickerbox\GPC::get_int( 'project_id', $t_current_project );

	# If all projects, use default project if set
	$t_default_project = \Flickerbox\User\Pref::get_pref( \Flickerbox\Auth::get_current_user_id(), 'default_project' );
	if( ALL_PROJECTS == $t_project_id && ALL_PROJECTS != $t_default_project ) {
		$t_project_id = $t_default_project;
	}

	if( ( ALL_PROJECTS == $t_project_id || \Flickerbox\Project::exists( $t_project_id ) )
	 && $t_project_id != $t_current_project
	) {
		\Flickerbox\Helper::set_current_project( $t_project_id );
		# Reloading the page is required so that the project browser
		# reflects the new current project
		\Flickerbox\Print_Util::header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
	}

	# New issues cannot be reported for the 'All Project' selection
	if( ALL_PROJECTS == $t_current_project ) {
		\Flickerbox\Print_Util::header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
	}

	\Flickerbox\Access::ensure_project_level( \Flickerbox\Config::mantis_get( 'report_bug_threshold' ) );

	$f_build				= \Flickerbox\GPC::get_string( 'build', '' );
	$f_platform				= \Flickerbox\GPC::get_string( 'platform', '' );
	$f_os					= \Flickerbox\GPC::get_string( 'os', '' );
	$f_os_build				= \Flickerbox\GPC::get_string( 'os_build', '' );
	$f_product_version		= \Flickerbox\GPC::get_string( 'product_version', '' );
	$f_target_version		= \Flickerbox\GPC::get_string( 'target_version', '' );
	$f_profile_id			= \Flickerbox\GPC::get_int( 'profile_id', 0 );
	$f_handler_id			= \Flickerbox\GPC::get_int( 'handler_id', 0 );

	$f_category_id			= \Flickerbox\GPC::get_int( 'category_id', 0 );
	$f_reproducibility		= \Flickerbox\GPC::get_int( 'reproducibility', (int)\Flickerbox\Config::mantis_get( 'default_bug_reproducibility' ) );
	$f_eta					= \Flickerbox\GPC::get_int( 'eta', (int)\Flickerbox\Config::mantis_get( 'default_bug_eta' ) );
	$f_severity				= \Flickerbox\GPC::get_int( 'severity', (int)\Flickerbox\Config::mantis_get( 'default_bug_severity' ) );
	$f_priority				= \Flickerbox\GPC::get_int( 'priority', (int)\Flickerbox\Config::mantis_get( 'default_bug_priority' ) );
	$f_summary				= \Flickerbox\GPC::get_string( 'summary', '' );
	$f_description			= \Flickerbox\GPC::get_string( 'description', '' );
	$f_steps_to_reproduce	= \Flickerbox\GPC::get_string( 'steps_to_reproduce', \Flickerbox\Config::mantis_get( 'default_bug_steps_to_reproduce' ) );
	$f_additional_info		= \Flickerbox\GPC::get_string( 'additional_info', \Flickerbox\Config::mantis_get( 'default_bug_additional_info' ) );
	$f_view_state			= \Flickerbox\GPC::get_int( 'view_state', (int)\Flickerbox\Config::mantis_get( 'default_bug_view_status' ) );
	$f_due_date				= \Flickerbox\GPC::get_string( 'due_date', '' );

	if( $f_due_date == '' ) {
		$f_due_date = \Flickerbox\Date::get_null();
	}

	$t_changed_project		= false;
}

$f_report_stay			= \Flickerbox\GPC::get_bool( 'report_stay', false );
$f_copy_notes_from_parent         = \Flickerbox\GPC::get_bool( 'copy_notes_from_parent', false );
$f_copy_attachments_from_parent   = \Flickerbox\GPC::get_bool( 'copy_attachments_from_parent', false );

$t_fields = \Flickerbox\Config::mantis_get( 'bug_report_page_fields' );
$t_fields = \Flickerbox\Columns::filter_disabled( $t_fields );

$t_show_category = in_array( 'category_id', $t_fields );
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_show_eta = in_array( 'eta', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_steps_to_reproduce = in_array( 'steps_to_reproduce', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'update_bug_assign_threshold' ) );
$t_show_profiles = \Flickerbox\Config::mantis_get( 'enable_profiles' );
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_show_status = in_array( 'status', $t_fields );

$t_show_versions = \Flickerbox\Version::should_show_product_version( $t_project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && \Flickerbox\Config::mantis_get( 'enable_product_build' ) == ON;
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'roadmap_update_threshold' ) );
$t_show_additional_info = in_array( 'additional_info', $t_fields );
$t_show_due_date = in_array( 'due_date', $t_fields ) && \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'due_date_update_threshold' ), \Flickerbox\Helper::get_current_project(), \Flickerbox\Auth::get_current_user_id() );
$t_show_attachments = in_array( 'attachments', $t_fields ) && \Flickerbox\File::allow_bug_upload();
$t_show_view_state = in_array( 'view_state', $t_fields ) && \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'set_view_status_threshold' ) );

if( $t_show_due_date ) {
	\Flickerbox\HTML::require_js( 'jscalendar/calendar.js' );
	\Flickerbox\HTML::require_js( 'jscalendar/lang/calendar-en.js' );
	\Flickerbox\HTML::require_js( 'jscalendar/calendar-setup.js' );
	\Flickerbox\HTML::require_css( 'calendar-blue.css' );
}

# don't index bug report page
\Flickerbox\HTML::robots_noindex();

\Flickerbox\HTML::page_top( \Flickerbox\Lang::get( 'report_bug_link' ) );

\Flickerbox\Print_Util::recently_visited();

$t_form_encoding = '';
if( $t_show_attachments ) {
	$t_form_encoding = 'enctype="multipart/form-data"';
}
?>
<div id="report-bug-div" class="form-container">
	<form id="report-bug-form" method="post" <?php echo $t_form_encoding; ?> action="bug_report.php?posted=1">
		<fieldset class="has-required">
			<legend><span><?php echo \Flickerbox\Lang::get( 'enter_report_details_title' ) ?></span></legend>
			<?php echo \Flickerbox\Form::security_field( 'bug_report' ) ?>
			<input type="hidden" name="m_id" value="<?php echo $f_master_bug_id ?>" />
			<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />

			<?php
			\Flickerbox\Event::signal( 'EVENT_REPORT_BUG_FORM_TOP', array( $t_project_id ) );

			if( $t_show_category ) {
			?>
			<div class="field-container">
				<label><span><?php
					echo \Flickerbox\Config::mantis_get( 'allow_no_category' ) ? '' : '<span class="required">*</span>';
					\Flickerbox\Print_Util::documentation_link( 'category' );
				?></span></label>
				<span class="select">
					<?php if( $t_changed_project ) {
						echo '[' . \Flickerbox\Project::get_field( $t_bug->project_id, 'name' ) . '] ';
					} ?>
						<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="category_id" name="category_id" class="autofocus">
					<?php
					\Flickerbox\Print_Util::category_option_list( $f_category_id );
					?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php }

	if( $t_show_reproducibility ) {
?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'reproducibility' ) ?></span></label>
				<span class="input">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="reproducibility" name="reproducibility">
						<?php \Flickerbox\Print_Util::enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}

	if( $t_show_eta ) {
?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'eta' ) ?></span></label>
				<span class="input">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="eta" name="eta">
						<?php \Flickerbox\Print_Util::enum_string_option_list( 'eta', $f_eta ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}

	if( $t_show_severity ) {
?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'severity' ) ?></span></label>
				<span class="input">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="severity" name="severity">
						<?php \Flickerbox\Print_Util::enum_string_option_list( 'severity', $f_severity ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}

	if( $t_show_priority ) {
?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'priority' ) ?></span></label>
				<span class="input">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="priority" name="priority">
						<?php \Flickerbox\Print_Util::enum_string_option_list( 'priority', $f_priority ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
	<?php
	}

	if( $t_show_due_date ) {
		$t_date_to_display = '';

		if( !\Flickerbox\Date::is_null( $f_due_date ) ) {
			$t_date_to_display = date( \Flickerbox\Config::mantis_get( 'calendar_date_format' ), $f_due_date );
		}
?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'due_date' ) ?></span></label>
				<span class="input">
					<?php echo '<input ' . \Flickerbox\Helper::get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetime" size="20" maxlength="16" value="' . $t_date_to_display . '" />' ?>
				</span>
				<span class="label-style"></span>
			</div>
		<?php } ?>
		<?php if( $t_show_platform || $t_show_os || $t_show_os_version ) { ?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'select_profile' ) ?></span></label>
				<span class="select">
					<?php if( count( \Flickerbox\Profile::get_all_for_user( \Flickerbox\Auth::get_current_user_id() ) ) > 0 ) { ?>
						<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="profile_id" name="profile_id">
							<?php \Flickerbox\Print_Util::profile_option_list( \Flickerbox\Auth::get_current_user_id(), $f_profile_id ) ?>
						</select>
					<?php } ?>

					<?php \Flickerbox\Collapse::icon( 'profile' ); ?>
					<?php echo \Flickerbox\Lang::get( 'or_fill_in' ); ?>
				</span>
				<span class="label-style"></span>
			</div>

			<?php \Flickerbox\Collapse::open( 'profile' ); ?>
				<div class="field-container">
					<label><span><?php echo \Flickerbox\Lang::get( 'platform' ) ?></span></label>
					<span class="input">
						<?php if( \Flickerbox\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
						<select id="platform" name="platform">
							<option value=""></option>
							<?php \Flickerbox\Print_Util::platform_option_list( $f_platform ); ?>
						</select>
						<?php
							} else {
								echo '<input type="text" id="platform" name="platform" class="autocomplete" size="32" maxlength="32" tabindex="' . \Flickerbox\Helper::get_tab_index_value() . '" value="' . \Flickerbox\String::attribute( $f_platform ) . '" />';
							}
						?>
					</span>
					<span class="label-style"></span>
				</div>
				<div class="field-container">
					<label><span><?php echo \Flickerbox\Lang::get( 'os' ) ?></span></label>
					<span class="input">
						<?php if( \Flickerbox\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
						<select id="os" name="os">
							<option value=""></option>
							<?php \Flickerbox\Print_Util::os_option_list( $f_os ); ?>
						</select>
						<?php
							} else {
								echo '<input type="text" id="os" name="os" class="autocomplete" size="32" maxlength="32" tabindex="' . \Flickerbox\Helper::get_tab_index_value() . '" value="' . \Flickerbox\String::attribute( $f_os ) . '" />';
							}
						?>
					</span>
					<span class="label-style"></span>
				</div>
				<div class="field-container">
					<label><span><?php echo \Flickerbox\Lang::get( 'os_version' ) ?></span></label>
					<span class="input">
						<?php
						if( \Flickerbox\Config::mantis_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
					?>
					<select id="os_build" name="os_build">
						<option value=""></option>
							<?php \Flickerbox\Print_Util::os_build_option_list( $f_os_build ); ?>
						</select>
					<?php
						} else {
							echo '<input type="text" id="os_build" name="os_build" class="autocomplete" size="16" maxlength="16" tabindex="' . \Flickerbox\Helper::get_tab_index_value() . '" value="' . \Flickerbox\String::attribute( $f_os_build ) . '" />';
						}
					?>
					</span>
					<span class="label-style"></span>
				</div>
			<?php \Flickerbox\Collapse::closed( 'profile' );?>
			<?php \Flickerbox\Collapse::end( 'profile' ); ?>
<?php } ?>
<?php
	if( $t_show_product_version ) {
		$t_product_version_released_mask = VERSION_RELEASED;

		if( \Flickerbox\Access::has_project_level( \Flickerbox\Config::mantis_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
			$t_product_version_released_mask = VERSION_ALL;
		}
?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'product_version' ) ?></span></label>
				<span class="select">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="product_version" name="product_version">
						<?php \Flickerbox\Print_Util::version_option_list( $f_product_version, $t_project_id, $t_product_version_released_mask ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}
?>
<?php if( $t_show_product_build ) { ?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'product_build' ) ?></span></label>
				<span class="input">
					<input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="text" id="build" name="build" size="32" maxlength="32" value="<?php echo \Flickerbox\String::attribute( $f_build ) ?>" />
				</span>
				<span class="label-style"></span>
			</div>
<?php } ?>

<?php if( $t_show_handler ) { ?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'assign_to' ) ?></span></label>
				<span class="select">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="handler_id" name="handler_id">
						<option value="0" selected="selected"></option>
						<?php \Flickerbox\Print_Util::assign_to_option_list( $f_handler_id ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php } ?>

<?php if( $t_show_status ) { ?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'status' ) ?></span></label>
				<span class="select">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> name="status">
					<?php
					$t_resolution_options = \Flickerbox\Print_Util::get_status_option_list(
						\Flickerbox\Access::get_project_level( $t_project_id ),
						\Flickerbox\Config::mantis_get( 'bug_submit_status' ),
						true,
						ON == \Flickerbox\Config::mantis_get( 'allow_reporter_close' ),
						$t_project_id );
					foreach ( $t_resolution_options as $t_key => $t_value ) {
					?>
						<option value="<?php echo $t_key ?>" <?php \Flickerbox\Helper::check_selected( $t_key, \Flickerbox\Config::mantis_get( 'bug_submit_status' ) ); ?> >
							<?php echo $t_value ?>
						</option>
					<?php } ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php } ?>

<?php if( $t_show_resolution ) { ?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'resolution' ) ?></span></label>
				<span class="select">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> name="resolution">
						<?php
						\Flickerbox\Print_Util::enum_string_option_list( 'resolution', \Flickerbox\Config::mantis_get( 'default_bug_resolution' ) );
						?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php } ?>

<?php # Target Version (if permissions allow)
	if( $t_show_target_version ) { ?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'target_version' ) ?></span></label>
				<span class="select">
					<select <?php echo \Flickerbox\Helper::get_tab_index() ?> id="target_version" name="target_version">
						<?php \Flickerbox\Print_Util::version_option_list( '', null, VERSION_FUTURE ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
<?php } ?>
<?php \Flickerbox\Event::signal( 'EVENT_REPORT_BUG_FORM', array( $t_project_id ) ) ?>

			<div class="field-container">
				<label><span class="required">*</span><span><?php \Flickerbox\Print_Util::documentation_link( 'summary' ) ?></span></label>
				<span class="input">
					<input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="text" id="summary" name="summary" size="105" maxlength="128" value="<?php echo \Flickerbox\String::attribute( $f_summary ) ?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><span class="required">*</span><?php \Flickerbox\Print_Util::documentation_link( 'description' ) ?></span></label>
				<span class="textarea">
					<textarea <?php echo \Flickerbox\Helper::get_tab_index() ?> id="description" name="description" cols="80" rows="10"><?php echo \Flickerbox\String::textarea( $f_description ) ?></textarea>
				</span>
				<span class="label-style"></span>
			</div>

<?php if( $t_show_steps_to_reproduce ) { ?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'steps_to_reproduce' ) ?></span></label>
				<span class="textarea">
					<textarea <?php echo \Flickerbox\Helper::get_tab_index() ?> id="steps_to_reproduce" name="steps_to_reproduce" cols="80" rows="10"><?php echo \Flickerbox\String::textarea( $f_steps_to_reproduce ) ?></textarea>
				</span>
				<span class="label-style"></span>
			</div>
<?php } ?>

<?php if( $t_show_additional_info ) { ?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'additional_information' ) ?></span></label>
				<span class="textarea">
					<textarea <?php echo \Flickerbox\Helper::get_tab_index() ?> id="additional_info" name="additional_info" cols="80" rows="10"><?php echo \Flickerbox\String::textarea( $f_additional_info ) ?></textarea>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}

	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( ( $t_def['display_report'] || $t_def['require_report']) && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
			$t_custom_fields_found = true;
?>
			<div class="field-container">
				<label>
					<span>
						<?php if( $t_def['require_report'] ) { ?>
						<span class="required">*</span>
						<?php } ?>
						<?php if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) { ?>
							<label for="custom_field_<?php echo \Flickerbox\String::attribute( $t_def['id'] ) ?>"><?php echo \Flickerbox\String::display( \Flickerbox\Lang::get_defaulted( $t_def['name'] ) ) ?></label>
						<?php } else {
							echo \Flickerbox\String::display( \Flickerbox\Lang::get_defaulted( $t_def['name'] ) );
						} ?>
					</span>
				</label>
				<span class="input">
					<?php print_custom_field_input( $t_def, ( $f_master_bug_id === 0 ) ? null : $f_master_bug_id ) ?>
				</span>
				<span class="label-style"></span>
			</div>
<?php
		}
	} # foreach( $t_related_custom_field_ids as $t_id )
?>
<?php
	# File Upload (if enabled)
	if( $t_show_attachments ) {
		$t_max_file_size = (int)min( \Flickerbox\Utility::ini_get_number( 'upload_max_filesize' ), \Flickerbox\Utility::ini_get_number( 'post_max_size' ), \Flickerbox\Config::mantis_get( 'max_file_size' ) );
		$t_file_upload_max_num = max( 1, \Flickerbox\Config::mantis_get( 'file_upload_max_num' ) );
?>
			<div class="field-container">
				<label>
					<span><?php echo \Flickerbox\Lang::get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' ) ?></span>
					<br />
					<?php echo \Flickerbox\Print_Util::max_filesize( $t_max_file_size ); ?>
				</label>
				<span class="input">
					<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
<?php
		# Display multiple file upload fields
		for( $i = 0; $i < $t_file_upload_max_num; $i++ ) {
?>
					<input <?php echo \Flickerbox\Helper::get_tab_index() ?> id="ufile[]" name="ufile[]" type="file" />
<?php
			if( $t_file_upload_max_num > 1 ) {
				echo '<br />';
			}
		}
	}
?>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	if( $t_show_view_state ) {
?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'view_status' ) ?></span></label>
				<span class="input">
					<label><input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php \Flickerbox\Helper::check_checked( $f_view_state, VS_PUBLIC ) ?> /> <?php echo \Flickerbox\Lang::get( 'public' ) ?></label>
					<label><input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php \Flickerbox\Helper::check_checked( $f_view_state, VS_PRIVATE ) ?> /> <?php echo \Flickerbox\Lang::get( 'private' ) ?></label>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}

	# Relationship (in case of cloned bug creation...)
	if( $f_master_bug_id > 0 ) {
?>
			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'relationship_with_parent' ) ?></span></label>
				<span class="input">
					<?php \Flickerbox\Relationship::list_box( \Flickerbox\Config::mantis_get( 'default_bug_relationship_clone' ), 'rel_type', false, true ) ?>
					<?php echo '<strong>' . \Flickerbox\Lang::get( 'bug' ) . ' ' . \Flickerbox\Bug::format_id( $f_master_bug_id ) . '</strong>' ?>
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo \Flickerbox\Lang::get( 'copy_from_parent' ) ?></span></label>
				<span class="input">
					<label><input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="checkbox" id="copy_notes_from_parent" name="copy_notes_from_parent" <?php \Flickerbox\Helper::check_checked( $f_copy_notes_from_parent ) ?> /> <?php echo \Flickerbox\Lang::get( 'copy_notes_from_parent' ) ?></label>
					<label><input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="checkbox" id="copy_attachments_from_parent" name="copy_attachments_from_parent" <?php \Flickerbox\Helper::check_checked( $f_copy_attachments_from_parent ) ?> /> <?php echo \Flickerbox\Lang::get( 'copy_attachments_from_parent' ) ?></label>
				</span>
				<span class="label-style"></span>
			</div>
<?php
	}
?>
			<div class="field-container">
				<label><span><?php \Flickerbox\Print_Util::documentation_link( 'report_stay' ) ?></span></label>
				<span class="input">
					<label><input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="checkbox" id="report_stay" name="report_stay" <?php \Flickerbox\Helper::check_checked( $f_report_stay ) ?> /> <?php echo \Flickerbox\Lang::get( 'check_report_more_bugs' ) ?></label>
				</span>
				<span class="label-style"></span>
			</div>

			<span class="submit-button">
				<input <?php echo \Flickerbox\Helper::get_tab_index() ?> type="submit" class="button" value="<?php echo \Flickerbox\Lang::get( 'submit_report_button' ) ?>" />
			</span>
		</fieldset>
	</form>
</div>
<?php
\Flickerbox\HTML::page_bottom();
