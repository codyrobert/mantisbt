<?php
\Core\HTML::require_js( 'bugFilter.js' );
\Core\HTML::require_css( 'status_config.php' );

$f_page_number		= \Core\GPC::get_int( 'page_number', 1 );
# Get Project Id and set it as current
$t_project_id = \Core\GPC::get_int( 'project_id', \Core\Helper::get_current_project() );
if( ( ALL_PROJECTS == $t_project_id || \Core\Project::exists( $t_project_id ) ) && $t_project_id != \Core\Helper::get_current_project() ) {
	\Core\Helper::set_current_project( $t_project_id );
	# Reloading the page is required so that the project browser
	# reflects the new current project
	\Core\Print_Util::header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
}

$t_per_page = null;
$t_bug_count = null;
$t_page_count = null;

$t_rows = \Core\Filter::get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, null, null, null, true );
if( $t_rows === false ) {
	\Core\Print_Util::header_redirect( 'view_all_set.php?type=0' );
}

$t_bugslist = array();
$t_users_handlers = array();
$t_project_ids  = array();
$t_row_count = count( $t_rows );
for( $i=0; $i < $t_row_count; $i++ ) {
	array_push( $t_bugslist, $t_rows[$i]->id );
	$t_users_handlers[] = $t_rows[$i]->handler_id;
	$t_project_ids[] = $t_rows[$i]->project_id;
}
$t_unique_users_handlers = array_unique( $t_users_handlers );
$t_unique_project_ids = array_unique( $t_project_ids );
\Core\User::cache_array_rows( $t_unique_users_handlers );
\Core\Project::cache_array_rows( $t_unique_project_ids );

\Core\GPC::set_cookie( \Core\Config::mantis_get( 'bug_list_cookie' ), implode( ',', $t_bugslist ) );

# don't index view issues pages

if( \Core\Current_User::get_pref( 'refresh_delay' ) > 0 ) {
	$t_query = '?';

	if( $f_page_number > 1 )  {
		$t_query .= 'page_number=' . $f_page_number . '&';
	}

	$t_query .= 'refresh=true';

	\Core\HTML::meta_redirect( 'view_all_bug_page.php' . $t_query, \Core\Current_User::get_pref( 'refresh_delay' ) * 60 );
}

\Core\Print_Util::recently_visited();

define( 'VIEW_ALL_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view_all_inc.php' );