<?php
namespace PhpEmber;

/**
 * Provides a simple common interface to access different implementations
 * of data collections.
 *
 * Each entry in the collection is called a model and is represented by a
 * object instance which can be of any class.
 *
 * This interface allows to load and save models from the collection
 * (which may be a persistent storage) and to introspect the attributes that
 * describe each model.
 */
interface AdapterInterface
{

    /**
     * Returns the name of the data type.
     *
     * @return string Name of the type in singular and camelCase format.
     */
    public function getTypeKey();

    /**
     * Returns an array of objects describing each attribute.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Returns the attribute used to uniquely identify each model
     * in the collection.
     *
     * @return AttributeInterface
     */
    public function getId();

    /**
     * Loads a single model by its identifier.
     *
     * @param string $id
     * @return null|object
     */
    public function find($id);

    /**
     * Loads multiple models by their identifier.
     *
     * @param array $ids
     * @return array|\Traversable
     */
    public function findMany($ids);

    /**
     * Filters and loads matching models.
     *
     * @param array $options
     * @return array
     */
    public function findAll($options = array());

    /**
     * Creates a new model and returns it.
     *
     * @return object
     */
    public function create();

    /**
     * Writes modified attribute values into persistent storage.
     *
     * @param object $model
     */
    public function save($model);

    /**
     * Removes a model from the collection by its identifier.
     *
     * @param string $id
     * @return bool
     */
    public function remove($id);
}
