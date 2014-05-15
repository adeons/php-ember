<?php
namespace PhpEmber\Yii;

use PhpEmber\Adapter;
use PhpEmber\AdapterContainer;
use PhpEmber\AttributeInfo;
use PhpEmber\AttributeType;

class ActiveAdapter implements Adapter {
	
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
		
		// the framework parses it as integer; reinterpret as boolean
		'tinyint(1)' => AttributeType::BOOLEAN_TYPE
	);
	
	public $criteria = array();
	
	private $typeKey;
	
	/**
	 * @var AdapterContainer
	 */
	private $container;
	
	/**
	 * @var string
	 */
	private $className;
	
	/**
	 * @var CActiveRecord
	 */
	private $finder;
	
	private $enabledRelations = array();
	private $baseJoin = array();
	
	/**
	 * @var AttributeInfo[]
	 */
	private $attributes = array();
	
	function __construct($typeKey, AdapterContainer $container = null, $className = null) {
		$this->typeKey = $typeKey;
		$this->container = $container;
		
		if(!$className) {
			$className = ucfirst($typeKey);
		}
		
		$this->className = $className;
		$this->finder = \CActiveRecord::model($className);
		
		$this->makeColumnAttributes();
	}
	
	function getContainer() {
		return $this->container;
	}
	
	function getTypeKey() {
		return $this->typeKey;
	}
	
	function getClassName() {
		return $this->className;
	}
	
	function getFinder() {
		return $this->finder;
	}
		
	function canSetId() {
		
		$table = $this->finder->getTableSchema();
		return !$table->columns[$table->primaryKey]->autoIncrement;
	}
	
	function isFullJoinRelation($name) {
		return isset($this->enabledRelations[$name]) && $this->enabledRelations[$name];
	}
	
	function getAttributeNames() {
		return array_keys($this->attributes);
	}
	
	function getAttributeInfo($name) {
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}
	
	function addAttribute(AttributeInfo $info) {
		$this->attributes[$info->name] = $info;
	}
	
	/**
	 * @param string $name
	 * @param boolean $fullJoin
	 * @return AttributeInfo
	 */
	function enableRelation($name, $fullJoin = false) {
		
		$meta = $this->finder->getMetaData();
		
		$info = $this->createRelationAttribute($meta->relations[$name], $fullJoin);
		
		$this->addAttribute($info);
		
		return $info;
	}
	
	function adapterOf($name) {
		
		if(!$this->container) {
			return null;
		}
		
		$info = $this->getAttributeInfo($name);
		
		if(!$info) {
			return null;
		}
		
		return $this->container->getAdapter($info->relatedType);
	}
	
	function readAttribute($model, $name) {
		$meta = $model->getMetaData();
		
		if($meta->hasRelation($name)) {
			
			$relation = $meta->relations[$name];
			
			if($relation instanceof \CBelongsToRelation) {
				return $model->getAttribute($relation->foreignKey);
			}
			
			$related = $model->getRelated($name);
			
			if($relation instanceof \CStatRelation) {
				return $related;
			}
			
			if(is_array($related)) {
				
				return array_map(function($relatedModel) {
					return $relatedModel->getPrimaryKey();
					
				}, $related);
				
			} else {
				
				return $related ? $related->getPrimaryKey() : null;
			}
		}
		
		return $model->$name;
	}
	
	function writeAttribute($model, $name, $value) {
		$meta = $model->getMetaData();
		
		if($meta->hasRelation($name)) {
			
			$relation = $meta->relations[$name];
			
			if($relation instanceof \CBelongsToRelation) {
				$model->setAttribute($relation->foreignKey, $value);
			}
			
			// TODO: support other relations types
			return;
		}
		
		$model->setAttribute($name, $value);
	}
	
	function find($id) {
		
		$model = $this->finder
			->with($this->baseJoin)
			->findByPk($id, $this->criteria);
		
		if(!$model) {
			return null;
		}
		
		return new ActiveModelProxy($this, $model);
	}
	
	function findMany(array $ids) {
		
		$models = $this->finder
			->with($this->baseJoin)
			->findAllByPk($ids, $this->criteria);
		
		return new ArrayModelIterator($this, $models);
	}
	
	function findAll($query, array $options) {
		
		$criteria = new \CDbCriteria;
		$criteria->mergeWith($this->criteria);
		
		$this->applyOptions($criteria, $options);
		$this->applyQuery($criteria, $query);
		
		$models = $this->finder
			->with($this->baseJoin)
			->findAll($criteria);
		
		$count = count($models);
		
		if($criteria->limit != -1 && $count >= $criteria->limit) {
			
			// only count the total if the query has limit,
			// or if the result set has the same length as the limit
			$total = intval($this->finder->count($criteria));
			
		} else {
			
			$total = $count;
		}
		
		return array(new ArrayModelIterator($this, $models), $total);
	}
	
	function remove($id) {
		$model = $this->finder->findByPk($id);
		return $model ? $model->delete() : false;
	}
	
	function create() {
		$className = '\\' . $this->className;
		return new ActiveModelProxy($this, new $className);
	}
	
	protected function applyQuery(\CDbCriteria $criteria, $query) {
	}
	
	protected function applyOptions(\CDbCriteria $criteria, array $options) {
		
		if(isset($options['start'])) {
			$criteria->offset = $options['start'];
		}
		
		if(isset($options['count'])) {
			$criteria->limit = $options['count'];
		}
		
		if(isset($options['sort'])) {
			$this->applySortOptions($criteria, $options['sort']);
		}
	}
	
	/**
	 * @param CDbCriteria $criteria
	 * @param array $options
	 */
	protected function applySortOptions(\CDbCriteria $criteria, array $options) {
		
		$db = $this->finder->getDbConnection();
		$pieces = array();
		
		foreach($options as $option) {
			
			$attrName = $option['attribute'];
			
			$piece = $db->quoteColumnName('t.' . $attrName);
			
			if(isset($option['descending']) && $option['descending']) {
				$piece .= ' DESC';
			}
			
			$pieces[] = $piece;
		}
		
		$criteria->order = implode(', ', $pieces);
	}
	
	/**
	 * @param \CDbColumnSchema $column
	 * @return AttributeInfo
	 */
	function createColumnAttribute(\CDbColumnSchema $column) {
		$typeMap = self::$typeMap;
		
		if(isset($typeMap[$column->dbType])) {
			$type = $typeMap[$column->dbType];
			
		} elseif(isset($typeMap[$column->type])) {
			$type = $typeMap[$column->type];
			
		} else {
			$type = null;
		}
		
		$info = new AttributeInfo($column->name, $type);
		$info->writable = true;
		$info->required = !$column->allowNull;
		
		if($type == AttributeType::DATE_TYPE) {
			$info->dateFormat = 'Y-m-d H:i:s';
		}
		
		return $info;
	}
	
	function makeColumnAttributes() {
		
		foreach($this->finder->getMetaData()->columns as $name => $column) {
			
			if($column->isPrimaryKey || $column->isForeignKey) {
				continue;
			}
			
			$this->addAttribute($this->createColumnAttribute($column));
		}
	}
	
	/**
	 * @param CActiveRelation $relation
	 * @param boolean $fullJoin
	 * @return AttributeInfo
	 */
	function createRelationAttribute(\CActiveRelation $relation, $fullJoin = false) {
		$name = $relation->name;
		
		$this->enabledRelations[$name] = $fullJoin;
		
		$info = new AttributeInfo($name);
		
		if($relation instanceof \CStatRelation) {
			
			// TODO: guess type (integer for COUNT, etc)
			$info->type = AttributeType::FLOAT_TYPE;
			
			// Always add the stat subquery
			$this->baseJoin[] = $name;
			return $info;
		}
		
		// guess type key from class name
		$info->relatedType = lcfirst($relation->className);
		
		if($relation instanceof \CBelongsToRelation) {
			
			$info->type = AttributeType::BELONGS_TO;
			$info->writable = true;
			
			if($fullJoin) {
				// Only JOIN if related model data is requested
				$this->baseJoin[] = $name;
			}
			
			return $info;
		}
		
		// CHasManyRelation or CManyManyRelation
		$info->type = AttributeType::HAS_MANY;
		
		if($fullJoin) {
			
			// JOIN all related columns
			$this->baseJoin[] = $name;
			
		} else {
			
			// Only JOIN primary key
			
			$relatedFinder = \CActiveRecord::model($relation->className);
			
			$this->baseJoin[$name] = array(
				'select' => $relatedFinder->getTableSchema()->primaryKey
			);
		}
		
		return $info;
	}
	
}
