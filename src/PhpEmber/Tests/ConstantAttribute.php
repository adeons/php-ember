<?php
namespace PhpEmber\Tests;

/**
 * Attribute stub.
 * Read only; always returns a constant value.
 */
class ConstantAttribute implements \PhpEmber\AttributeInterface
{

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $type;

    /**
     *
     * @var unknown
     */
    private $value;

    /**
     * Constructor.
     *
     * @param string $name
     * @param mixed $value
     * @param string $type
     */
    public function __construct($name, $value = null, $type = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getRelatedType()
    {
        return null;
    }

    public function getRelatedAdapter()
    {
        return null;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return false;
    }

    public function get($model)
    {
        return $this->value;
    }

    public function set($model, $value)
    {
    }

    public function getRelated($model)
    {
        return null;
    }

}
