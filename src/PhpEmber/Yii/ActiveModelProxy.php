<?php
namespace PhpEmber\Yii;

use PhpEmber\ErrorBag;
use PhpEmber\ModelProxy;

class ActiveModelProxy implements ModelProxy {
	
	private $adapter;
	public $ar;
	
	function __construct(ActiveAdapter $adapter, \CActiveRecord $ar = null) {
		$this->adapter = $adapter;
		$this->ar = $ar;
	}
	
	function getAdapter() {
		return $this->adapter;
	}
	
	function canSetId() {
		return $this->adapter->canSetId();
	}
	
	function getId() {
		return $this->ar->getPrimaryKey();
	}
	
	function setId($id) {
		$this->ar->setPrimaryKey($id);
	}
	
	function getAttributeNames() {
		return $this->adapter->getAttributeNames();
	}
	
	function getAttribute($name) {
		return $this->adapter->readAttribute($this->ar, $name);
	}
	
	function setAttribute($name, $value) {
		$this->adapter->writeAttribute($this->ar, $name, $value);
	}
	
	function getRelated($name) {
		
		if(!$this->adapter->isFullJoinRelation($name)) {
			// related models (if any) only contains primary key values
			return null;
		}
		
		$ar = $this->ar;
		
		if(!$ar->hasRelated($name)) {
			return null;
		}
		
		$relation = $ar->getMetaData()->relations[$name];
		
		$related = $ar->getRelated($name);
		
		if(!$related) {
			return null;
		}
		
		if(!is_array($related)) {
			$related = array($related);
		}
		
		return new ArrayModelIterator($this->adapter->adapterOf($name), $related);
	}
	
	function save(ErrorBag $errors) {
		
		$ok = $this->ar->save();
		
		if(!$ok) {
			foreach($this->ar->getErrors() as $attribute => $errors) {
				$context->addErrors($attribute, $errors);
			}
		}
		
		return $ok;
	}
	
}
