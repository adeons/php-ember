<?php
namespace PhpEmber;

class ErrorBag {
	
	private $errors = array();
	
	function hasErrors() {
		return (bool) $this->errors;
	}
	
	function getErrors() {
		return $this->errors;
	}
	
	function addError($attribute, $error) {
		$this->errors[$attribute][] = $error;
	}
	
	function addErrors($attribute, array $errors) {
		
		$this->errors[$attribute] = isset($this->errors[$attribute]) ?
			array_merge($this->errors[$attribute], $errors) : $errors;
	}
	
	function clearErrors() {
		$this->errors = array();
	}
	
}
