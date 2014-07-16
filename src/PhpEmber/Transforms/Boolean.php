<?php
namespace PhpEmber\Transforms;

class Boolean implements \PhpEmber\TransformInterface
{

    public function serialize($value, $options = array())
    {
        return (bool) $value;
    }
}
