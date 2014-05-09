<?php
namespace PhpEmber;

class BooleanTransform extends Transform {
	
	function fromPayload($payload) {
		return array((bool) $payload, array());
	}
	
	function toPayload($value) {
		return (bool) $value;
	}
	
}
