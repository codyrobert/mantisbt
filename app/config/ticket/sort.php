<?php return [

	10 => [
		function($ticket_a, $ticket_b) 
		{
			$priority_a = (bool)in_array('priority', $ticket_a->tags());
			$priority_b = (bool)in_array('priority', $ticket_b->tags());
			
			if ($priority_a === $priority_b)
			{
				return 0;
			}
			
			return ($priority_a < $priority_b) ? 1 : -1;
		},
	],
	
];