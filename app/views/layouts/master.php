<?php
use Core\Action;
use Core\App;
use Core\Auth;
use Core\Config;
use Core\Event;
use Core\Helper;
use Core\HTML;
use Core\String;
use Core\URL;
use Core\Utility;
?><!doctype html>
<html class="no-js" lang="">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title><?php echo App::page_title(); ?></title>

	<?php
	$t_meta = Config::get_global( 'meta_include_file' );
	if( !Utility::is_blank( $t_meta ) ) {
		include( $t_meta );
	}
	global $g_robots_meta;
	if( !Utility::is_blank( $g_robots_meta ) ) {
		echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
	}
	
	HTML::rss_link();

	$t_favicon_image = Config::mantis_get( 'favicon_image' );
	if( !Utility::is_blank( $t_favicon_image ) ) {
		echo "\t", '<link rel="shortcut icon" href="', Helper::mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
	}

	# Advertise the availability of the browser search plug-ins.
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" href="' . String::sanitize_url( 'browser_search_plugin.php?type=text', true ) . '" />' . "\n";
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" href="' . String::sanitize_url( 'browser_search_plugin.php?type=id', true ) . '" />' . "\n";
	
	Action::perform('after_head');
	?>
	
</head>
<body>

	<?php Action::perform('page_top'); ?>
	
	<?php
	global $g_error_send_page_header;
	$g_error_send_page_header = false;
	?>
	
	<header id="top">
	
		<h1<?php if (Config::mantis_get('logo_image')): ?> class="logo" style="background-image:url('<?php echo URL::get(Config::mantis_get('logo_image')); ?>');"<?php endif; ?>>
			<a href="<?php echo URL::home(); ?>"><?php echo Config::get('app')['site_name']; ?></a>
		</h1>
		
		<?php
		if(Auth::is_user_authenticated()) 
		{
			$this->insert('Partials/Menus/Main_Menu');
			$this->insert('Partials/Forms/Jump_To_Bug');
		}
		?>
		
	</header>
	
	<?php
	if( Auth::is_user_authenticated() ) {
		HTML::login_info();

		if( ON == Config::mantis_get( 'show_project_menu_bar' ) ) {
			HTML::print_project_menu_bar();
			echo '<br />';
		}
	}
	
	echo '<div id="content">', "\n";
	Event::signal( 'EVENT_LAYOUT_CONTENT_BEGIN' );
	?>
	
	<?php echo $this->section('content'); ?>


	<?php HTML::page_bottom(); ?>
	
	<?php Action::perform('page_bottom'); ?>
	
</body>
</html>