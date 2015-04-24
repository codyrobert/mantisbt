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

<section-nav id="ticket-nav" items='<?php echo json_encode($categories); ?>'></section-nav>

<?php $this->stop(); ?>
<?php $this->start('sidebar'); ?>

<aside>
	
	<h4>Filter</h4>
	
	<?php
	$projects_list = [Lang::get('all_projects')];
	
	foreach (User::current()->projects() as $project)
	{
		$projects_list[$project->slug()] = $project->name;
	}
	
	$form = new Form('filter_rows', [
		'class'	=> 'form-style--fill-width',
	]);
	
	$form->addElement(new Form\Element\Select(null, 'project', $projects_list, [
		'id' => 'filter_view_by_project',
	]));
	
	$form->render();
	?>
</aside>

<aside>
	
	<h4>
		<a class="aux-link" href="#"><i class="mdi mdi-arrow-right-bold-circle-outline"></i></a>
		People
	</h4>
	
	<div class="user-list">
		<?php foreach (array_slice(User::current()->related_users(), 0, 13) as $user): ?>
		<a href="#">
			<?php echo $this->gravatar([
				'email'	=> $user->email,		
			]); ?>
			<?php //echo $user->realname; ?>
		</a>
		<?php endforeach; ?>
	</div>

</aside>

<?php $this->stop(); ?>

<div id="tickets-table" class="tabular-data">

	<?php
	foreach (User::current()->tickets() as $ticket):
	
		$row_categories = [];
		
		if ($ticket->status < 90)
		{
			$row_categories[] = 'open';
		
			if ($ticket->handler_id == User::current()->id)
			{
				$row_categories[] = 'assigned';
			}
			
			if ($ticket->reporter_id == User::current()->id)
			{
				$row_categories[] = 'reported';
			}
		}
		
		if ($ticket->status == 90)
		{
			$row_categories[] = 'closed';
		}
		
		$append_classes = ['row'];
		
		if (!in_array($current_category, $row_categories))
		{
			$append_classes[] = 'hide';
		}
	?>
	<a data-category="<?php echo implode(' ', $row_categories); ?>" data-project="<?php echo $ticket->project()->slug(); ?>" data-reporter="<?php echo $ticket->reporter_id; ?>" data-assigned="<?php echo $ticket->handler_id; ?>" href="<?php echo URL::get('ticket/'.$ticket->id); ?>" class="<?php $ticket->classes($append_classes, true); ?>">
		<div class="cell ticket-id"><strong><?php echo $ticket->id; ?></strong></div>
		<div class="cell ticket-project"><?php echo $ticket->project()->name; ?></div>
		<div class="cell ticket-summary"><?php echo $ticket->summary; ?></div>
	</a>
	<?php endforeach; ?>
	
</div>