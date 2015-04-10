<?php

use Core\Action;
use Core\App;
use Core\Auth;
use Core\Config;
use Core\Error;
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
	
	<link rel="import" href="<?php echo URL::get('web_components/svg-element.html'); ?>" />
	<link rel="import" href="<?php echo URL::get('web_components/dropdown-menu.html'); ?>" />
	
</head>
<body class="<?php echo $this->body_class($this->section('sidebar') ? 'page-with-sidebar' : ''); ?>">

	<?php Action::perform('page_top'); ?>
	
	<?php
	global $g_error_send_page_header;
	$g_error_send_page_header = false;
	?>
	
	<header id="head">
		<div class="wrap">
			
			<h1><a href="<?php echo URL::home(); ?>">
				<img src="<?php echo Config::get('_/app.logo') ? URL::get(Config::get('_/app.logo')) : '/media/images/logo.png'; ?>" />
			</a></h1>
			
			<?php
			$this->insert('Partials/Forms/Search');
			$this->insert('Partials/Menu', ['items' => Menu::main()]);
			?>
		
		</div>
	</header>
	
	<div id="content">
		<div class="wrap">
		
			<?php echo $this->section('before_content'); ?>
		
			<?php if ($this->section('sidebar')): ?>
			<div class="section-sidebar">
	
				<?php echo $this->section('sidebar'); ?>
			
			</div>
			<?php endif; ?>
		
			<div class="section-content">
	
				<?php echo $this->section('content'); ?>
				
			</div>
			
			<?php echo $this->section('after_content'); ?>
		
		</div>
	</div>

	<footer id="floor">
		<div class="wrap">
		
		<?php
		if (Config::mantis_get('show_footer_menu')) 
		{
			HTML::print_menu();
		}
	
		Error::print_delayed();
	
		HTML::bottom_banner();
		HTML::footer();
		?>
	
		</div>
	</footer>
	
	<?php Action::perform('page_bottom'); ?>
	
</body>
</html>