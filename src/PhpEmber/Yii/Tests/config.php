<?php
// test application configuration
return array(
	'basePath' => __DIR__,
	'components' => array(
		'db' => array(
			'connectionString' => 'sqlite::memory:'
		)
	)
);
