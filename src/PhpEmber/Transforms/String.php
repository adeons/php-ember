<?php
namespace PhpEmber\Transforms;

class String implements \PhpEmber\TransformInterface
{

    /**
     *
     * @var bool
     */
    private $treatNullAsEmpty;

    /**
     * Constructor.
     *
     * @param bool $treatNullAsEmpty If true, null values will be serialized as an empty string.
     */
    public function __construct($treatNullAsEmpty = false)
    {
        $this->treatNullAsEmpty = $treatNullAsEmpty;
    }

    public function serialize($value, $options = array())
    {
        if ($value === null && !$this->treatNullAsEmpty) {
            // strval will convert null into ""
            return null;
        }

        return strval($value);
    }

}
