<?php
$this->layout('layouts/master', $this->data);

print_recently_visited();
?>

<div>

<?php
if( \Flickerbox\Config::get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_TOP || \Flickerbox\Config::get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTH ) {
	\Flickerbox\HTML::status_legend();
	echo '<br />';
}
?>

<?php echo $content; ?>

<?php
if( \Flickerbox\Config::get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTTOM || \Flickerbox\Config::get( 'status_legend_position' ) == STATUS_LEGEND_POSITION_BOTH ) {
	\Flickerbox\HTML::status_legend();
}