<?php
namespace PhpEmber;

/**
 * Adapter which stores its models in a PHP array.
 * Used mainly for unit testing.
 */
class ArrayAdapter implements Adapter {
	
	private $typeKey;
	private $container;
	private $attributes = array();
	private $data = array();
	private $lastId = 0;
	
	function __construct($typeKey, AdapterContainer $container = null) {
		$this->typeKey = $typeKey;
		$this->container = $container;
	}
	
	function getContainer() {
		return $this->container;
	}
	
	function getTypeKey() {
		return $this->typeKey;
	}
	
	function getData() {
		return $this->data;
	}
	
	function setData(array $data) {
		$this->data = $data;
		$this->lastId = max(array_keys($data));
	}
	
	function getLastId() {
		return $this->lastId;
	}
	
	function getAttributeNames() {
		return array_keys($this->attributes);
	}
	
	function getAttributeInfo($name) {
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}
	
	function addAttribute(AttributeInfo $info) {
		$this->attributes[$info->name] = $info;
	}
	
	function find($id) {
		
		if(!isset($this->data[$id])) {
			return null;
		}
		
		return new ArrayModelProxy($this, $id, $this->data[$id]);
	}
	
	function findMany(array $ids) {
		$data = array();
		
		foreach($ids as $id) {
			
			if(isset($this->data[$id])) {
				
				$data[$id] = $this->data[$id];
			}
		}
		
		return new ArrayModelIterator($this, $data);
	}
	
	function findAll($query, array $options) {
		
		return array(
			new ArrayModelIterator($this, $this->data),
			count($this->data)
		);
	}
	
	function create() {
		return new ArrayModelProxy($this);
	}
	
	function insert(array $attributes, $id = null) {
		
		if($id) {
			
			if($id > $this->lastId) {
				$this->lastId = $id;
			}
			
		} else {
			
			$this->lastId ++;
			$id = $this->lastId;
		}
		
		$this->data[$id] = $attributes;
		return $id;
	}
	
	function update($id, array $attributes, $newId = null) {
		
		if($newId) {
			unset($this->data[$id]);
			$id = $newId;
		}
		
		$this->data[$id] = $attributes;
	}
	
	function remove($id) {
		
		if(!isset($this->data[$id])) {
			return false;
		}
		
		unset($this->data[$id]);
		return true;
	}
	
}

class ArrayModelIterator implements ModelIterator {
	
	private $adapter;
	private $data;
	private $proxy;
	
	function __construct(ArrayAdapter $adapter, array $data) {
		$this->adapter = $adapter;
		$this->data = $data;
		$this->update();
	}
	
	function count() {
		return count($this->data);
	}
	
	function current() {
		return $this->proxy;
	}
	
	function key() {
		return $this->proxy ? $this->proxy->id : null;
	}
	
	function next() {
		next($this->data);
		$this->update();
	}
	
	function rewind() {
		reset($this->data);
		$this->update();
	}
	
	function valid() {
		return $this->proxy != null;
	}
	
	protected function update() {
		
		$id = key($this->data);
		
		$this->proxy = $id ? new ArrayModelProxy($this->adapter, $id, current($this->data)) : null;
	}
	
}

class ArrayModelProxy implements ModelProxy {
	
	private $adapter;
	private $oldId;
	private $id;
	private $attributes;
	
	function __construct(ArrayAdapter $adapter, $id = null, array $attributes = array()) {
		$this->adapter = $adapter;
		$this->oldId = $id;
		$this->id = $id;
		$this->attributes = $attributes;
	}
	
	function getAdapter() {
		return $this->adapter;
	}
	
	function canSetId() {
		return true;
	}
	
	function getId() {
		return $this->id;
	}
	
	function setId($id) {
		$this->id = $id;
	}
	
	function getAttribute($name) {
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}
	
	function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
	}
	
	function getRelated($name) {
		return null;
	}
	
	function save(ErrorBag $errors) {
		
		if($this->oldId) {
			
			$this->adapter->update($this->id, $this->attributes, $this->oldId);
			
		} else {
			
			$this->id = $this->adapter->insert($this->attributes, $this->id);
		}
		
		$this->oldId = $this->id;
		return true;
	}
	
}
