<?php
namespace PhpEmber;

class ModelPool {
	
	private $serializer;
	private $payloads = array();
	private $singularized;
	private $meta = array();
	
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
	
	function getSingularized() {
		return $this->singularized;
	}
	
	function setSingularized($typeKey) {
		$this->singularized = $typeKey;
	}
	
	function getMeta($name) {
		return isset($this->meta[$name]) ? $this->meta[$name] : null;
	}
	
	function setMeta($name, $value) {
		$this->meta[$name] = $value;
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
	
	function toArray() {
		$result = array();
		
		foreach($this->payloads as $typeKey => $models) {
			
			$result[$typeKey] = $typeKey == $this->singularized ?
				current($models) :
				array_values($models);
		}
		
		if($this->meta) {
			$result['meta'] = $this->meta;
		}
		
		return $result;
	}
	
}
