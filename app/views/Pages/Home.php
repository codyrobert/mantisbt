<?php
use Core\Form;
use Core\Lang;
use Core\Menu;
use Core\URL;

use PFBC\Element;

$this->layout('Layouts/Master', $this->data);
$this->start('before_content');
?>

<nav class="section-nav">
	<ul>
		<li><a href="#"><i class="mdi mdi-rss"></i> News Feed</a></li>
		<li><a href="#" class="active"><i class="mdi mdi-bookmark"></i> Open Tickets</a></li>
		<li><a href="#"><i class="mdi mdi-bookmark-outline"></i> Recently Closed</a></li>
	</ul>
</nav>

<?php $this->stop(); ?>
<?php $this->start('sidebar'); ?>

<aside>
	
	<h4>Filter</h4>
	
	<?php
	$form = new Form('filter_rows', [
		'class'	=> 'form-style--fill-width',
	]);
	
	$form->addElement(new Form\Element\Select(Lang::get('project'), 'project', [
		OFF	=> Lang::get('all_projects'),
	]));
	
	$form->render();
	?>

</aside>

<?php $this->stop(); ?>

<div class="tabular-data">

	<?php foreach ($tickets as $ticket): ?>
	<div class="row">
		<strong class="cell"><?php echo $ticket->id; ?></strong>
		<div class="cell"><a href="<?php echo URL::get('ticket/'.$ticket->id); ?>"><?php echo $ticket->summary; ?></a></div>
	</div>
	<?php endforeach; ?>
	
</div>