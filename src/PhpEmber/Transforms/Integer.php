<?php
namespace PhpEmber\Transforms;

class Integer implements \PhpEmber\TransformInterface
{

    public function serialize($value, $options = array())
    {
        return intval($value);
    }
}
