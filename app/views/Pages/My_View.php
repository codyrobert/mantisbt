<?php
use Core\Menu;

$this->layout('Layouts/Master', $this->data);
?>

<div>

<?php
if( \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_TOP || \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTH ) {
	\Core\HTML::status_legend();
	echo '<br />';
}
?>

<header class="page-title">
	<h2><?php echo $this->e($section_title); ?></h2>
</header>

<div>
	<div class="myview-boxes">
	
		<?php foreach ($boxes as $label): ?>
		<div class="section">
			<?php $this->insert('Partials/My_View_Box', $this->data + ['label' => $label]); ?>
		</div>
		<?php endforeach; ?>
		
	</div>
</div>


<?php echo $content; ?>

<?php
if( \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTTOM || \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTH ) {
	\Core\HTML::status_legend();
}