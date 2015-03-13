<nav<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>>
	<ul>
		<?php foreach ($items as $url => $label): ?>
		<li<?php if (\Core\URL::is_current($url)): ?> class="active"<?php endif; ?>><a href="<?php echo $url; ?>"><?php echo $label ;?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>