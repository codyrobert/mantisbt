<?php
$this->layout('Layouts/Master', $this->data);

\Core\Print_Util::recently_visited();
?>

<div>

<?php
if( \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_TOP || \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTH ) {
	\Core\HTML::status_legend();
	echo '<br />';
}
?>

<?php echo $content; ?>

<?php
if( \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTTOM || \Core\Config::mantis_get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTH ) {
	\Core\HTML::status_legend();
}