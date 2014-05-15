<?php
namespace PhpEmber\Yii\Tests;

use PhpEmber\AttributeType;
use PhpEmber\Tests\AdapterTestCase;
use PhpEmber\Yii\ActiveAdapter;

class ActiveAdapterTest extends AdapterTestCase {
	
	static function setUpBeforeClass() {
		
		if(TestHelper::canTest()) {
			TestHelper::setUp(array('User'));
		}
	}
	
	static function tearDownAfterClass() {
		
		if(TestHelper::canTest()) {
			TestHelper::tearDown();
		}
	}
	
	function setUp() {
		
		if(TestHelper::canTest()) {
			TestHelper::fillTables();
			
		} else {
			
			$this->markTestSkipped('Yii include file not set or invalid.');
		}
		
		$this->adapter = new ActiveAdapter('user');
	}
	
	function tearDown() {
		
		if(TestHelper::canTest()) {
			TestHelper::clearTables();
		}
	}
	
}
