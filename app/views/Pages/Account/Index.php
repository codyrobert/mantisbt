<?php
use Core\Lang;
use Core\Menu;

$this->layout('Layouts/Master', $this->data);
?>

<header class="page-title">
	<?php $this->insert('Partials/Menu', array('items' => Menu::account())); ?>
	<h2><?php echo Lang::get('edit_account_title'); ?></h2>
</header>

<?php
if (@$errors)
{
	echo '<ul class="notice error">', PHP_EOL;
	
	foreach ($errors as $error)
	{
		echo '<li>', $error, '</li>', PHP_EOL;
	}
	
	echo '</ul>', PHP_EOL;
}
elseif (@$messages)
{
	echo '<ul class="notice">', PHP_EOL;
	
	foreach ($messages as $message)
	{
		echo '<li>', $this->e($message), '</li>', PHP_EOL;
	}
	
	echo '</ul>', PHP_EOL;
}

$this->insert('Partials/Forms/User/Edit');