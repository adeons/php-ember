<?php
namespace PhpEmber;

class AdapterContainer {
	
	private $adapters = array();
	private $factories = array();
	
	function getAdapter($name) {
		
		if(isset($this->adapters[$name])) {
			return $this->adapters[$name];
		}
		
		if(isset($this->factories[$name])) {
			return $this->createAdapter($name);
		}
		
		return null;
	}
	
	function addAdapter(Adapter $adapter) {
		$this->adapters[$adapter->getTypeKey()] = $adapter;
	}
	
	function removeAdapter($name) {
		
		unset($this->adapters[$name]);
		unset($this->factories[$name]);
	}
	
	function register($name, $factory) {
		$this->factories[$name] = $factory;
	}
	
	protected function createAdapter($name) {
		
		$factory = $this->factories[$name];
		
		if(is_string($factory)) {
			
			// class name
			$adapter = new $factory($name, $this);
			
		} else {
			
			// lambda function or callable array
			$adapter = call_user_func($factory, $this, $name);
		}
		
		$this->addAdapter($adapter);
		return $adapter;
	}
	
}
