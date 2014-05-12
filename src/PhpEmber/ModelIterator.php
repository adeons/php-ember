<?php
namespace PhpEmber;

interface ModelIterator extends \Countable, \Iterator {
	
	/**
	 * @return Adapter
	 */
	function getAdapter();
}
