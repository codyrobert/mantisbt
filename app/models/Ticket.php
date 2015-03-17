<?php
namespace Model;


use Core\Model;


class Ticket extends Model
{
	protected $schema = [
		'table_name'	=> 'mantis_bug_table',
		'id_key'		=> 'id',
	];
}