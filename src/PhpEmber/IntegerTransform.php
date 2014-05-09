<?php
namespace PhpEmber;

class IntegerTransform extends Transform {
	
	function fromPayload($payload) {
		return array(intval($payload), array());
	}
	
	function toPayload($value) {
		return intval($value);
	}
	
}
