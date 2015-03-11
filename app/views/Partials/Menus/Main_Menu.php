<nav id="main-menu">
	<ul>
		<?php foreach (\Core\Menu::main() as $url => $label): ?>
		<li><a href="<?php echo $url; ?>"><?php echo $label ;?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>