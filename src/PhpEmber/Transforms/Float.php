<?php
namespace PhpEmber\Transforms;

class Float implements \PhpEmber\TransformInterface
{

    public function serialize($value, $options = array())
    {
        return floatval($value);
    }
}
