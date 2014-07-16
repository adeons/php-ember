<?php
namespace PhpEmber\Yii;

class ToManyAttribute implements \PhpEmber\AttributeInterface
{

    /**
     *
     * @var ActiveAdapterContainer
     */
    private $container;

    private $relation;

    /**
     *
     * @var ActiveAdapter
     */
    private $relatedAdapter;

    /**
     *
     * @param ActiveAdapterContainer $container
     * @param \CHasManyRelation|\CManyManyRelation $relation
     */
    public function __construct($container, $relation)
    {
        $this->container = $container;
        $this->relation = $relation;
    }

    public function getName()
    {
        return $this->relation->name;
    }

    public function getRelatedType()
    {
        return $this->container->typeKeyOfClass($this->relation->className);
    }

    public function getRelatedAdapter()
    {
        if (!$this->relatedAdapter) {

            $this->relatedAdapter = $this->container->get(
                $this->getRelatedType());
        }

        return $this->relatedAdapter;
    }

    public function getType()
    {
        return \PhpEmber\AttributeType::HAS_MANY;
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
        $ids = array();

        foreach ($related as $relatedModel) {
            $ids[] = $relatedModel->getPrimaryKey();
        }

        return $ids;
    }

    public function set($model, $value)
    {
    }

    public function getRelated($model)
    {
        if (!$model->hasRelated($this->relation->name)) {
            return array();
        }

        return $model->getRelated($this->relation->name);
    }
}
