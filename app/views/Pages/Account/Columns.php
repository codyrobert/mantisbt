<?php
$this->layout('Layouts/Master', $this->data);


define( 'ACCOUNT_COLUMNS', true );

define( 'MANAGE_COLUMNS_INC_ALLOW', true );
include (ROOT.'/manage_columns_inc.php' );