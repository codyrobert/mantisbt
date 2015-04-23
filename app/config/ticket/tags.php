<?php return [
	
	'assigned' => function($ticket) 
	{
		return (bool)($ticket->handler_id == \Model\User::current()->id);
	},
	
	'closed' => function($ticket) 
	{
		return (bool)($ticket->status == 90);
	},
	
	'open' => function($ticket) 
	{
		return (bool)($ticket->status < 90);
	},
	
	'priority' => function($ticket) 
	{
		return (bool)strstr($ticket->summary, '[P]');
	},
	
	'reported' => function($ticket) 
	{
		return (bool)($ticket->reporter_id == \Model\User::current()->id);
	},
	
];