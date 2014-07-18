<?php
namespace PhpEmber\Tests;

/**
 * Adapter stub.
 * Used to test adapter attribute instrospection.
 */
class NullAdapter implements \PhpEmber\AdapterInterface
{

    /**
     *
     * @var string
     */
    private $typeKey;

    /**
     *
     * @var string
     */
    private $idName;

    /**
     *
     * @var \PhpEmber\AttributeInterface[]
     */
    private $attributes = array();

    /**
     * Constructor.
     *
     * @param string $typeKey
     * @param string $idName
     */
    public function __construct($typeKey, $idName = 'id')
    {
        $this->typeKey = $typeKey;
        $this->idName = $idName;
    }

    public function getTypeKey()
    {
        return $this->typeKey;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getId()
    {
        return $this->attributes[$this->idName];
    }

    public function find($id)
    {
        return null;
    }

    public function findMany($ids)
    {
        return array();
    }

    public function findAll($options = array())
    {
        return array(array(), 0);
    }

    public function create()
    {
        return new \stdClass();
    }

    public function save($model)
    {
    }

    public function remove($id)
    {
        return false;
    }

    /**
     *
     * @param \PhpEmber\AttributeInterface $attribute
     * @return \PhpEmber\AttributeInterface
     */
    public function addAttribute(\PhpEmber\AttributeInterface $attribute)
    {
        $this->attributes[$attribute->getName()] = $attribute;
        return $attribute;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @return \PhpEmber\Tests\FixtureAttribute
     */
    public function attr($name, $value = null, $type = null)
    {
        $attribute = new ConstantAttribute($name, $value, $type);
        $this->attributes[$name] = $attribute;

        return $attribute;
    }

}
