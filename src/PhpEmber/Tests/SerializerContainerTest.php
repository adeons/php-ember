<?php
namespace PhpEmber\Tests;

use PhpEmber\SerializerContainer;

class SerializerContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetReturnsNullIfNotFound()
    {
        $container = new SerializerContainer;

        $this->assertNull($container->get('fruit'));
    }

    public function testSet()
    {
        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $container = new SerializerContainer;
        $container->set('fruit', $serializer);

        $this->assertSame($serializer, $container->get('fruit'));
    }

    public function testGetFor()
    {
        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $fruits->expects($this->atLeastOnce())
            ->method('getTypeKey')
            ->will($this->returnValue('fruit'));

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $container = new SerializerContainer;
        $container->set('fruit', $serializer);

        $this->assertSame($serializer, $container->getFor($fruits));
    }

    public function testGetForCallsCreateIfNotFound()
    {
        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $fruits->expects($this->atLeastOnce())
            ->method('getTypeKey')
            ->will($this->returnValue('fruit'));

        $container = $this->getMock('\\PhpEmber\\SerializerContainer',
            array('create'));

        $container->expects($this->once())
            ->method('create')
            ->with($this->identicalTo($fruits));

        $container->getFor($fruits);
    }

    public function testSerializeCallsSerializer()
    {
        $options = array('option' => 'foo-bar');

        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $fruits->expects($this->any())
            ->method('getTypeKey')
            ->will($this->returnValue('fruit'));

        $orange = new \stdClass;

        $output = $this->getMock('\\PhpEmber\\ResponseWriterInterface');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $serializer->expects($this->once())
            ->method('serialize')
            ->with(
                $this->identicalTo($fruits), $this->identicalTo($orange),
                $this->identicalTo($output), $this->equalTo($options)
            );

        $container = new SerializerContainer;
        $container->set('fruit', $serializer);

        $container->serialize($fruits, $orange, $output, $options);
    }

}
