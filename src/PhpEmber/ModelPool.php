<?php
namespace PhpEmber;

class ModelPool {
	
	private $serializer;
	private $payloads = array();
	
	function __construct(Serializer $serializer) {
		$this->serializer = $serializer;
	}
	
	function getSerializer() {
		return $this->serializer;
	}
	
	function hasPayload($typeKey, $id) {
		return isset($this->payloads[$typeKey][$id]);
	}
	
	function getPayload($typeKey, $id) {
		
		return isset($this->payloads[$typeKey][$id]) ?
			$this->payloads[$typeKey][$id] : null;
	}
	
	function setPayload($typeKey, $id, array $payload) {
		$this->payloads[$typeKey][$id] = $payload;
	}
	
	/**
	 * @param ModelProxy $model
	 * @param boolean $relations
	 * @return boolean
	 */
	function poolModel(ModelProxy $model, $relations = false) {
		
		$adapter = $model->getAdapter();
		$typeKey = $adapter->getTypeKey();
		$id = $model->getId();
		
		if($this->hasPayload($typeKey, $id)) {
			// already serialized
			return false;
		}
		
		$context = new SerializerContext($model);
		$this->serializer->encode($context);
		
		$this->setPayload($typeKey, $id, $context->payload);
		
		if($relations) {
			
			foreach($adapter->getAttributeNames() as $attr) {
				
				if(!$adapter->getAttributeInfo($attr)->relatedType) {
					continue;
				}
				
				$related = $model->getRelated($attr);
				
				if(!$related) {
					continue;
				}
				
				$this->poolModels($related);
			}
		}
		
		return true;
	}
	
	/**
	 * @param ModelIterator $models
	 * @param boolean $relations
	 */
	function poolModels(ModelIterator $models, $relations = false) {
		
		foreach($models as $model) {
			$this->poolModel($model, $relations);
		}
	}
	
	function toArray($mainType = null, $mainAsObject = false) {
		$result = array();
		
		foreach($this->payloads as $typeKey => $typePayloads) {
			
			if($mainAsObject && $typeKey == $mainType) {
				
				// single a single object requested (not an array)
				$finalValue = current($typePayloads);
				
			} else {
				
				// remove ID keys
				$finalValue = array_values($typePayloads);
			}
			
			$result[$typeKey] = $finalValue;
		}
		
		if(!$mainAsObject && !isset($result[$mainType])) {
			
			// main type result should be an array, but no payloads for it were found
			// so, add an empty array (see #1)
			$result[$mainType] = array();
		}
		
		return $result;
	}
	
}
