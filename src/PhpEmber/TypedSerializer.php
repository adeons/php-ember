<?php
namespace PhpEmber;

class TypedSerializer implements Serializer {
	
	/**
	 * @var AdapterContainer
	 */
	private $adapters;
	
	/**
	 * @var Serializer[]
	 */
	private $types = array();
	
	/**
	 * @var callable[]
	 */
	private $callbacks = array();
	
	function __construct(AdapterContainer $adapters) {
		$this->adapters = $adapters;
	}
	
	/**
	 * @param string $typeKey
	 * @return Serializer
	 */
	function forType($typeKey) {
		
		if(isset($this->types[$typeKey])) {
			
			// reuse existing instance
			return $this->types[$typeKey];
		}
		
		return $this->createForType($typeKey);
	}
	
	/**
	 * @param ModelProxy $model
	 * @return Serializer
	 */
	function forModel(ModelProxy $model) {
		
		return $this->forType($model->getAdapter()->getTypeKey());
	}
	
	/**
	 * @param string $typeKey
	 * @param callable $callback
	 */
	function register($typeKey, $callback) {
		$this->callbacks[$typeKey] = $callback;
	}
	
	/**
	 * @param string $typeKey
	 * @return Serializer
	 */
	protected function createForType($typeKey) {
		
		$adapter = $this->adapters->getAdapter($typeKey);
		
		$serializer = new ModelSerializer($adapter);
		
		if(isset($this->callbacks[$typeKey])) {
			
			call_user_func($this->callbacks[$typeKey], $serializer);
		}
		
		$serializer->makeTransforms();
		
		$this->types[$typeKey] = $serializer;
		return $serializer;
	}
	
	function decode(SerializerContext $context) {
		
		$this->forModel($context->getModel())->decode($context);
	}
	
	function encode(SerializerContext $context) {
		
		$this->forModel($context->getModel())->encode($context);
	}
	
}
