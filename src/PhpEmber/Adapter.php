<?php
namespace PhpEmber;

/**
 * Adapter base class.
 */
abstract class Adapter implements AdapterInterface
{

    /**
     *
     * @var AttributeInterface[]
     */
    private $attributes = array();

    /**
     *
     * @return AdapterContainerInterface
     */
    public abstract function getContainer();

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     *
     * @param AttributeInterface $attribute
     * @return AttributeInterface
     */
    public function addAttribute($attribute)
    {
        $this->attributes[$attribute->getName()] = $attribute;
        return $attribute;
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @param string $relatedType
     * @return PropertyAttribute
     */
    public function attr($name, $type = null, $relatedType = null)
    {
        $attribute = new PropertyAttribute($name, $type);

        if ($relatedType) {
            $attribute->bindRelatedAdapter($this->getContainer(), $relatedType);
        }

        $this->attributes[$name] = $attribute;
        return $attribute;
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    public function removeAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            return false;
        }

        unset($this->attributes[$name]);
        return true;
    }

}
