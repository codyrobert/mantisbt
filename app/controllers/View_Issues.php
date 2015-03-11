<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Auth;
use Core\Category;
use Core\Config;
use Core\Controller\Page;
use Core\Current_User;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\URL;


class View_Issues extends Page
{
	function __construct($params)
	{
		parent::__construct();
		
		App::queue_css(URL::get('css/status_config.php'));
		Auth::ensure_user_authenticated();
	}
	
	function action_my_view()
	{
		$this->set([
			'page_title'	=> Lang::get( 'my_view_link' ),
			'view'			=> 'My_View',
		]);
		
		$t_current_user_id = Auth::get_current_user_id();
		
		# Improve performance by caching category data in one pass
		Category::get_all_rows( Helper::get_current_project() );
		
		if( Current_User::get_pref( 'refresh_delay' ) > 0 ) {
			HTML::meta_redirect( 'my_view_page.php?refresh=true', Current_User::get_pref( 'refresh_delay' ) * 60 );
		}
		
		$f_page_number		= GPC::get_int( 'page_number', 1 );
		
		$t_per_page = Config::mantis_get( 'my_view_bug_count' );
		$t_bug_count = null;
		$t_page_count = null;
		
		$t_boxes = Config::mantis_get( 'my_view_boxes' );
		asort( $t_boxes );
		reset( $t_boxes );
		#print_r ($t_boxes);
		
		$t_project_id = Helper::get_current_project();
		?>
		
		<div>
		<?php include( ROOT . 'core/timeline_inc.php' ); ?>
		
		<div class="myview_boxes_area">
		
		<table class="hide" cellspacing="3" cellpadding="0">
		<?php
		$t_number_of_boxes = count( $t_boxes );
		$t_boxes_position = Config::mantis_get( 'my_view_boxes_fixed_position' );
		$t_counter = 0;
		
		while( list( $t_box_title, $t_box_display ) = each( $t_boxes ) ) {
			if( $t_box_display == 0 ) {
				# don't display bugs that are set as 0
				$t_number_of_boxes = $t_number_of_boxes - 1;
			} else if( $t_box_title == 'assigned' && ( Current_User::is_anonymous()
				|| !Access::has_project_level( Config::mantis_get( 'handle_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
				# don't display "Assigned to Me" bugs to users that bugs can't be assigned to
				$t_number_of_boxes = $t_number_of_boxes - 1;
			} else if( $t_box_title == 'monitored' && ( Current_User::is_anonymous() or !Access::has_project_level( Config::mantis_get( 'monitor_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
				# don't display "Monitored by Me" bugs to users that can't monitor bugs
				$t_number_of_boxes = $t_number_of_boxes - 1;
			} else if( in_array( $t_box_title, array( 'reported', 'feedback', 'verify' ) ) &&
				( Current_User::is_anonymous() or !Access::has_project_level( Config::mantis_get( 'report_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
				# don't display "Reported by Me" bugs to users that can't report bugs
				$t_number_of_boxes = $t_number_of_boxes - 1;
			} else {
				# display the box
				$t_counter++;
		
				# check the style of displaying boxes - fixed (ie. each box in a separate table cell) or not
				if( ON == $t_boxes_position ) {
					if( 1 == $t_counter%2 ) {
						# for even box number start new row and column
						echo '<tr><td class="myview-left-col">';
						include( ROOT . 'my_view_inc.php' );
						echo '</td></tr>';
					} else if( 0 == $t_counter%2 ) {
						# for odd box number only start new column
						echo '<tr><td class="myview-right-col">';
						include( ROOT . 'my_view_inc.php' );
						echo '</td></tr>';
					}
				} else if( OFF == $t_boxes_position ) {
					# start new table row and column for first box
					if( 1 == $t_counter ) {
						echo '<tr><td class="myview-left-col">';
					}
		
					# start new table column for the second half of boxes
					if( $t_counter == ceil( $t_number_of_boxes / 2 ) + 1 ) {
						echo '<td class="myview-right-col">';
					}
		
					# display the required box
					include( ROOT . 'my_view_inc.php' );
					echo '<br />';
		
					# close the first column for first half of boxes
					if( $t_counter == ceil( $t_number_of_boxes / 2 ) ) {
						echo '</td>';
					}
				}
			}
		}
		
		# Close the box groups depending on the layout mode and whether an empty cell
		# is required to pad the number of cells in the last row to the full width of
		# the table.
		if( ON == $t_boxes_position && $t_counter == $t_number_of_boxes && 1 == $t_counter%2 ) {
			echo '<td class="myview-right-col"></td></tr>';
		} else if( OFF == $t_boxes_position && $t_counter == $t_number_of_boxes ) {
			echo '</td></tr>';
		}
		?>
		
		</table>
		</div>
		
		<?php
	}
}