<?php
foreach (timezone_identifiers_list() as $identifier) 
{
	$zone = explode('/', $identifier, 2);
	$id = $zone[1] ? $zone[1] : $identifier;
	
	$ungrouped[$identifier] = str_replace('_', ' ', $id);
	$grouped[$zone[0]][$identifier] = str_replace('_', ' ', $id);
}

return [
	'ungrouped'	=> @(array)$ungrouped,
	'grouped'	=> @(array)$grouped,
];