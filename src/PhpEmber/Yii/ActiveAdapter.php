<?php
namespace PhpEmber\Yii;

use PhpEmber\AttributeType;
use PhpEmber\PropertyAttribute;

/**
 * Adapter for Yii ActiveRecord.
 */
class ActiveAdapter extends \PhpEmber\Adapter
{

    /**
     *
     * @var array
     */
    private static $typeMap = array(
        'boolean' => AttributeType::BOOLEAN_TYPE,
        'integer' => AttributeType::INTEGER_TYPE,
        'float' => AttributeType::FLOAT_TYPE,
        'double' => AttributeType::FLOAT_TYPE,
        'decimal' => AttributeType::FLOAT_TYPE,
        'string' => AttributeType::STRING_TYPE,
        'text' => AttributeType::STRING_TYPE,
        'date' => AttributeType::DATE_TYPE,
        'datetime' => AttributeType::DATE_TYPE,
        'timestamp' => AttributeType::DATE_TYPE,

        // Yii parses it as integer; reinterpret as boolean
        'tinyint(1)' => AttributeType::BOOLEAN_TYPE
    );

    /**
     *
     * @var string
     */
    private $typeKey;

    /**
     *
     * @var \CActiveRecord
     */
    private $finder;

    /**
     *
     * @var ActiveAdapterContainer
     */
    private $container;

    /**
     *
     * @var \PhpEmber\AttributeInterface
     */
    private $id;

    /**
     *
     * @var array
     */
    private $baseJoin = array();

    /**
     *
     * @param string $typeKey
     * @param \CActiveRecord $finder
     * @param ActiveAdapterContainer $container
     */
    public function __construct($typeKey, $finder, ActiveAdapterContainer $container = null)
    {
        $this->typeKey = $typeKey;
        $this->finder = $finder;
        $this->container = $container;

        $pk = $finder->getTableSchema()->primaryKey;

        if (is_array($pk)) {

            throw new \LogicException(sprintf(
                '"%s" has a composite primary key which are not supported yet.',
                get_class($finder)));
        }

        $this->id = $this->mapColumn($pk);
    }

    /**
     *
     * @return ActiveAdapterContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getTypeKey()
    {
        return $this->typeKey;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return \CActiveRecord
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     *
     * @param string $name
     * @param bool $fullJoin
     * @return \PhpEmber\AttributeInterface
     */
    public function enableRelation($name, $fullJoin = false)
    {
        $meta = $this->finder->getMetaData();

        $info = $this->createRelationAttribute(
            $meta->relations[$name], $fullJoin);

        return $this->addAttribute($info);
    }

    public function find($id)
    {
        return $this->finder
            ->with($this->baseJoin)
            ->findByPk($id);
    }

    public function findMany($ids)
    {
        return $this->finder
            ->with($this->baseJoin)
            ->findAllByPk($ids);
    }

    public function findAll($options = array())
    {
        $criteria = new \CDbCriteria;

        $this->applyOptions($criteria, $options);

        if (isset($options['criteria'])) {
            $criteria->mergeWith($options['criteria']);
        }

        $models = $this->finder
            ->with($this->baseJoin)
            ->findAll($criteria);

        $total = intval($this->finder->count($criteria));

        return array($models, $total);
    }

    public function remove($id)
    {
        $model = $this->finder->findByPk($id);
        return $model ? $model->delete() : false;
    }

    public function create()
    {
        $className = get_class($this->finder);
        return new $className;
    }

    public function save($model)
    {
        $model->save();
    }

    /**
     *
     * @param \CDbCriteria $criteria
     * @param array $options
     */
    protected function applyOptions($criteria, $options)
    {
        if (isset($options['start'])) {
            $criteria->offset = $options['start'];
        }

        if (isset($options['count'])) {
            $criteria->limit = $options['count'];
        }

        if (isset($options['sort'])) {
            $this->applySortOptions($criteria, $options['sort']);
        }
    }

    /**
     *
     * @param \CDbCriteria $criteria
     * @param array $options
     */
    protected function applySortOptions($criteria, $options)
    {
        $db = $this->finder->getDbConnection();
        $pieces = array();

        foreach ($options as $option) {

            $attrName = $option['attribute'];

            $piece = $db->quoteColumnName('t.' . $attrName);

            if (isset($option['descending']) && $option['descending']) {
                $piece .= ' DESC';
            }

            $pieces[] = $piece;
        }

        $criteria->order = implode(', ', $pieces);
    }

    /**
     *
     * @param string $name
     * @return \PhpEmber\AttributeInterface
     */
    public function mapColumn($name)
    {
        $meta = $this->finder->getMetaData();

        return $this->addAttribute(
            $this->createColumnAttribute($meta->columns[$name]));
    }

    /**
     *
     * @param array $exclude
     */
    public function mapColumns($exclude = array())
    {
        foreach ($this->finder->getMetaData()->columns as $name => $column) {

            if ($column->isForeignKey || in_array($name, $exclude)) {
                continue;
            }

            $this->addAttribute($this->createColumnAttribute($column));
        }
    }

    /**
     *
     * @param \CDbColumnSchema $column
     * @return \PhpEmber\AttributeInterface
     */
    protected function createColumnAttribute($column)
    {
        $typeMap = self::$typeMap;

        if (isset($typeMap[$column->dbType])) {
            $type = $typeMap[$column->dbType];

        } elseif (isset($typeMap[$column->type])) {
            $type = $typeMap[$column->type];

        } else {
            $type = null;
        }

        $attribute = new PropertyAttribute($column->name, $type);

        if ($type == AttributeType::DATE_TYPE) {

            // convert string values into DateTime objects

            $attribute->setGetter(function ($model, $attribute) {

                $value = $model->{$attribute->getName()};

                if ($value) {
                    return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                }

                return null;
            });

            $attribute->setSetter(function ($model, $value, $attribute) {

                $model->{$attribute->getName()} = $value ?
                    $value->format('Y-m-d H:i:s') : null;
            });
        }

        return $attribute;
    }

    /**
     *
     * @param \CActiveRelation $relation
     * @param bool $fullJoin
     * @return \PhpEmber\AttributeInterface
     */
    protected function createRelationAttribute($relation, $fullJoin = false)
    {
        $name = $relation->name;

        $this->enabledRelations[$name] = $fullJoin;

        if ($relation instanceof \CStatRelation) {

            // Always add the stat subquery
            $this->baseJoin[] = $name;

            // TODO: guess type (integer for COUNT, etc)
            return new PropertyAttribute($name, AttributeType::FLOAT_TYPE);
        }

        if ($relation instanceof \CBelongsToRelation) {

            if ($fullJoin) {
                // Only JOIN if related model data is requested
                $this->baseJoin[] = $name;
            }

            $foreignKey = $this->finder->getTableSchema()->columns[$relation->foreignKey];

            return new BelongsToAttribute(
                $this->container, $relation, !$foreignKey->allowNull);
        }

        if ($relation instanceof \CHasOneRelation) {

            if ($fullJoin) {
                $this->baseJoin[] = $name;
            }

            return new HasOneAttribute($this->container, $relation);
        }

        // CHasManyRelation or CManyManyRelation

        if ($fullJoin) {

            // JOIN all related columns
            $this->baseJoin[] = $name;
        } else {

            // Only JOIN primary key

            $relatedFinder = \CActiveRecord::model($relation->className);

            $this->baseJoin[$name] = array(
                'select' => $relatedFinder->getTableSchema()->primaryKey
            );
        }

        return new ToManyAttribute($this->container, $relation);
    }
}
