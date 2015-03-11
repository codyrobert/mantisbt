<?php

use Core\Lang;
use Core\URL;

?><div id="bug-jump">
	<form method="post" class="bug-jump-form" action="<?php echo URL::get('jump_to_bug.php'); ?>">
		<fieldset class="bug-jump">
			<input type="hidden" name="bug_label" value="<?php echo Lang::get('issue_id'); ?>" />
			<input type="text" name="bug_id" size="8" />
			<input type="submit" value="<?php echo Lang::get('jump'); ?>" />
		</fieldset>
	</form>
</div>