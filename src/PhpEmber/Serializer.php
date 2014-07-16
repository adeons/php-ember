<?php
namespace PhpEmber;

/**
 * Maps attribute values using transforms.
 * @see TransformInterface
 */
class Serializer implements SerializerInterface
{

    /**
     *
     * @var TransformInterface[]
     */
    private $transforms = array();

    /**
     *
     * @var array
     */
    private $relationCallbacks = array();

    /**
     *
     * @param string $attribute
     * @return null|TransformInterface
     */
    public function getTransform($attribute)
    {
        return isset($this->transforms[$attribute]) ?
            $this->transforms[$attribute] : null;
    }

    /**
     *
     * @param string $attribute
     * @param TransformInterface $transform
     */
    public function setTransform($attribute, $transform)
    {
        $this->transforms[$attribute] = $transform;
    }

    /**
     *
     * @param string $attribute
     * @return callable
     */
    public function getRelationCallback($attribute)
    {
        return isset($this->relationCallbacks[$attribute]) ?
            $this->relationCallbacks[$attribute] : null;
    }

    /**
     *
     * @param string $attribute
     * @param callable $transform
     */
    public function setRelationCallback($attribute, $callback)
    {
        $this->relationCallbacks[$attribute] = $callback;
    }

    /**
     * Transforms each attribute value from the model and writes the result
     * in the output.
     *
     * Attributes without a corresponding transform will be written as-is.
     *
     * @param AdapterInterface $adapter Adapter used to read model attributes.
     * @param object $model The model to serialize.
     * @param ResponseWriterInterface $output Result output.
     * @param array $options Options array.
     *
     * This is used to customize what and how each attribute is serialized.
     * Each key should be a string corresponding to an attribute name, and
     * its value can be any of the following:
     * <ul>
     * <li>A nested array used as options for the corresponding transform.</li>
     * <li>A boolean false value will prevent the attribute from being
     * serialized.</li>
     * <li>A boolean true value is interpreted as if it were an empty
     * array.</li>
     * <li>Null values are ignored (as if they were not in the array), and
     * the attribute will be serialized normally.</li>
     * </ul>
     *
     * <p>An attribute will be serialized even if no options are found for it.</p>
     *
     * <p>Also, if the attribute is a relation, and it has an option value
     * (except null) the serializer will try to get its related model or models
     * and call a corresponding relation callback.</p>
     */
    public function serialize($adapter, $model, $output, $options = array())
    {
        $typeKey = $adapter->getTypeKey();
        $id = $adapter->getId()->get($model);

        if (!$output->lock($typeKey, $id)) {
            // already serialized
            return;
        }

        $payload = array();

        foreach ($adapter->getAttributes() as $name => $attribute) {

            if (!$attribute->isReadable()) {
                // attribute not readable
                continue;
            }

            // find transform options

            if (isset($options[$name])) {
                $hasOptions = true;
                $attributeOptions = $options[$name];

                if ($attributeOptions === false) {
                    // false skips this attribute
                    continue;
                }

                if ($attributeOptions === true) {
                    // a true value is used as an alias for array()
                    $attributeOptions = array();
                }

            } else {
                $hasOptions = false;
                $attributeOptions = array();
            }

            // serialize value using the transform (if any) and attach the
            // result in the payload

            $value = $attribute->get($model);

            if (isset($this->transforms[$name])) {

                $value = $this->transforms[$name]->serialize($value, $attributeOptions);
            }

            $payload[$name] = $value;

            if ($hasOptions && $value && isset($this->relationCallbacks[$name])) {

                // the attribute has a corresponding relation callback and
                // an option value: get related model/models and call
                // the callback

                $related = $attribute->getRelated($model);

                if($related)
                {
                    call_user_func($this->relationCallbacks[$name],
                       $attribute->getRelatedAdapter(), $related,
                       $output, $attributeOptions);
                }
            }

        }

        $output->write($typeKey, $id, $payload);
    }

}
