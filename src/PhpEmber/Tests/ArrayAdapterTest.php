<?php
namespace PhpEmber\Tests;

use PhpEmber\ArrayAdapter;
use PhpEmber\AttributeInfo;
use PhpEmber\AttributeType;

class ArrayAdapterTest extends AdapterTestCase {
	
	function setUp() {
		
		$adapter = new ArrayAdapter('user');
		$adapter->addAttribute(new AttributeInfo('name', AttributeType::STRING_TYPE));
		$adapter->addAttribute(new AttributeInfo('canLogin', AttributeType::BOOLEAN_TYPE));
		$adapter->addAttribute(new AttributeInfo('createdAt', AttributeType::DATE_TYPE));
		$adapter->setData(require(__DIR__ . '/users.php'));
		
		$this->adapter = $adapter;
	}
	
}
