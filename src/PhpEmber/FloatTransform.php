<?php
namespace PhpEmber;

class FloatTransform extends Transform {
	
	function fromPayload($payload) {
		return array(floatval($payload), array());
	}
	
	function toPayload($value) {
		return floatval($value);
	}
	
}
