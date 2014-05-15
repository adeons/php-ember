<?php
class User extends CActiveRecord {
	
	static function createTable($db) {
		
		$db->createCommand()->createTable('User', array(
			'id' => 'pk',
			'name' => 'string',
			'canLogin' => 'boolean',
			'createdAt' => 'datetime'
		));
	}
	
	static function fillTable($db) {
		
		$rows = require(__DIR__ . '/../../Tests/users.php');
		
		foreach($rows as $id => $row) {
			$row['id'] = $id;
			$db->createCommand()->insert('User', $row);
		}
	}
}
