<?php
namespace Model\User;


class Preferences extends \Core\Model
{
	protected static $schema = [
		'table_name'		=> 'mantis_user_pref_table',
		'id_key'			=> 'id',
	];
}