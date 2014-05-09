<?php
namespace PhpEmber;

class StringTransform extends Transform {
	
	function fromPayload($payload) {
		return array(strval($payload), array());
	}
	
	function toPayload($value) {
		return strval($value);
	}
	
}
