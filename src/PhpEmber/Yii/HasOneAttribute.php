<?php
namespace PhpEmber\Yii;

class HasOneAttribute implements \PhpEmber\AttributeInterface
{

    private $resolver;

    private $relation;

    public function __construct($resolver, $relation)
    {
        $this->resolver = $resolver;
        $this->relation = $relation;
    }

    public function getName()
    {
        return $this->relation->name;
    }

    public function getRelatedType()
    {
        return $this->resolver->typeKeyOfClass($this->relation->className);
    }

    public function getRelatedAdapter()
    {
        return $this->resolver->get($this->getRelatedType());
    }

    public function getType()
    {
        return \PhpEmber\AttributeType::BELONGS_TO;
    }

    public function isRequired()
    {
        return false;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return false;
    }

    public function get($model)
    {
        $related = $model->getRelated($this->relation->name);

        if (!$related) {
            return null;
        }

        return $related->getPrimaryKey();
    }

    public function set($model, $value)
    {
    }

    public function getRelated($model)
    {
        if (!$model->hasRelated($this->relation->name)) {
            return null;
        }

        return $model->getRelated($this->relation->name);
    }
}
