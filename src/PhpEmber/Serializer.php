<?php
namespace PhpEmber;

interface Serializer {
	
	/**
	 * @param SerializerContext $context
	 */
	function encode(SerializerContext $context);
	
	
	/**
	 * @param SerializerContext $context
	 * @return boolean
	 */
	function decode(SerializerContext $context);
	
}
