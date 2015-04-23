<?php return [
	
	'priority' => function($ticket) 
	{
		return (bool)strstr($ticket->summary, '[P]');
	}
	
];