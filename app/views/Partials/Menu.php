<nav<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>>
	<ul>
		<?php foreach ($items as $url => $params): ?>
		<li class="<?php echo $params['class']; ?><?php if (\Core\URL::is_current($url)): ?> active<?php endif; ?>"><a href="<?php echo $url; ?>"<?php if ($params['title']): ?> title="<?php echo $params['title']; ?>"<?php endif; ?>><?php echo $params['label']; ?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>