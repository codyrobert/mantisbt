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
 * Excel (2003 SP2 and above) export page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses excel_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );

define( 'PRINT_ALL_BUG_OPTIONS_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'print_all_bug_options_inc.php' );

\Flickerbox\Auth::ensure_user_authenticated();

$f_export = \Flickerbox\GPC::get_string( 'export', '' );

\Flickerbox\Helper::begin_long_process();

$t_export_title = excel_get_default_filename();

$t_short_date_format = \Flickerbox\Config::mantis_get( 'short_date_format' );

# This is where we used to do the entire actual filter ourselves
$t_page_number = \Flickerbox\GPC::get_int( 'page_number', 1 );
$t_per_page = 100;

$t_result = \Flickerbox\Filter::get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
if( $t_result === false ) {
	\Flickerbox\Print_Util::header_redirect( 'view_all_set.php?type=0&print=1' );
}

header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
header( 'Pragma: public' );
header( 'Content-Disposition: attachment; filename="' . urlencode( \Flickerbox\File::clean_name( $t_export_title ) ) . '.xml"' ) ;

echo excel_get_header( $t_export_title );
echo excel_get_titles_row();

$f_bug_arr = explode( ',', $f_export );

$t_columns = excel_get_columns();

do {
	# pre-cache custom column data
	\Flickerbox\Columns::plugin_cache_issue_data( $t_result );

	foreach( $t_result as $t_row ) {
		if( \Flickerbox\Utility::is_blank( $f_export ) || in_array( $t_row->id, $f_bug_arr ) ) {
			echo excel_get_start_row();

			foreach ( $t_columns as $t_column ) {
				$t_custom_field = \Flickerbox\Columns::column_get_custom_field_name( $t_column );
				if( $t_custom_field !== null ) {
					echo excel_format_custom_field( $t_row->id, $t_row->project_id, $t_custom_field );
				} else if( \Flickerbox\Columns::column_is_plugin_column( $t_column ) ) {
					echo excel_format_plugin_column_value( $t_column, $t_row );
				} else {
					$t_function = 'excel_format_' . $t_column;
					echo $t_function( $t_row );
				}
			}

			echo excel_get_end_row();
		} #in_array
	} #for loop

	# Get the next page if we are not processing the last one
	# @@@ Note that since we are not using a transaction, there is a risk that we get a duplicate record or we miss
	# one due to a submit or update that happens in parallel.
	$t_more = ( $t_page_number < $t_page_count );
	if( $t_more ) {
		$t_page_number++;
		$t_result = \Flickerbox\Filter::get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	}
} while( $t_more );

echo excel_get_footer();
