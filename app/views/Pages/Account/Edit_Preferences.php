<?php
$this->layout('Layouts/Master', $this->data);

define( 'ACCOUNT_PREFS_INC_ALLOW', true );
include( ROOT.'account_prefs_inc.php' );

edit_account_prefs();