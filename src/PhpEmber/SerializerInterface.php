<?php
namespace PhpEmber;

/**
 * Model serialization strategy.
 */
interface SerializerInterface
{

    /**
     * Writes the serialized representation of the given model.
     *
     * @param AdapterInterface $adapter Adapter which may be used to
     * introspect the model.
     * @param object $model The model to serialize.
     * @param ResponseWriterInterface $output Result output.
     * @param array $options Optional options array. This may be used by the
     * implementation to customize the result.
     */
    public function serialize($adapter, $model, $output, $options = array());

}
