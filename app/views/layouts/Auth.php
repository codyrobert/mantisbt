<?php

use Core\Action;
use Core\App;
use Core\Auth;
use Core\Config;
use Core\Event;
use Core\Helper;
use Core\HTML;
use Core\Menu;
use Core\Print_Util;
use Core\String;
use Core\URL;
use Core\Utility;

?><!doctype html>
<html>
<head>
	
	<?php $this->insert('Partials/Head'); ?>
	
</head>
<body class="<?php echo $this->body_class(); ?>">

	<?php Action::perform('page_top'); ?>
	
	<header id="head">
	
		<h1><a href="<?php echo URL::home(); ?>">
			<img src="<?php echo Config::get('_/app.logo') ? URL::get(Config::get('_/app.logo')) : '/media/images/logo.png'; ?>" />
		</a></h1>
		
	</header>
	
	<div id="content">
		<div class="wrap">
	
			<?php Event::signal( 'EVENT_LAYOUT_CONTENT_BEGIN' ); ?>
			<?php echo $this->section('content'); ?>
			<?php Event::signal( 'EVENT_LAYOUT_CONTENT_END' ); ?>
		
		</div>
	</div>

	<footer id="floor">
		<div class="wrap">
		
		<?php
		if( \Core\Config::mantis_get( 'show_footer_menu' ) ) 
		{
			\Core\HTML::print_menu();
		}
	
		\Core\Error::print_delayed();
	
		\Core\HTML::bottom_banner();
		\Core\HTML::footer();
		?>
	
		</div>
	</footer>
	
	<?php Action::perform('page_bottom'); ?>
	
</body>
</html>