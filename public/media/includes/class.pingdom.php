<?php
use Acquia\Pingdom\PingdomApi;

class Pingdom {

	private static $_instance;
	
	private static $_apiLimits = array(
		'checks' => 288, // 12 times an hour
		'averageResponseTimes' => 4, // four times a day
		'performance' => 2, // twice a day
	);
	
	private function __construct() {}
	
	static function factory()
	{
		if (!self::$_instance)
		{
			self::$_instance = new PingdomApi('admin@flickerbox.com', 'c$i4zi8FRiE*i9', 'rln0e7zt8wmyp4ohqqp1f85sf6yhqzd7');
		}
		
		return self::$_instance;
	}
	
	static function apiLimitTimestampFor($key)
	{
		$secondsInDay = 24 * 60 * 60;
		$limitPerDay = self::$_apiLimits[$key];
		$now = time();
		
		$timestampLimit = $now -  ($secondsInDay / $limitPerDay);
		
		return $timestampLimit;
	}
	
	static function canQueryApi($name)
	{
		$log = R::findOne('apiLog', 'name = ? and timestamp > ?', array($name, self::apiLimitTimestampFor($name)));
		
		if (!$log)
		{
			$bean = R::dispense('apilog');
			$bean->name = $name;
			$bean->timestamp = time();
			
			R::store($bean);
		}
		
		return !(bool)$log;
	}
	
	static function pullChecks()
	{
		if (self::canQueryApi('checks'))
		{
			$pingdom = self::factory();
			$checks = $pingdom->getChecks();
	
			foreach ((array)$checks as $check)
			{
				if (!$bean = R::findOne('pingdomcheck', 'pingdom_id = ?', array($check->id)))
				{
					$bean = R::dispense('pingdomcheck');
					$bean->pingdom_id = $check->id;
				}
				
				$bean->name = $check->name;
				$bean->host = $check->hostname;
				$bean->lasterrortime = $check->lasterrortime;
				$bean->lasttesttime = $check->lasttesttime;
				$bean->lastresponsetime = $check->lastresponsetime;
				$bean->status = $check->status;
				
				R::store($bean);
			}
		}
	}
	
	static function pullAverageResponseTimes()
	{
		if (self::canQueryApi('averageResponseTimes'))
		{
			$pingdom = self::factory();
			
			foreach (self::getChecks() as $check)
			{
				$averages = $pingdom->request('GET', 'summary.hoursofday/'.$check->pingdom_id);
				
				if (count((array) $averages->hoursofday))
				{
					if (!$bean = R::findOne('pingdomaverages', 'pingdom_id = ?', array($check->id)))
					{
						$bean = R::dispense('pingdomaverages');
						$bean->pingdom_id = $check->pingdom_id;
					}
					
					$bean->data = json_encode($averages->hoursofday);
					
					R::store($bean);
				}
			}
		}
	}
	
	static function pullPerformance()
	{
		if (self::canQueryApi('performance'))
		{
			$pingdom = self::factory();
			
			foreach (self::getChecks() as $check)
			{
				$performance = $pingdom->request('GET', 'summary.performance/'.$check->pingdom_id, array(
					'from'			=> strtotime('-1 day'),
					'includeuptime' => true
				));
				
				foreach ((array)$performance->summary->hours as $hour)
				{
					if (!$bean = R::findOne('pingdomhours', 'pingdom_id = ? and starttime = ?', array($check->id, $hour->starttime)))
					{
						$bean = R::dispense('pingdomhours');
						$bean->pingdom_id = $check->pingdom_id;
						$bean->starttime = $hour->starttime;
						$bean->uptime = $hour->uptime;
						$bean->avgresponse = $hour->avgresponse;
						$bean->unmonitored = $hour->unmonitored;
						
						R::store($bean);
					}
				}
			}
		}
	}
	
	static function getChecks()
	{
		return R::findAll('pingdomcheck');
	}
	
	static function getCheckIdByName($name)
	{
		if ($bean = R::findOne('pingdomcheck', 'name = ?', array($name)))
		{
			return $bean->pingdomId;
		}
		
		return false;
	}
	
	static function getClients()
	{
		return R::findAll('client', ' order by nicename asc');
	}
	
	static function getAverageResponseTimesForCheck($id)
	{
		if ($bean = R::findOne('pingdomaverages', 'pingdom_id = ?', array($id)))
		{
			foreach((array)json_decode($bean->data) as $row)
			{
				$averages[] = (int)$row->avgresponse;
			}
			
			return $averages;
		}
		
		return false;
	}
	
	static function getAveragePerformanceForCheck($id)
	{
		if ($hours = R::find('pingdomhours', 'pingdom_id = ? and starttime > ?', array($id, strtotime('-30 days'))))
		{
			$uptimes = array();
			
			foreach ($hours as $hour)
			{
				$uptimes[] = $hour->uptime;
			}
			
			$totalUptime = array_sum($uptimes);
			$totalMonitored = count($uptimes) * 3600;
			
			return round($totalUptime / $totalMonitored * 100, 2);
		}
		
		return false;
	}
}