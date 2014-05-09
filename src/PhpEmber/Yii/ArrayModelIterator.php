<?php
namespace PhpEmber\Yii;

use PhpEmber\ModelIterator;

class ArrayModelIterator implements ModelIterator {
	
	private $adapter;
	private $models;
	private $proxy;
	
	function __construct(ActiveAdapter $adapter, array $models) {
		$this->adapter = $adapter;
		$this->models = $models;
		$this->update();
	}
	
	function count() {
		return count($this->models);
	}
	
	function current() {
		return $this->proxy;
	}
	
	function key() {
		return $this->proxy ? $this->proxy->id : null;
	}
	
	function next() {
		next($this->models);
		$this->update();
	}
	
	function rewind() {
		reset($this->models);
		$this->update();
	}
	
	function valid() {
		return $this->proxy != null;
	}
	
	protected function update() {
		
		$current = current($this->models);
		
		$this->proxy = $current ? new ActiveModelProxy($this->adapter, $current) : null;
	}
	
}
