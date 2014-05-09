<?php
namespace PhpEmber;

class SerializerContext {
	
	private $model;
	
	public $errors;
	
	public $payload;
	
	function __construct(ModelProxy $model) {
		$this->model = $model;
		$this->errors = new ErrorBag;
	}
	
	function getModel() {
		return $this->model;
	}
	
}
