<?php
namespace PhpEmber;

/**
 * Describes an attribute and allows to get and set its value in any model.
 */
interface AttributeInterface
{

    /**
     * Returns the name of this attribute.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the type of this attribute.
     *
     * @return null|string
     * @see AttributeType
     */
    public function getType();

    /**
     * If this attribute is a relation, returns the type key
     * of the related adapter; otherwise null is returned.
     *
     * @return null|string
     */
    public function getRelatedType();

    /**
     * If this attribute is a relation, returns the related adapter;
     * otherwise null is returned.
     *
     * @return null|AdapterInterface
     */
    public function getRelatedAdapter();

    /**
     * Returns whether the value of this attribute can be obtained
     * from a model.
     *
     * @return bool
     */
    public function isReadable();

    /**
     * Returns whether the value of this attribute can be changed.
     *
     * @return bool
     */
    public function isWritable();

    /**
     * Returns the value from a model.
     *
     * @param object $model
     * @return mixed
     */
    public function get($model);

    /**
     * Changes the value in a model.
     *
     * @param object $model
     * @param mixed $value
     */
    public function set($model, $value);

    /**
     * Returns related model or models, if available.
     *
     * @param object $model
     * @return null|array|object|\Traversable
     */
    public function getRelated($model);
}
