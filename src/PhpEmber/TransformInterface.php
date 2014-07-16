<?php
namespace PhpEmber;

/**
 * Attribute serialization strategy.
 */
interface TransformInterface
{

    /**
     * Returns the formatted representation of a value.
     *
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function serialize($value, $options = array());
}
