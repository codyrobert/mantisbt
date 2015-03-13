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
<html class="no-js" lang="">
<head>
	
	<?php $this->insert('Partials/Head'); ?>
	
</head>
<body class="<?php echo $this->body_class(); ?>">

	<?php Action::perform('page_top'); ?>
	
	<header id="head">
	
		<h1<?php if (Config::mantis_get('logo_image')): ?> class="logo" style="background-image:url('<?php echo URL::get(Config::mantis_get('logo_image')); ?>');"<?php endif; ?>>
			<a href="<?php echo URL::home(); ?>"><?php echo Config::get('app')['site_name']; ?></a>
		</h1>
		
	</header>
	
	<section id="content">
		<div class="wrap">
	
			<?php Event::signal( 'EVENT_LAYOUT_CONTENT_BEGIN' ); ?>
			<?php echo $this->section('content'); ?>
			<?php Event::signal( 'EVENT_LAYOUT_CONTENT_END' ); ?>
		
		</div>
	</section>

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