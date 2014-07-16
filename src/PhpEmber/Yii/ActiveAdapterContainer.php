<?php
namespace PhpEmber\Yii;

/**
 * ActiveRecord adapter container and factory.
 *
 * Adapters will be created on demand when first accessed.
 */
class ActiveAdapterContainer extends \CApplicationComponent implements \PhpEmber\AdapterResolverInterface
{

    /**
     *
     * @var array
     */
    private $activeClasses = array();

    /**
     *
     * @var ActiveAdapter
     */
    private $adapters = array();

    /**
     *
     * @var string
     */
    private $adapterSetUpMethod = 'jsonApiAdapter';

    /**
     *
     * @return array
     */
    public function getActiveClasses()
    {
        return $this->activeClasses;
    }

    /**
     * Registers ActiveRecord classes.
     *
     * @param array $activeClasses
     */
    public function setActiveClasses($activeClasses)
    {
        // clear previous registered type keys and created adapters
        $this->activeClasses = array();
        $this->adapters = array();

        foreach ($activeClasses as $typeKey => $className) {

            if (is_int($typeKey)) {
                $typeKey = null;
            }

            $this->addActiveClass($className, $typeKey);
        }
    }

    /**
     * Registers an ActiveRecord class.
     *
     * @param string $className
     * @param null|string $typeKey
     */
    public function addActiveClass($className, $typeKey = null)
    {
        if (!$typeKey) {
            // autogenerate type key by lowercasing first character
            $typeKey = lcfirst($className);
        }

        $this->activeClasses[$typeKey] = $className;
    }

    public function has($typeKey)
    {
        return isset($this->activeClasses[$typeKey]);
    }

    public function get($typeKey)
    {
        if (isset($this->adapters[$typeKey])) {

            // adapter was created before; return same instance
            return $this->adapters[$typeKey];
        }

        if (!isset($this->activeClasses[$typeKey])) {

            throw new \InvalidArgumentException(sprintf(
                'Adapter "%s" not found.', $typeKey));
        }

        // adapter instance not found, create one
        $adapter = $this->create($typeKey, $this->activeClasses[$typeKey]);

        // save instance so it can be reused later
        $this->adapters[$typeKey] = $adapter;

        return $adapter;
    }

    /**
     * Translates an ActiveRecord class name into a type key.
     *
     * @param string $className
     * @return string
     */
    public function typeKeyOfClass($className)
    {
        $typeKey = array_search($className, $this->activeClasses, true);

        if ($typeKey === false) {

            throw new \InvalidArgumentException(sprintf(
                'ActiveRecord class "%s" has no adapter.', $className));
        }

        return $typeKey;
    }

    /**
     * Creates and configurates a new adapter.
     *
     * @param string $typeKey
     * @param string $className
     * @return ActiveAdapter
     */
    protected function create($typeKey, $className)
    {
        $finder = \CActiveRecord::model($className);

        $adapter = new ActiveAdapter($typeKey, $finder, $this);

        if (method_exists($finder, $this->adapterSetUpMethod)) {

            // the class has an adapter configurator method
            $finder->{$this->adapterSetUpMethod}($adapter);

        } else {

            // no configuration method found; map all columns by default
            $adapter->mapColumns();
        }

        return $adapter;
    }

}
