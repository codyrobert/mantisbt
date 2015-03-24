<?php
namespace Controller;


use Core\URL;


class Set_Project extends \Core\Controller\Page
{
	function action_index()
	{
	
		$f_project_id	= \Core\GPC::get_string( 'project_id' );
		$f_make_default	= \Core\GPC::get_bool( 'make_default' );
		$f_ref			= \Core\GPC::get_string( 'ref', '' );
		
		$c_ref = \Core\String::prepare_header( $f_ref );
		
		$t_project = explode( ';', $f_project_id );
		$t_top     = $t_project[0];
		$t_bottom  = $t_project[count( $t_project ) - 1];
		
		if( ALL_PROJECTS != $t_bottom ) {
			\Core\Project::ensure_exists( $t_bottom );
		}
		
		# Set default project
		if( $f_make_default ) {
			\Core\Current_User::set_default_project( $t_top );
		}
		
		\Core\Helper::set_current_project( $f_project_id );
		
		# redirect to 'same page' when switching projects.
		
		# for proxies that clear out HTTP_REFERER
		if( !\Core\Utility::is_blank( $c_ref ) ) {
			$t_redirect_url = $c_ref;
		} else if( !isset( $_SERVER['HTTP_REFERER'] ) || \Core\Utility::is_blank( $_SERVER['HTTP_REFERER'] ) ) {
			$t_redirect_url = \Core\Config::mantis_get( 'default_home_page' );
		} else {
			$t_home_page = \Core\Config::mantis_get( 'default_home_page' );
		
			# Check that referrer matches our address after squashing case (case insensitive compare)
			$t_path = rtrim( \Core\Config::mantis_get( 'path' ), '/' );
			if( preg_match( '@^(' . $t_path . ')/(?:/*([^\?#]*))(.*)?$@', $_SERVER['HTTP_REFERER'], $t_matches ) ) {
				$t_referrer_page = $t_matches[2];
				$t_param = $t_matches[3];
		
				# if view_all_bug_page, pass on filter
				if( strcasecmp( 'view_all_bug_page.php', $t_referrer_page ) == 0 ) {
					$t_source_filter_id = \Core\Filter::db_get_project_current( $f_project_id );
					$t_redirect_url = 'view_all_set.php?type=4';
		
					if( $t_source_filter_id !== null ) {
						$t_redirect_url = 'view_all_set.php?type=3&source_query_id=' . $t_source_filter_id;
					}
				} else if( stripos( $t_referrer_page, '_page.php' ) !== false ) {
					switch( $t_referrer_page ) {
						case 'bug_view_page.php':
						case 'bug_view_advanced_page.php':
						case 'bug_update_page.php':
						case 'bug_change_status_page.php':
							$t_path = $t_home_page;
							break;
						default:
							$t_path = $t_referrer_page . $t_param;
							break;
					}
					$t_redirect_url = $t_path;
				} else if( $t_referrer_page == 'plugin.php' ) {
					$t_redirect_url = $t_referrer_page . $t_param; # redirect to same plugin page
				} else {
					$t_redirect_url = $t_home_page;
				}
			} else {
				$t_redirect_url = $t_home_page;
			}
		}
		
		URL::redirect($t_redirect_url);
	}
}
