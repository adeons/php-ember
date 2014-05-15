<?php
// test models (ID => attributes)
return array(
	1 => array(
		'name' => 'admin',
		'canLogin' => true,
		'createdAt' => '2014-5-5 12:00'
	),
	
	2 => array(
		'name' => 'tester',
		'canLogin' => true,
		'createdAt' => '2014-5-6 12:30'
	),
	
	3 => array(
		'name' => 'disabled-user',
		'canLogin' => false,
		'createdAt' => '2014-5-7 13:00'
	),
	
	4 => array(
		'name' => 'sample-user',
		'canLogin' => true,
		'createdAt' => '2014-5-8 13:30'
	)
);
