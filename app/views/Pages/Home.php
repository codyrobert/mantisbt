<?php
use Core\Form;
use Core\Lang;
use Core\Menu;
use Core\URL;

use PFBC\Element;

use Model\Project;
use Model\User;


$this->layout('Layouts/Master', $this->data);
$this->start('before_content');
?>

<section-nav id="ticket-nav" items='<?php echo json_encode([
	['label' => 'Open Tickets', 'href' => URL::get('/#/open_tickets'), 'icon' => 'bookmark'],
	['label' => 'Recently Closed', 'href' => URL::get('/#/recently_closed'), 'icon' => 'bookmark-outline'],
]); ?>'></section-nav>

<?php $this->stop(); ?>
<?php $this->start('sidebar'); ?>

<aside>
	
	<h4>Filter</h4>
	
	<?php
	$form = new Form('filter_rows', [
		'class'	=> 'form-style--fill-width',
	]);
	
	$form->addElement(new Form\Element\Select(null, 'project', [
		OFF	=> Lang::get('all_projects'),
	] + User::current()->projects_list()));
	
	$form->addElement(new Form\Element\Select(null, 'status', [
		OFF	=> 'All Users',
	] + User::get_col('realname')));
	
	$form->render();
	?>

</aside>

<?php $this->stop(); ?>

<div id="tickets-table" class="tabular-data">

	<?php foreach (User::current()->tickets() as $ticket): ?>
	<a data-status="<?php echo ($ticket->status == 90) ? 'closed' : 'open'; ?>" href="<?php echo URL::get('ticket/'.$ticket->id); ?>" class="<?php $ticket->classes('row', true); ?>">
		<div class="cell ticket-id"><strong><?php echo $ticket->id; ?></strong></div>
		<div class="cell ticket-project"><?php echo $ticket->project()->name; ?></div>
		<div class="cell ticket-summary"><?php echo $ticket->summary; ?></div>
	</a>
	<?php endforeach; ?>
	
</div>