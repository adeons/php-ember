<?php
namespace PhpEmber\Tests;

use PhpEmber\AttributeType;
use PhpEmber\ErrorBag;

/**
 * Tests adapter basic usage.
 */
abstract class AdapterTestCase extends \PHPUnit_Framework_TestCase {
	
	protected $adapter;
	
	function testGetTypeKey() {
		
		$this->assertEquals('user', $this->adapter->getTypeKey());
	}
	
	/**
	 * @dataProvider attributeProvider
	 */
	function testGetAttributeInfo($name, $type) {
		
		$info = $this->adapter->getAttributeInfo($name);
		
		$this->assertNotNull($info);
		$this->assertEquals($type, $info->type);
	}
	
	/**
	 * @dataProvider falseIdProvider
	 */
	function testFindByIdNotFound($id) {
		
		$this->assertNull($this->adapter->find($id));
	}
	
	/**
	 * @dataProvider idProvider
	 */
	function testFindById($id) {
		
		$model = $this->adapter->find($id);
		
		$this->assertNotNull($model);
		$this->assertEquals($id, $model->getId());
	}
	
	/**
	 * @dataProvider multiIdProvider
	 */
	function testFindMany(array $ids) {
		
		$models = $this->adapter->findMany($ids);
		
		$pending = count($ids);
		$found = array();
		
		while($pending) {
			
			$model = $models->current();
			$this->assertNotNull($model);
			
			// prevent infinite loop if model iterator has bugs
			$pending --;
			$found[] = $model->getId();
			
			$models->next();
		}
		
		// the two arrays must contain the same IDs, but order may differ
		$this->assertEmpty(array_diff($ids, $found));
	}
	
	function testCreateNullId() {
		
		$this->assertNull($this->adapter->create()->getId());
	}
	
	function testSetId() {
		
		$model = $this->adapter->create();
		$model->setId(10);
		
		$this->assertEquals(10, $model->getId());
	}
	
	function testUpdate() {
		
		$errors = new ErrorBag;
		
		$model = $this->adapter->find(1);
		$model->setAttribute('name', 'root');
		$model->save($errors);
		
		$this->assertFalse($errors->hasErrors());
		
	}
	
	function testCreate() {
		
		$errors = new ErrorBag;
		
		$model = $this->adapter->create();
		$model->setAttribute('name', 'guest');
		$model->setAttribute('canLogin', true);
		$model->setAttribute('createdAt', '2014-5-9 14:00');
		$model->save($errors);
		
		$this->assertFalse($errors->hasErrors());
		$this->assertNotEmpty($model->getId());
	}
	
	/**
	 * @dataProvider idProvider
	 */
	function testRemove($id) {
		
		$this->assertTrue($this->adapter->remove($id));
		$this->assertNull($this->adapter->find($id));
	}
	
	function attributeProvider() {
		return array(
			array('name', AttributeType::STRING_TYPE),
			array('canLogin', AttributeType::BOOLEAN_TYPE),
			array('createdAt', AttributeType::DATE_TYPE)
		);
	}
	
	function idProvider() {
		return array(
			array(1),
			array(2),
			array(3)
		);
	}
	
	function falseIdProvider() {
		return array(
			array(100),
			array(200),
			array(300)
		);
	}
	
	function multiIdProvider() {
		return array(
			array(array(1)),
			array(array(1, 2)),
			array(array(1, 2, 3))
		);
	}
	
}
