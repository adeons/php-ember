<?php
namespace PhpEmber;

interface ModelProxy {
	
	/**
	 * @return Adapter
	 */
	function getAdapter();
	
	/**
	 * @return boolean
	 */
	function canSetId();
	
	/**
	 * @return string
	 */
	function getId();
	
	/**
	 * @param string $id
	 */
	function setId($id);
	
	/**
	 * @param string $name
	 * @return mixed
	 */
	function getAttribute($name);
	
	/**
	 * @param string $name
	 * @param mixed $value
	 */
	function setAttribute($name, $value);
	
	/**
	 * @param string $name
	 * @return ModelIterator
	 */
	function getRelated($name);
	
	/**
	 * @param ErrorBag $errors
	 * @return boolean
	 */
	function save(ErrorBag $errors);
	
}
