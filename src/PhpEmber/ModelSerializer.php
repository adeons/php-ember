<?php
namespace PhpEmber;

class ModelSerializer implements Serializer {
	
	private static $typeMap = array(
		AttributeType::BOOLEAN_TYPE => 'PhpEmber\\BooleanTransform',
		AttributeType::INTEGER_TYPE => 'PhpEmber\\IntegerTransform',
		AttributeType::FLOAT_TYPE => 'PhpEmber\\FloatTransform',
		AttributeType::STRING_TYPE => 'PhpEmber\\StringTransform',
		AttributeType::DATE_TYPE => 'PhpEmber\\DateTransform',
		AttributeType::BELONGS_TO => 'PhpEmber\\Transform',
		AttributeType::HAS_MANY => 'PhpEmber\\Transform'
	);
	
	public $excludes = array();
	
	private $adapter;
	private $idName = 'id';
	private $transforms = array();
	private $encodeCallback;
	private $decodeCallback;
	
	function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
	}
	
	function getAdapter() {
		return $this->adapter;
	}
	
	function getIdName() {
		return $this->idName;
	}
	
	function setIdName($name) {
		$this->idName = $name;
	}
	
	function getTransforms() {
		return $this->transforms;
	}
	
	/**
	 * @param string $name
	 * @return Transform
	 */
	function getTransform($name) {
		
		return isset($this->transforms[$name]) ? $this->transforms[$name] : null;
	}
	
	function addTransform(Transform $transform) {
		
		$this->transforms[$transform->getName()] = $transform;
		return $transform;
	}
	
	function removeTransform($name) {
		unset($this->transforms[$name]);
	}
	
	/**
	 * @param string $name
	 * @return Transform
	 */
	function attr($name) {
		
		$transform = $this->getTransform($name);
		
		if(!$transform) {
			$transform = $this->makeTransform($name);
			
			if(!$transform) {
				
				$transform = $this->addTransform(
					new Transform($this->adapter->getAttributeInfo($name)));
			}
		}
		
		return $transform;
	}
	
	/**
	 * @param string $name
	 * @return Transform
	 */
	function makeTransform($name) {
		
		$info = $this->adapter->getAttributeInfo($name);
		
		if(!$info) {
			return null;
		}
		
		if(!isset(self::$typeMap[$info->type])) {
			return null;
		}
		
		$className = self::$typeMap[$info->type];
		$transform = new $className($info);
		
		$this->addTransform($transform);
		return $transform;
	}
	
	function makeTransforms() {
		
		foreach($this->adapter->getAttributeNames() as $name) {
			
			if(isset($this->transforms[$name]) || in_array($name, $this->excludes)) {
				continue;
			}
			
			$this->makeTransform($name);
		}
	}
	
	/**
	 * @param callable $callback
	 * @return SimpleSerializer
	 */
	function onEncode($callback) {
		$this->encodeCallback = $callback;
		return $this;
	}
	
	/**
	 * @param callable $callback
	 * @return SimpleSerializer
	 */
	function onDecode($callback) {
		$this->decodeCallback = $callback;
		return $this;
	}
	
	function decode(SerializerContext $context) {
		
		if($this->decodeCallback) {
			
			if(call_user_func($this->decodeCallback, $context)) {
				// skip default behavior if callback returns true
				return true;
			}
		}
		
		$model = $context->getModel();
		
		if($model->canSetId() && array_key_exists($this->idName, $context->payload)) {
			
			$newId = $context->payload[$this->idName];
			
			if(is_string($newId) && $newId != $model->getId()) {
				$model->setId($newId);
			}
		}
		
		foreach($this->transforms as $transform) {
			$transform->decode($context);
		}
	}
	
	function encode(SerializerContext $context) {
		
		if($this->encodeCallback) {
			
			if(call_user_func($this->encodeCallback, $context)) {
				// skip default behavior if callback returns true
				return true;
			}
		}
		
		$model = $context->getModel();
		
		$context->payload[$this->idName] = $model->getId();
		
		foreach($this->transforms as $transform) {
			$transform->encode($context);
		}
	}
	
}
