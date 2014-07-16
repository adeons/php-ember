<?php
namespace PhpEmber\Yii;

class BelongsToAttribute implements \PhpEmber\AttributeInterface
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

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function get($model)
    {
        return $model->getAttribute($this->relation->foreignKey);
    }

    public function set($model, $value)
    {
        $model->setAttribute($this->relation->foreignKey, $value);
    }

    public function getRelated($model)
    {
        if (!$model->hasRelated($this->relation->name)) {
            return null;
        }

        return $model->getRelated($this->relation->name);
    }
}
