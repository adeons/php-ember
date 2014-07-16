<?php
namespace PhpEmber;

class PropertyAttribute implements AttributeInterface
{

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var null|string
     */
    private $type;

    /**
     *
     * @var null|string
     */
    private $relatedType;

    /**
     *
     * @var null|AdapterResolverInterface
     */
    private $relatedContainer;

    /**
     *
     * @var null|AdapterInterface
     */
    private $relatedAdapter;

    /**
     *
     * @var boolean
     */
    private $readable = true;

    /**
     *
     * @var boolean
     */
    private $writable = true;

    /**
     *
     * @var null|string|callable
     */
    private $getter;

    /**
     *
     * @var null|string|callable
     */
    private $setter;

    /**
     *
     * @var null|callable
     */
    private $relatedGetter;

    /**
     * Constructor.
     *
     * @param string $name
     * @param null|string $type
     */
    public function __construct($name, $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRelatedType()
    {
        return $this->relatedType;
    }

    public function getRelatedAdapter()
    {
        if (!$this->relatedAdapter && $this->relatedType) {

            $this->relatedAdapter = $this->relatedContainer
                ->get($this->relatedType);

            if (!$this->relatedAdapter) {

                throw new \LogicException(sprintf(
                    'Relation "%s" requires "%s" adapter.',
                    $this->name, $this->relatedType));
            }
        }

        return $this->relatedAdapter;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    /**
     *
     * @param bool $readable
     * @return PropertyAttribute
     */
    public function setReadable($readable)
    {
        $this->readable = $readable;
        return $this;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    /**
     *
     * @param bool $writable
     * @return PropertyAttribute
     */
    public function setWritable($writable)
    {
        $this->writable = $writable;
        return $this;
    }

    /**
     *
     * @return null|string|callable
     */
    public function getGetter()
    {
        return $this->getter;
    }

    /**
     *
     * @param null|string|callable $getter
     * @return PropertyAttribute
     */
    function setGetter($getter)
    {
        $this->getter = $getter;
        return $this;
    }

    /**
     *
     * @return null|string|callable
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     *
     * @param null|string|callable $setter
     * @return PropertyAttribute
     */
    public function setSetter($setter)
    {
        $this->setter = $setter;
        return $this;
    }

    /**
     *
     * @return null|callable
     */
    public function getRelatedGetter()
    {
        return $this->relatedGetter;
    }

    /**
     *
     * @param null|callable $relatedGetter
     * @return PropertyAttribute
     */
    public function setRelatedGetter($relatedGetter)
    {
        $this->relatedGetter = $relatedGetter;
        return $this;
    }

    /**
     *
     * @param AdapterResolverInterface $relatedContainer
     * @param string $typeKey
     * @return PropertyAttribute
     */
    public function bindRelatedAdapter($relatedContainer, $relatedType)
    {
        $this->relatedContainer = $relatedContainer;
        $this->relatedType = $relatedType;

        return $this;
    }

    public function get($model)
    {
        if ($this->getter) {

            if (is_string($this->getter)) {
                return $model->{$this->getter}();
            }

            return call_user_func($this->getter, $model, $this);
        }

        return $model->{$this->name};
    }

    public function set($model, $value)
    {
        if ($this->setter) {

            if (is_string($this->setter)) {
                $model->{$this->setter}($value);

            } else {
                call_user_func($this->setter, $model, $value, $this);
            }

        } else {

            $model->{$this->name} = $value;
        }
    }

    public function getRelated($model)
    {
        if ($this->relatedGetter) {

            return call_user_func($this->relatedGetter, $model, $this);
        }

        return $this->type == AttributeType::HAS_MANY ? array() : null;
    }
}
