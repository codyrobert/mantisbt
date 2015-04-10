<?php
use Core\Menu;
use Core\URL;

$this->layout('Layouts/Master', $this->data);
$this->section('sidebar');
?>

<?php $this->stop(); ?>

<nav class="section-nav">
	<ul>
		<li><a href="#" class="active"><i class="mdi mdi-bookmark-outline"></i> Tickets</a></li>
		<li><a href="#"><i class="mdi mdi-rss"></i> News Feed</a></li>
	</ul>
</nav>

<div class="tabular-data">

	<?php foreach ($tickets as $ticket): ?>
	<div class="row">
		<strong class="cell"><?php echo $ticket->id; ?></strong>
		<div class="cell"><a href="<?php echo URL::get('ticket/'.$ticket->id); ?>"><?php echo $ticket->summary; ?></a></div>
	</div>
	<?php endforeach; ?>
	
</div>