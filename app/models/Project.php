<?php
namespace Model;


use Core\Access;
use Core\Auth;
use Core\Bug;
use Core\Config;
use Core\DB;
use Core\Model;
use Core\Print_Util;


class Project extends Model
{
	protected static $schema = [
		'table_name'		=> 'mantis_project_table',
		'id_key'			=> 'id',
	];
}