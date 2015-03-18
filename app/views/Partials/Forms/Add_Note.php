<?php

use Core\Access;
use Core\Config;
use Core\Helper;
use Core\Form;
use Core\Lang;

?><form id="add_note" method="post" action="bugnote_add.php">
	
	<div class="field-container">
		<label class="field-label" for="bugnote_text"><?php echo Lang::get( 'bugnote'); ?></label>
		<div class="field-input"><textarea name="bugnote_text" cols="60" rows="10"></textarea></div>
	</div>
	
	<?php if (Access::has_bug_level(Config::mantis_get('set_view_status_threshold'), $ticket->id)): ?>
	<div class="field-container">
		<label class="field-label" for="bugnote_add_view_status"><?php echo Lang::get( 'view_status'); ?></label>
		<div class="field-input">
			<input type="checkbox" id="bugnote_add_view_status" name="private" <?php Helper::check_checked(Config::mantis_get('default_bugnote_view_status'), VS_PRIVATE); ?> />
			<label for="bugnote_add_view_status"><?php echo Lang::get( 'private'); ?></label>
		</div>
	</div>
	<?php else: ?>
		<?php echo Helper::get_enum_element('project_view_state', Config::mantis_get('default_bugnote_view_status')); ?>
	<?php endif; ?>
	
	<?php if (Config::mantis_get('time_tracking_enabled') && Access::has_bug_level(Config::mantis_get('time_tracking_edit_threshold'), $ticket->id)): ?>
	<h4><?php echo Lang::get( 'time_tracking'); ?></h4>
	
		<?php if( Config::mantis_get( 'time_tracking_stopwatch' ) ) { ?>
		<input type="text" name="time_tracking" class="stopwatch_time" size="8" placeholder="hh:mm:ss" />
		<input type="button" name="time_tracking_toggle" class="stopwatch_toggle" value="<?php echo Lang::get('time_tracking_stopwatch_start'); ?>" />
		<input type="button" name="time_tracking_reset" class="stopwatch_reset" value="<?php echo Lang::get('time_tracking_stopwatch_reset'); ?>" />
		<?php } else { ?>
		<input type="text" name="time_tracking" size="5" placeholder="hh:mm" />
		<?php } ?>

	<?php endif; ?>

	<div class="field-submit">
		<button class="button"><?php echo Lang::get( 'add_bugnote_button'); ?></button>
	</div>
	
	<?php echo Form::security_field('bugnote_add'); ?>
	<input type="hidden" name="bug_id" value="<?php echo $ticket->id; ?>" />
	
</form>