<?php
namespace PhpEmber\Tests\Yii;

use PhpEmber\Yii\ActiveAdapter;

class ActiveAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \CDbTableSchema
     */
    private $table;

    /**
     *
     * @var \CActiveRecordMetaData
     */
    private $meta;

    /**
     *
     * @var \CActiveRecord
     */
    private $finder;

    /**
     * Creates a column schema mock.
     * @param string $name
     * @param string $type
     * @param bool $isPrimaryKey
     * @param bool $isForeignKey
     */
    protected function makeColumnSchema(
        $name, $type = 'string',
        $isPrimaryKey = false, $isForeignKey = false
    ) {
        $column = $this->getMock('\\CDbColumnSchema');
        $column->name = $name;
        $column->type = $type;
        $column->primaryKey = $isPrimaryKey;
        $column->isForeignKey = $isForeignKey;

        return $column;
    }

    protected function setUp()
    {
        if (!defined('YII_INCLUDE_FILE')) {
            $this->markTestSkipped('YII_INCLUDE_FILE not defined.');
            return;
        }

        require_once YII_INCLUDE_FILE;

        spl_autoload_register(array('YiiBase','autoload'));

        // mock an ActiveRecord global instance (which CActiveRecord::model() returns),
        // without calling its constructor to avoid application singleton and/or database setup.

        // Note: CActiveRecord file contains the CActiveRecordMetaData class, so its
        // created first to force autoloading.

        $this->finder = $this->getMock('\\CActiveRecord',
            array(), array(), '', false);

        // mock table

        $this->table = $this->getMock('\\CDbTableSchema',
            array(), array(), '', false);

        $this->table->primaryKey = 'id';

        $this->table->columns = array(
            'id' => $this->makeColumnSchema('id', true)
        );

        // mock metadata

        $this->meta = $this->getMock('\\CActiveRecordMetaData',
            array(), array(), '', false);

        $this->meta->tableSchema = $this->table;
        $this->meta->columns = $this->table->columns;

        // mock global instance

        $this->finder->expects($this->any())
            ->method('getMetaData')
            ->will($this->returnValue($this->meta));

        $this->finder->expects($this->any())
            ->method('getTableSchema')
            ->will($this->returnValue($this->table));

        $this->finder->expects($this->any())
            ->method('with')
            ->will($this->returnSelf());
    }

    protected function tearDown()
    {
        // unregister autoloader (causes conflicts)
        spl_autoload_unregister(array('YiiBase','autoload'));
    }

    public function testFind()
    {
        $orange = $this->getMock('\\CActiveRecord',
            array(), array(), '', false);

        $this->finder->expects($this->once())
            ->method('findByPk')
            ->with($this->equalTo('orange'))
            ->will($this->returnValue($orange));

        $fruits = new ActiveAdapter('fruit', $this->finder);

        $this->assertSame($orange, $fruits->find('orange'));
    }

    public function testFindMany()
    {
        $orange = $this->getMock('\\CActiveRecord',
            array(), array(), '', false);

        $banana = $this->getMock('\\CActiveRecord',
            array(), array(), '', false);

        $ids = array('orange', 'banana');
        $models = array($orange, $banana);

        $this->finder->expects($this->once())
            ->method('findAllByPk')
            ->with($this->equalTo($ids))
            ->will($this->returnValue($models));

        $fruits = new ActiveAdapter('fruit', $this->finder);

        $this->assertSame($models, $fruits->findMany($ids));
    }

    public function testRemove()
    {
        $orange = $this->getMock('\\CActiveRecord',
            array(), array(), '', false);

        $orange->expects($this->once())
            ->method('delete');

        $this->finder->expects($this->once())
            ->method('findByPk')
            ->with($this->equalTo('orange'))
            ->will($this->returnValue($orange));

        $fruits = new ActiveAdapter('fruit', $this->finder);
        $fruits->remove('orange');
    }

    public function testSave()
    {
        $orange = $this->getMock('\\CActiveRecord',
            array(), array(), '', false);

        $orange->expects($this->once())
            ->method('save');

        $fruits = new ActiveAdapter('fruit', $this->finder);
        $fruits->save($orange);
    }

}
