<?php return [

	'access' => [
		10		=> 'viewer',
		25		=> 'reporter',
		40		=> 'updater',
		55		=> 'developer',
		70		=> 'manager',
		90		=> 'administrator',
	],
	
	'project_status' => [
		10		=> 'development',
		30		=> 'release',
		50		=> 'stable',
		70		=> 'obsolete',
	],
	
	'project_view_state' => [
		10		=> 'public',
		50		=> 'private',
	],
	
	'view_state' => [
		10		=> 'public',
		50		=> 'private',
	],
	
	'priority' => [
		10		=> 'none',
		20		=> 'low',
		30		=> 'normal',
		40		=> 'high',
		50		=> 'urgent',
		60		=> 'immediate',
	],
	
	'severity' => [
		10		=> 'feature',
		20		=> 'trivial',
		30		=> 'text',
		40		=> 'tweak',
		50		=> 'minor',
		60		=> 'major',
		70		=> 'crash',
		80		=> 'block',
	],
	
	'reproducibility' => [
		10		=> 'always',
		30		=> 'sometimes',
		50		=> 'random',
		70		=> 'have not tried',
		90		=> 'unable to duplicate',
		100		=> 'N/A',
	],
	
	'status' => [
		10		=> 'new',
		20		=> 'feedback',
		30		=> 'acknowledged',
		40		=> 'confirmed',
		50		=> 'assigned',
		80		=> 'resolved',
		90		=> 'closed',
	],
	
	'resolution' => [
		10		=> 'open',
		20		=> 'fixed',
		30		=> 'reopened',
		40		=> 'unable to duplicate',
		50		=> 'not fixable',
		60		=> 'duplicate',
		70		=> 'not a bug',
		80		=> 'suspended',
		90		=> 'wont fix',
	],
	
	'projection' => [
		10		=> 'none',
		30		=> 'tweak',
		50		=> 'minor fix',
		70		=> 'major rework',
		90		=> 'redesign',
	],
	
	'eta' => [
		10		=> 'none',
		20		=> '< 1 day',
		30		=> '2-3 days',
		40		=> '< 1 week',
		50		=> '< 1 month',
		60		=> '> 1 month',
	],
	
	'sponsorship' => [
		0		=> 'Unpaid',
		1		=> 'Requested',
		2		=> 'Paid',
	],
	
	'custom_field_type' => [
		0		=> 'string',
		1		=> 'numeric',
		2		=> 'float',
		3		=> 'enum',
		4		=> 'email',
		5		=> 'checkbox',
		6		=> 'list',
		7		=> 'multiselection list',
		8		=> 'date',
		9		=> 'radio',
		10		=> 'textarea',
	],

];