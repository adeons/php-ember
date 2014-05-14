<?php
namespace PhpEmber;

class Transform implements Serializer {
	
	private $attribute;
	private $required;
	private $allowRead = true;
	private $allowWrite = false;
	private $decodeCallback;
	private $encodeCallback;
	
	function __construct(AttributeInfo $attribute) {
		$this->attribute = $attribute;
		$this->required = $attribute->required;
		$this->allowRead = $attribute->readable;
		$this->allowWrite = $attribute->writable;
	}
	
	function getAttribute() {
		return $this->attribute;
	}
	
	function getName() {
		return $this->attribute->name;
	}
	
	function isRequired() {
		return $this->required;
	}
	
	function isReadable() {
		return $this->allowRead;
	}
	
	function setReadable($readable) {
		$this->allowRead = $readable;
	}
	
	function isWritable() {
		return $this->allowWrite;
	}
	
	function setWritable($writable) {
		$this->allowWrite = $writable;
	}
	
	function exclude() {
		$this->allowRead = false;
		$this->allowWrite = false;
		return $this;
	}
	
	function readOnly() {
		$this->allowRead = true;
		$this->allowWrite = false;
		return $this;
	}
	
	function writeOnly() {
		$this->allowRead = false;
		$this->allowWrite = true;
		return $this;
	}
	
	function onDecode($callback) {
		$this->decodeCallback = $callback;
		return $this;
	}
	
	function onEncode($callback) {
		$this->encodeCallback = $callback;
		return $this;
	}
	
	function decode(SerializerContext $context) {
		
		if(!$this->allowWrite) {
			return true;
		}
		
		$name = $this->attribute->name;
		
		if(!array_key_exists($name, $context->payload)) {
			return true;
		}
		
		$payload = $context->payload[$name];
		
		if($payload === null) {
			
			if($this->required) {
				$context->errors->addError($name, 'Cannot be null');
				return false;
			}
			
			$value = null;
			
		} else {
			
			list($value, $errors) = $this->fromPayload($payload);
			
			if($errors) {
				$context->errors->addErrors($name, $errors);
				return false;
			}
		}
		
		if($this->decodeCallback) {
			$value = call_user_func($this->decodeCallback, $value);
		}
		
		$context->getModel()->setAttribute($name, $value);
		return true;
	}
	
	function encode(SerializerContext $context) {
		
		if(!$this->allowRead) {
			return;
		}
		
		$name = $this->attribute->name;
		$value = $context->getModel()->getAttribute($name);
		
		if($value === null) {
			$payload = null;
			
		} else {
			
			$payload = $this->toPayload($value);
			
			if($this->encodeCallback) {
				$payload = call_user_func($this->encodeCallback, $payload);
			}
		}
		
		$context->payload[$name] = $payload;
	}
	
	function fromPayload($payload) {
		return array($payload, array());
	}
	
	function toPayload($value) {
		return $value;
	}
	
}
