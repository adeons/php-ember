<?php
namespace PhpEmber\Tests;

use PhpEmber\Serializer;
use PhpEmber\AttributeInterface;

class SerializerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Creates a response writer which will accept and discard any input.
     */
    protected function makeNullWriter()
    {
        $output = $this->getMock('\\PhpEmber\\ResponseWriterInterface');

        $output->expects($this->atLeastOnce())
            ->method('lock')
            ->will($this->returnValue(true));

        return $output;
    }

    /**
     * Creates an attribute which will always return a constant value.
     * @param mixed $value
     */
    protected function makeAttribute($value)
    {
        $attribute = $this->getMock('\\PhpEmber\\AttributeInterface');

        $attribute->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue(true));

        $attribute->expects($this->any())
            ->method('get')
            ->will($this->returnValue($value));

        return $attribute;
    }

    /**
     * Creates an adapter mock.
     * @param string $typeKey
     * @param AttributeInterface[] $attributes
     * @param string $idName
     */
    protected function makeAdapter($typeKey, $attributes, $idName = 'id')
    {
        $adapter = $this->getMock('\\PhpEmber\\AdapterInterface');

        $adapter->expects($this->any())
            ->method('getTypeKey')
            ->will($this->returnValue($typeKey));

        $adapter->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $adapter->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($attributes[$idName]));

        return $adapter;
    }

    public function testSerializeWillLock()
    {
        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange')
        ));

        $output = $this->getMock('\\PhpEmber\\ResponseWriterInterface');

        $output->expects($this->once())
            ->method('lock')
            ->with($this->equalTo('fruit'), $this->equalTo('orange'));

        $serializer = new Serializer($fruits);
        $serializer->serialize($fruits, new \stdClass, $output);
    }

    public function testSerializeSkipsNotReadable()
    {
        $notReadable = $this->getMock('\\PhpEmber\\AttributeInterface');

        $notReadable->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue(false));

        $notReadable->expects($this->never())
            ->method('get');

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'notReadable' => $notReadable
        ));

        $transform = $this->getMock('\\PhpEmber\\TransformInterface');

        $transform->expects($this->never())
            ->method('serialize');

        $serializer = new Serializer($fruits);
        $serializer->setTransform('notReadable', $transform);

        $serializer->serialize($fruits,
            new \stdClass, $this->makeNullWriter());
    }

    public function testSerializeSkipsByOption()
    {
        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange')
        ));

        $idTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $idTransform->expects($this->never())
            ->method('serialize');

        $serializer = new Serializer($fruits);
        $serializer->setTransform('id', $idTransform);

        $serializer->serialize($fruits, new \stdClass,
            $this->makeNullWriter(), array('id' => false));
    }

    public function testSerializeCallsGet()
    {
        $orange = new \stdClass;

        $colorAttribute = $this->getMock('\\PhpEmber\\AttributeInterface');

        $colorAttribute->expects($this->any())
            ->method('isReadable')
            ->will($this->returnValue(true));

        $colorAttribute->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($orange));

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'color' => $colorAttribute
        ));

        $colorTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $serializer = new Serializer($fruits);
        $serializer->setTransform('color', $colorTransform);

        $serializer->serialize($fruits,
            $orange, $this->makeNullWriter());
    }

    public function testSerializeCallsTransform()
    {
        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange')
        ));

        $idTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $idTransform->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo('orange'), $this->isType('array'));

        $serializer = new Serializer($fruits);
        $serializer->setTransform('id', $idTransform);

        $serializer->serialize($fruits,
            new \stdClass, $this->makeNullWriter());
    }

    public function testSerializeCallsTransformWithOptions()
    {
        $options = array('option' => 'foo-bar');

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange')
        ));

        $idTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $idTransform->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo('orange'), $this->equalTo($options));

        $serializer = new Serializer($fruits);
        $serializer->setTransform('id', $idTransform);

        $serializer->serialize($fruits, new \stdClass,
            $this->makeNullWriter(), array('id' => $options));
    }

    public function testSerializeCallsWrite()
    {
        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange')
        ));

        $output = $this->makeNullWriter();

        $output->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo('fruit'), $this->equalTo('orange'),
                $this->isType('array')
            );

        $serializer = new Serializer($fruits);

        $serializer->setTransform(
            'id', $this->getMock('\\PhpEmber\\TransformInterface'));

        $serializer->serialize($fruits, new \stdClass, $output);
    }

    public function testSerializeSkipsRelationWithoutCallback()
    {
        $genusAttribute = $this->makeAttribute('citrus');

        $genusAttribute->expects($this->never())
            ->method('getRelated');

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'genus' => $genusAttribute
        ));

        $genusTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $serializer = new Serializer($fruits);
        $serializer->setTransform('genus', $genusTransform);

        $serializer->serialize($fruits, new \stdClass,
            $this->makeNullWriter(), array('genus' => true));
    }

    public function testSerializeSkipsRelationWithoutOption()
    {
        $genusAttribute = $this->makeAttribute('citrus');

        $genusAttribute->expects($this->never())
            ->method('getRelated');

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'genus' => $genusAttribute
        ));

        $genusTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->never())
            ->method('callback');

        $serializer = new Serializer($fruits);
        $serializer->setTransform('genus', $genusTransform);
        $serializer->setRelationCallback('genus', array($dummy, 'callback'));

        $serializer->serialize($fruits, new \stdClass,
            $this->makeNullWriter());
    }

    public function testSerializeSkipsEmptyRelation()
    {
        $genusAttribute = $this->makeAttribute('citrus');

        $genusAttribute->expects($this->never())
            ->method('getRelated')
            ->will($this->returnValue(null));

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'genus' => $genusAttribute
        ));

        $genusTransform = $this->getMock('\\PhpEmber\\TransformInterface');

        $output = $this->makeNullWriter();

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->never())
            ->method('callback');

        $serializer = new Serializer($fruits);
        $serializer->setTransform('genus', $genusTransform);
        $serializer->setRelationCallback('genus', array($dummy, 'callback'));

        $serializer->serialize($fruits, new \stdClass,
            $output, array('genus' => true));
    }

    public function testSerializeCallsGetRelated()
    {
        $orange = new \stdClass;

        $genusAttribute = $this->makeAttribute('citrus');

        $genusAttribute->expects($this->atLeastOnce())
            ->method('getRelated')
            ->with($this->identicalTo($orange));

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'genus' => $genusAttribute
        ));

        $output = $this->makeNullWriter();

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->any())
            ->method('callback');

        $serializer = new Serializer($fruits);
        $serializer->setRelationCallback('genus', array($dummy, 'callback'));

        $serializer->serialize($fruits, $orange,
            $output, array('genus' => true));
    }

    public function testSerializeCallsRelationCallback()
    {
        $citrus = new \stdClass;
        $orange = new \stdClass;

        $genera = $this->makeAdapter('genus', array(
            'id' => $this->makeAttribute('citrus')
        ));

        $genusAttribute = $this->makeAttribute('citrus');

        $genusAttribute->expects($this->atLeastOnce())
            ->method('getRelatedAdapter')
            ->will($this->returnValue($genera));

        $genusAttribute->expects($this->atLeastOnce())
            ->method('getRelated')
            ->will($this->returnValue($citrus));

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'genus' => $genusAttribute
        ));

        $output = $this->makeNullWriter();

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->once())
            ->method('callback')
            ->with(
                $this->identicalTo($genera), $this->identicalTo($citrus),
                $this->identicalTo($output), $this->isType('array')
            );

        $serializer = new Serializer($fruits);
        $serializer->setRelationCallback('genus', array($dummy, 'callback'));

        $serializer->serialize($fruits, $orange,
            $output, array('genus' => true));
    }

    public function testSerializeCallsRelationCallbackWithOptions()
    {
        $options = array('option' => 'foo-bar');

        $citrus = new \stdClass;
        $orange = new \stdClass;

        $genera = $this->makeAdapter('genus', array(
            'id' => $this->makeAttribute('citrus')
        ));

        $genusAttribute = $this->makeAttribute('citrus');

        $genusAttribute->expects($this->atLeastOnce())
            ->method('getRelatedAdapter')
            ->will($this->returnValue($genera));

        $genusAttribute->expects($this->atLeastOnce())
            ->method('getRelated')
            ->will($this->returnValue($citrus));

        $fruits = $this->makeAdapter('fruit', array(
            'id' => $this->makeAttribute('orange'),
            'genus' => $genusAttribute
        ));

        $output = $this->makeNullWriter();

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->once())
            ->method('callback')
            ->with(
                $this->identicalTo($genera), $this->identicalTo($citrus),
                $this->identicalTo($output), $this->equalTo($options)
            );

        $serializer = new Serializer($fruits);
        $serializer->setRelationCallback('genus', array($dummy, 'callback'));

        $serializer->serialize($fruits, $orange,
            $output, array('genus' => $options));
    }

}
