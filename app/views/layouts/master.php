<!doctype html>
<html class="no-js" lang="">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php
	$t_meta = config_get_global( 'meta_include_file' );
	if( !\Flickerbox\Utility::is_blank( $t_meta ) ) {
		include( $t_meta );
	}
	global $g_robots_meta;
	if( !\Flickerbox\Utility::is_blank( $g_robots_meta ) ) {
		echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
	}
	
	\Flickerbox\HTML::title( $this->e($title) );
	\Flickerbox\HTML::css();
	\Flickerbox\HTML::rss_link();

	$t_favicon_image = \Flickerbox\Config::get( 'favicon_image' );
	if( !\Flickerbox\Utility::is_blank( $t_favicon_image ) ) {
		echo "\t", '<link rel="shortcut icon" href="', helper_mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
	}

	# Advertise the availability of the browser search plug-ins.
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" href="' . \Flickerbox\String::sanitize_url( 'browser_search_plugin.php?type=text', true ) . '" />' . "\n";
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" href="' . \Flickerbox\String::sanitize_url( 'browser_search_plugin.php?type=id', true ) . '" />' . "\n";

	\Flickerbox\HTML::head_javascript();
	?>
	
</head>
<body>
	
	<?php
	global $g_error_send_page_header;
	$g_error_send_page_header = false;
	
	\Flickerbox\HTML::top_banner();
	
	if( \Flickerbox\Auth::is_user_authenticated() ) {
		\Flickerbox\HTML::login_info();

		if( ON == \Flickerbox\Config::get( 'show_project_menu_bar' ) ) {
			\Flickerbox\HTML::print_project_menu_bar();
			echo '<br />';
		}
	}
	
	\Flickerbox\HTML::print_menu();
	echo '<div id="content">', "\n";
	event_signal( 'EVENT_LAYOUT_CONTENT_BEGIN' );
	?>
	
	<?php echo $this->section('content'); ?>


	<?php \Flickerbox\HTML::page_bottom(); ?>
	
</body>
</html>