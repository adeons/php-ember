<?php
namespace PhpEmber;

class AttributeInfo {
	
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var boolean
	 */
	public $required = false;
	
	/**
	 * @var string
	 */
	public $relatedType;
	
	/**
	 * @var string
	 */
	public $type;
	
	/**
	 * @var boolean
	 */
	public $sortable = false;
	
	/**
	 * @var boolean
	 */
	public $readable = true;
	
	/**
	 * @var boolean
	 */
	public $writable = false;
	
	/**
	 * @var string
	 */
	public $dateFormat = null;
	
	function __construct($name, $type = null, $relatedType = null) {
		$this->name = $name;
		$this->relatedType = $relatedType;
		$this->type = $type;
	}
	
}
