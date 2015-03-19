<?php
use Core\Access;
use Core\Auth;
use Core\Bug;
use Core\Category;
use Core\Config;
use Core\Current_User;
use Core\GPC;
use Core\Helper;
use Core\Lang;
use Core\Print_Util;
use Core\Project;
use Core\String;
use Core\URL;


$this->layout('Layouts/Master', $this->data);

$t_fields_config_option = 'bug_view_page_fields';

$bug_id = $ticket->id;
$f_bug_id = $bug_id;
$p_bug = $t_bug = Bug::get( $f_bug_id, true );

$g_project_override = $t_bug->project_id;
?>


<header class="page-title">

	<div class="tools">
	
		<a class="notify" href="<?php echo URL::get('bug_reminder_page.php?bug_id='.$ticket->id); ?>"><svg-element src="<?php echo URL::get('/media/svgs/bell.svg'); ?>" style="height:26px;width:26px;"></svg-element></a>
	
		<dropdown-menu label="Options" structure='<?php
		
		$statuses = [];
				
		foreach ($ticket->get_status_list() as $key => $label)
		{
			$statuses[] = ['label' => $label, 'href' => '#'.$key, 'icon' => 'asterisk'];
		}
		
		echo json_encode([
		
			['label' => 'Edit', 'href' => '#', 'icon' => 'pencil'],
			
			['label' => 'Assign to...', 'icon' => 'user-4', 'structure' => [
			
				['label' => 'Myself', 'href' => '#', 'icon' => 'user-4'],
				['label' => 'Reporter', 'href' => '#', 'icon' => 'user-4'],
				['label' => 'Hamza', 'href' => '#', 'icon' => 'user-4']
				
			]],
			
			['label' => 'Change status to...', 'icon' => 'bullhorn', 'structure' => $statuses],
			
			['label' => 'Monitor', 'href' => '#', 'icon' => 'watch'],
			['label' => 'Move', 'href' => '#', 'icon' => 'arrow-right'],
			['label' => 'Clone', 'href' => '#', 'icon' => 'subtract'],
			['label' => 'Close', 'href' => '#', 'icon' => 'delete'],
			['label' => 'Print', 'href' => '#', 'icon' => 'print'],
			
		]); ?>'></dropdown-menu>
	
	</div>
	
	<h2>
		<?php if ($show->id): ?><span>#<?php echo Bug::format_id($ticket->id); ?></span><?php endif; ?>
		<?php echo $ticket->summary; ?>
	</h2>
	
</header>

<div class="main">

	<div class="main-sidebar">
	
		<aside id="people">
		
			<h5>People</h5>
			
			<p>
				<strong><?php echo Lang::get( 'reporter' ); ?>:</strong> <?php echo Print_Util::user_with_subject($ticket->reporter_id, $ticket->id); ?><br />
				<strong><?php echo Lang::get( 'assigned_to' ); ?>:</strong> <?php echo Print_Util::user_with_subject($ticket->handler_id, $ticket->id); ?></strong>	
			</p>
			
		</aside>
	
		<aside id="attachments">
		
			<h5><?php echo Lang::get( 'attached_files' ); ?></h5>
			
			<?php Print_Util::bug_attachments_list($ticket->id); ?>
			
		</aside>
	
	</div>
	
	<div class="main-body">
		
		<section id="ticket_description">
		
			<h4 class="section-title">Description</h4>
			
			<div class="content">
				<?php echo String::display_links($ticket->description); ?>
			</div>
			
			<?php if ($show->steps_to_reproduce && $ticket->steps_to_reproduce): ?>
			<h4><?php echo Lang::get( 'steps_to_reproduce' ); ?></h4>
			
			<div class="content">
				<?php echo String::display_links($ticket->steps_to_reproduce); ?>
			</div>
			<?php endif; ?>
			
			<?php if ($show->additional_information && $ticket->additional_information): ?>
			<h4><?php echo Lang::get( 'additional_information' ); ?></h4>
			
			<div class="content">
				<?php echo String::display_links($ticket->additional_information); ?>
			</div>
			<?php endif; ?>
			
		</section>
	
		<details id="ticket-details" open>
		
			<summary>Details</summary>
			
			<div class="row">
				<div class="column-1-4">
					<?php if ($show->project): ?><div><strong><?php echo Lang::get('email_project'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->category_id): ?><div><strong><?php echo Lang::get('category'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->priority): ?><div><strong><?php echo Lang::get('priority'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->severity): ?><div><strong><?php echo Lang::get('severity'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->status): ?><div><strong><?php echo Lang::get('status'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->projection): ?><div><strong><?php echo Lang::get('projection'); ?>:</strong></div><?php endif; ?>
				</div>
				
				<div class="column-1-4">
					<?php if ($show->project): ?><div><?php echo Project::get_name($ticket->project_id); ?></div><?php endif; ?>
					<?php if ($show->category_id): ?><div><?php echo Category::full_name($ticket->category_id); ?></div><?php endif; ?>
					<?php if ($show->priority): ?><div><?php echo Helper::get_enum_element('priority', $ticket->priority); ?></div><?php endif; ?>
					<?php if ($show->severity): ?><div><?php echo Helper::get_enum_element('severity', $ticket->severity); ?></div><?php endif; ?>
					<?php if ($show->status): ?><div><?php echo Helper::get_enum_element('status', $ticket->status); ?></div><?php endif; ?>
					<?php if ($show->projection): ?><div><?php echo Helper::get_enum_element('projection', $ticket->projection); ?></div><?php endif; ?>
				</div>
					
				<div class="column-1-4">
					<?php if ($show->date_submitted): ?><div><strong><?php echo Lang::get('date_submitted'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->last_updated): ?><div><strong><?php echo Lang::get('last_update'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->due_date): ?><div><strong><?php echo Lang::get('due_date'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->reproducibility): ?><div><strong><?php echo Lang::get('reproducibility'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->resolution): ?><div><strong><?php echo Lang::get('resolution'); ?>:</strong></div><?php endif; ?>
					<?php if ($show->eta): ?><div><strong><?php echo Lang::get('eta'); ?>:</strong></div><?php endif; ?>
				</div>
				
				<div class="column-1-4">
					<?php if ($show->date_submitted): ?><div><?php echo date(Config::mantis_get('normal_date_format'), $ticket->date_submitted); ?></div><?php endif; ?>
					<?php if ($show->last_updated): ?><div><?php echo date(Config::mantis_get('normal_date_format'), $ticket->last_updated); ?></div><?php endif; ?>
					<?php if ($show->due_date): ?><div><?php echo date(Config::mantis_get('normal_date_format'), $ticket->due_date); ?></div><?php endif; ?>
					<?php if ($show->reproducibility): ?><div><?php echo Helper::get_enum_element('reproducibility', $ticket->reproducibility); ?></div><?php endif; ?>
					<?php if ($show->resolution): ?><div><?php echo Helper::get_enum_element('resolution', $ticket->resolution); ?></div><?php endif; ?>
					<?php if ($show->eta): ?><div><?php echo Helper::get_enum_element('eta', $ticket->eta); ?></div><?php endif; ?>
				</div>
			</div>
	
			<!--
			<div class="row">
				<div class="column-1-4">
					<div><strong><?php echo Lang::get('email_project'); ?>:</strong></div>
					<div><strong><?php echo Lang::get('category'); ?>:</strong></div>
				</div>
				
				<div class="column-3-4">
					<div><?php echo Project::get_name($ticket->project_id); ?></div>
					<div><?php echo Category::full_name($ticket->category_id); ?></div>
				</div>
			</div>
			-->
		
		</details>
		
		<?php if(Current_User::get_pref('bugnote_order') === DESC && !Bug::is_readonly($ticket->id)): ?>
				<details open>
					<summary><?php echo Lang::get('add_bugnote_title'); ?></summary>
					<?php $this->insert('Partials/Forms/Add_Note', ['ticket' => $ticket, 'order' => Current_User::get_pref('bugnote_order')]); ?>
				</details>
		<?php endif; ?>
		
		<?php $this->insert('Partials/View_Notes', ['ticket' => $ticket]); ?>
		
		<?php if(Current_User::get_pref('bugnote_order') === ASC && !Bug::is_readonly($ticket->id)): ?>
				<details open>
					<summary><?php echo Lang::get('add_bugnote_title'); ?></summary>
					<?php $this->insert('Partials/Forms/Add_Note', ['ticket' => $ticket, 'order' => Current_User::get_pref('bugnote_order')]); ?>
				</details>
		<?php endif; ?>
		
		
	</div>

</div>

<?php
return;
?>

<?php
# In case the current project is not the same project of the bug we are
# viewing, override the current project. This ensures all config_get and other
# per-project function calls use the project ID of this bug.




$f_history = GPC::get_bool( 'history', Config::mantis_get( 'history_default_visible' ) );

$t_fields = Config::mantis_get( $t_fields_config_option );
$t_fields = \Core\Columns::filter_disabled( $t_fields );

$t_action_button_position = Config::mantis_get( 'action_button_position' );

$t_bugslist = GPC::get_cookie( Config::mantis_get( 'bug_list_cookie' ), false );

$t_show_versions = \Core\Version::should_show_product_version( $t_bug->project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields )
	&& ( Config::mantis_get( 'enable_product_build' ) == ON );
$t_product_build = $t_show_product_build ? \Core\String::display_line( $t_bug->build ) : '';
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields )
	&& Access::has_bug_level( Config::mantis_get( 'roadmap_view_threshold' ), $f_bug_id );

$t_product_version_string  = '';
$t_target_version_string   = '';
$t_fixed_in_version_string = '';

if( $t_show_product_version || $t_show_fixed_in_version || $t_show_target_version ) {
	$t_version_rows = \Core\Version::get_all_rows( $t_bug->project_id );

	if( $t_show_product_version ) {
		$t_product_version_string  = \Core\Prepare::version_string( $t_bug->project_id, \Core\Version::get_id( $t_bug->version, $t_bug->project_id ) );
	}

	if( $t_show_target_version ) {
		$t_target_version_string   = \Core\Prepare::version_string( $t_bug->project_id, \Core\Version::get_id( $t_bug->target_version, $t_bug->project_id ) );
	}

	if( $t_show_fixed_in_version ) {
		$t_fixed_in_version_string = \Core\Prepare::version_string( $t_bug->project_id, \Core\Version::get_id( $t_bug->fixed_in_version, $t_bug->project_id ) );
	}
}

$t_product_version_string = \Core\String::display_line( $t_product_version_string );
$t_target_version_string = \Core\String::display_line( $t_target_version_string );
$t_fixed_in_version_string = \Core\String::display_line( $t_fixed_in_version_string );

$t_bug_id = $f_bug_id;
$t_form_title = Lang::get( 'bug_view_title' );
$t_wiki_link = Config::get_global( 'wiki_enable' ) == ON ? 'wiki.php?id=' . $f_bug_id : '';

if( Access::has_bug_level( Config::mantis_get( 'view_history_threshold' ), $f_bug_id ) ) {
	$t_history_link = 'view.php?id=' . $f_bug_id . '&history=1#history';
} else {
	$t_history_link = '';
}

$t_show_reminder_link = !\Core\Current_User::is_anonymous() && !\Core\Bug::is_readonly( $f_bug_id ) &&
	  Access::has_bug_level( Config::mantis_get( 'bug_reminder_threshold' ), $f_bug_id );
$t_bug_reminder_link = 'bug_reminder_page.php?bug_id=' . $f_bug_id;

$t_print_link = 'print_bug_page.php?bug_id=' . $f_bug_id;

$t_top_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
$t_bottom_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

$t_show_project = in_array( 'project', $t_fields );
$t_project_name = $t_show_project ? \Core\String::display_line( \Core\Project::get_name( $t_bug->project_id ) ): '';
$t_show_id = in_array( 'id', $t_fields );
$t_formatted_bug_id = $t_show_id ? \Core\String::display_line( \Core\Bug::format_id( $f_bug_id ) ) : '';

$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_date_submitted = $t_show_date_submitted ? date( Config::mantis_get( 'normal_date_format' ), $t_bug->date_submitted ) : '';

$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_last_updated = $t_show_last_updated ? date( Config::mantis_get( 'normal_date_format' ), $t_bug->last_updated ) : '';

$t_show_tags = in_array( 'tags', $t_fields ) && Access::has_global_level( Config::mantis_get( 'tag_view_threshold' ) );

$t_bug_overdue = \Core\Bug::is_overdue( $f_bug_id );

$t_show_view_state = in_array( 'view_state', $t_fields );
$t_bug_view_state_enum = $t_show_view_state ? \Core\String::display_line( \Core\Helper::get_enum_element( 'view_state', $t_bug->view_state ) ) : '';

$t_show_due_date = in_array( 'due_date', $t_fields ) && Access::has_bug_level( Config::mantis_get( 'due_date_view_threshold' ), $f_bug_id );

if( $t_show_due_date ) {
	if( !\Core\Date::is_null( $t_bug->due_date ) ) {
		$t_bug_due_date = date( Config::mantis_get( 'normal_date_format' ), $t_bug->due_date );
	} else {
		$t_bug_due_date = '';
	}
}

$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && Access::has_bug_level( Config::mantis_get( 'view_handler_threshold' ), $f_bug_id );
$t_show_additional_information = !\Core\Utility::is_blank( $t_bug->additional_information ) && in_array( 'additional_info', $t_fields );
$t_show_steps_to_reproduce = !\Core\Utility::is_blank( $t_bug->steps_to_reproduce ) && in_array( 'steps_to_reproduce', $t_fields );
$t_show_monitor_box = !$t_force_readonly;
$t_show_relationships_box = !$t_force_readonly;
$t_show_sponsorships_box = Config::mantis_get( 'enable_sponsorship' ) && Access::has_bug_level( Config::mantis_get( 'view_sponsorship_total_threshold' ), $f_bug_id );
$t_show_upload_form = !$t_force_readonly && !\Core\Bug::is_readonly( $f_bug_id );
$t_show_history = $f_history;
$t_show_profiles = Config::mantis_get( 'enable_profiles' );
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_platform = $t_show_platform ? \Core\String::display_line( $t_bug->platform ) : '';
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_os = $t_show_os ? \Core\String::display_line( $t_bug->os ) : '';
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_os_version = $t_show_os_version ? \Core\String::display_line( $t_bug->os_build ) : '';
$t_show_projection = in_array( 'projection', $t_fields );
$t_projection = $t_show_projection ? \Core\String::display_line( \Core\Helper::get_enum_element( 'projection', $t_bug->projection ) ) : '';
$t_show_eta = in_array( 'eta', $t_fields );
$t_eta = $t_show_eta ? \Core\String::display_line( \Core\Helper::get_enum_element( 'eta', $t_bug->eta ) ) : '';
$t_show_attachments = in_array( 'attachments', $t_fields );
$t_can_attach_tag = $t_show_tags && !$t_force_readonly && Access::has_bug_level( Config::mantis_get( 'tag_attach_threshold' ), $f_bug_id );
$t_show_category = in_array( 'category_id', $t_fields );
$t_category = $t_show_category ? \Core\String::display_line( \Core\Category::full_name( $t_bug->category_id ) ) : '';
$t_show_priority = in_array( 'priority', $t_fields );
$t_priority = $t_show_priority ? \Core\String::display_line( \Core\Helper::get_enum_element( 'priority', $t_bug->priority ) ) : '';
$t_show_severity = in_array( 'severity', $t_fields );
$t_severity = $t_show_severity ? \Core\String::display_line( \Core\Helper::get_enum_element( 'severity', $t_bug->severity ) ) : '';
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_reproducibility = $t_show_reproducibility ? \Core\String::display_line( \Core\Helper::get_enum_element( 'reproducibility', $t_bug->reproducibility ) ): '';
$t_show_status = in_array( 'status', $t_fields );
$t_status = $t_show_status ? \Core\String::display_line( \Core\Helper::get_enum_element( 'status', $t_bug->status ) ) : '';
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_resolution = $t_show_resolution ? \Core\String::display_line( \Core\Helper::get_enum_element( 'resolution', $t_bug->resolution ) ) : '';
$t_show_summary = in_array( 'summary', $t_fields );
$t_show_description = in_array( 'description', $t_fields );

$t_summary = $t_show_summary ? \Core\Bug::format_summary( $f_bug_id, SUMMARY_FIELD ) : '';
$t_description = $t_show_description ? \Core\String::display_links( $t_bug->description ) : '';
$t_steps_to_reproduce = $t_show_steps_to_reproduce ? \Core\String::display_links( $t_bug->steps_to_reproduce ) : '';
$t_additional_information = $t_show_additional_information ? \Core\String::display_links( $t_bug->additional_information ) : '';




# prev/next links
if( $t_bugslist ) {
	echo '<td class="center prev-next-links"><span class="small">';

	$t_bugslist = explode( ',', $t_bugslist );
	$t_index = array_search( $f_bug_id, $t_bugslist );
	if( false !== $t_index ) {
		if( isset( $t_bugslist[$t_index-1] ) ) {
			\Core\Print_Util::bracket_link( 'view.php?id='.$t_bugslist[$t_index-1], '&lt;&lt;', false, 'previous-bug' );
		}

		if( isset( $t_bugslist[$t_index+1] ) ) {
			\Core\Print_Util::bracket_link( 'view.php?id='.$t_bugslist[$t_index+1], '&gt;&gt;', false, 'next-bug' );
		}
	}
	echo '</span></td>';
}


# Links
echo '<td class="right alternate-views-links" colspan="2">';

if( !\Core\Utility::is_blank( $t_history_link ) ) {
	# History
	echo '<span class="small">';
	\Core\Print_Util::bracket_link( $t_history_link, Lang::get( 'bug_history' ), false, 'bug-history' );
	echo '</span>';
}

# Print Bug
echo '<span class="small">';
\Core\Print_Util::bracket_link( $t_print_link, Lang::get( 'print' ), false, 'print' );
echo '</span>';
echo '</td>';
echo '</tr>';

if( $t_top_buttons_enabled ) {
	echo '<tr class="top-buttons">';
	echo '<td colspan="6">';
	\Core\HTML::buttons_view_bug_page( $t_bug_id );
	echo '</td>';
	echo '</tr>';
}

echo '</thead>';

if( $t_bottom_buttons_enabled ) {
	echo '<tfoot>';
	echo '<tr class="details-footer"><td colspan="6">';
	\Core\HTML::buttons_view_bug_page( $t_bug_id );
	echo '</td></tr>';
	echo '</tfoot>';
}

echo '<tbody>';




# Tagging
if( $t_show_tags ) {
	echo '<tr>';
	echo '<th class="bug-tags category">', Lang::get( 'tags' ), '</th>';
	echo '<td class="bug-tags" colspan="5">';
	\Core\Tag::display_attached( $t_bug_id );
	echo '</td></tr>';
}

# Attachments Form
if( $t_can_attach_tag ) {
	echo '<tr>';
	echo '<th class="bug-attach-tags category">', Lang::get( 'tag_attach_long' ), '</th>';
	echo '<td class="bug-attach-tags" colspan="5">';
	\Core\Print_Util::tag_attach_form( $t_bug_id );
	echo '</td></tr>';
}

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Custom Fields
$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

foreach( $t_related_custom_field_ids as $t_id ) {
	if( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
		continue;
	} # has read access

	$t_custom_fields_found = true;
	$t_def = custom_field_get_definition( $t_id );

	echo '<tr>';
	echo '<th class="bug-custom-field category">', \Core\String::display( Lang::get_defaulted( $t_def['name'] ) ), '</th>';
	echo '<td class="bug-custom-field" colspan="5">';
	print_custom_field_value( $t_def, $t_id, $f_bug_id );
	echo '</td></tr>';
}

if( $t_custom_fields_found ) {
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}



# User list sponsoring the bug
if( $t_show_sponsorships_box ) {
	define( 'BUG_SPONSORSHIP_LIST_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_sponsorship_list_view_inc.php' );
}

# Bug Relationships
if( $t_show_relationships_box ) {
	\Core\Relationship::view_box( $t_bug->id );
}

# File upload box
if( $t_show_upload_form ) {
	define( 'BUG_FILE_UPLOAD_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_file_upload_inc.php' );
}

# User list monitoring the bug
if( $t_show_monitor_box ) {
	define( 'BUG_MONITOR_LIST_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_monitor_list_view_inc.php' );
}

# Bugnotes and "Add Note" box
if( 'ASC' == \Core\Current_User::get_pref( 'bugnote_order' ) ) {
	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );

	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}
} else {
	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}

	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );
}

# Allow plugins to display stuff after notes
\Core\Event::signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_bug_id ) );

# Time tracking statistics
if( Config::mantis_get( 'time_tracking_enabled' ) &&
	Access::has_bug_level( Config::mantis_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
	define( 'BUGNOTE_STATS_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_stats_inc.php' );
}

# History
if( $t_show_history ) {
	define( 'HISTORY_INC_ALLOW', true );
	include( $t_mantis_dir . 'history_inc.php' );
}

\Core\Last_Visited::issue( $t_bug_id );