<?php
namespace PhpEmber;

interface Adapter {
	
	/**
	 * @return AdapterContainer
	 */
	function getContainer();
	
	/**
	 * @return string
	 */
	function getTypeKey();
	
	/**
	 * @return array
	 */
	function getAttributeNames();
	
	/**
	 * @param string $name
	 * @return AttributeInfo
	 */
	function getAttributeInfo($name);
	
	/**
	 * @param string $id
	 * @return ModelProxy
	 */
	function find($id);
	
	/**
	 * @param array $ids
	 * @return ModelIterator
	 */
	function findMany(array $ids);
	
	/**
	 * @param mixed $query
	 * @param array $options
	 * @return array
	 */
	function findAll($query, array $options);
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	function remove($id);
	
	/**
	 * @return ModelProxy
	 */
	function create();
	
}
