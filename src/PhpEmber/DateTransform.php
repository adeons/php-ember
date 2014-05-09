<?php
namespace PhpEmber;

class DateTransform extends Transform {
	
	function fromPayload($payload) {
		
		$errors = array();
		$value = \DateTime::createFromFormat(\DateTime::RFC2822, $payload);
		
		if(!$value) {
			$errors[] = 'Not a valid date';
		}
		
		return array($value, $errors);
	}
	
	function toPayload($value) {
		
		$date = is_object($value) ? $value : new \DateTime($value);
		
		return $date->format(\DateTime::RFC2822);
	}
	
}
