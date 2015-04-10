<?php
namespace Model;


class User extends \Core\Model
{
	protected static $_current_user = null;
	
	protected static $schema = [
		'table_name'		=> 'mantis_user_table',
		'id_key'			=> 'id',
	];
	
	protected $preferences = null;
	
	static function current()
	{
		if (self::$_current_user === null)
		{
			$user = new User(\Core\Auth::get_current_user_id());
			self::$_current_user = $user->loaded() ? $user : false;
		}
		
		return self::$_current_user;
	}
	
	function &preferences()
	{
		if ($this->preferences === null)
		{
			$this->preferences = User\Preferences::find('user_id = ?', [
				$this->id,
			]);
		}
		
		return $this->preferences;
	}
}