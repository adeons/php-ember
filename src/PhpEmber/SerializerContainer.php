<?php
namespace PhpEmber;

/**
 * Contains serializer objects for each adapter/type key and derivates model
 * serialization calls to a corresponding one.
 */
class SerializerContainer implements SerializerInterface
{

    /**
     *
     * @var SerializerInterface[]
     */
    private $serializers = array();

    /**
     *
     * @var array
     */
    private $transformMap = array(
        AttributeType::BOOLEAN_TYPE => 'PhpEmber\\Transforms\\Boolean',
        AttributeType::INTEGER_TYPE => 'PhpEmber\\Transforms\\Integer',
        AttributeType::FLOAT_TYPE => 'PhpEmber\\Transforms\\Float',
        AttributeType::STRING_TYPE => 'PhpEmber\\Transforms\\String',
        AttributeType::DATE_TYPE => 'PhpEmber\\Transforms\\Date'
    );

    /**
     *
     * @param string $typeKey
     * @return null|SerializerInterface
     */
    public function get($typeKey)
    {
        return isset($this->serializers[$typeKey]) ?
            $this->serializers[$typeKey] : null;
    }

    /**
     *
     * @param AdapterInterface $adapter
     * @return SerializerInterface
     */
    public function getFor($adapter)
    {
        $typeKey = $adapter->getTypeKey();

        if (isset($this->serializers[$typeKey])) {
            $serializer = $this->serializers[$typeKey];

        } else {

            $serializer = $this->create($adapter);
            $this->serializers[$typeKey] = $serializer;
        }

        return $serializer;
    }

    /**
     *
     * @param string $typeKey
     * @param SerializerInterface $serializer
     */
    public function set($typeKey, $serializer)
    {
        $this->serializers[$typeKey] = $serializer;
    }

    /**
     *
     * @param AdapterInterface $adapter
     * @return Serializer
     */
    protected function create($adapter)
    {
        $serializer = new Serializer($adapter);

        $this->makeTransforms($adapter, $serializer);

        $oneDelegate = array($this, 'serialize');
        $manyDelegate = array($this, 'serializeMany');

        foreach ($adapter->getAttributes() as $attribute) {

            $type = $attribute->getType();

            if ($type != AttributeType::BELONGS_TO
                && $type != AttributeType::HAS_MANY) {

                // not a relation
                continue;
            }

            $name = $attribute->getName();

            if ($type == AttributeType::BELONGS_TO) {
                $serializer->setRelationCallback($name, $oneDelegate);

            } else {
                $serializer->setRelationCallback($name, $manyDelegate);
            }
        }

        return $serializer;
    }

    /**
     *
     * @param AdapterInterface $adapter
     * @param Serializer $serializer
     */
    protected function makeTransforms($adapter, $serializer)
    {
        foreach ($adapter->getAttributes() as $attribute) {

            $type = $attribute->getType();

            if (!isset($this->transformMap[$type])) {
                continue;
            }

            $className = $this->transformMap[$type];

            $serializer->setTransform($attribute->getName(), new $className());
        }
    }

    /**
     * Calls the matching serializer for the given adapter.
     *
     * @param array $options Options array. Passed as-is to the resolved
     * serializer.
     */
    public function serialize($adapter, $model, $output, $options = array())
    {
        $this->getFor($adapter)->serialize(
            $adapter, $model, $output, $options);
    }

    /**
     * Serializes the given models using a matching serializer for an adapter.
     *
     * @param array $options Options array. Passed as-is to the resolved
     * serializer for every model.
     */
    public function serializeMany($adapter, $models, $output, $options = array())
    {
        $serializer = $this->getFor($adapter);

        foreach ($models as $model) {
            $serializer->serialize($adapter, $model, $output, $options);
        }
    }

}
