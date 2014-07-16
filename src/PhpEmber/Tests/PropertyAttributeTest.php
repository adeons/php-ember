<?php
namespace PhpEmber\Tests;

use PhpEmber\PropertyAttribute;

class PropertyAttributeTest extends \PHPUnit_Framework_TestCase
{

    public function testPropertyGetter()
    {
        $fruit = new \stdClass;
        $fruit->id = 'orange';

        $idAttribute = new PropertyAttribute('id');

        $this->assertSame($fruit->id, $idAttribute->get($fruit));
    }

    public function testPropertySetter()
    {
        $fruit = new \stdClass;
        $id = 'orange';

        $idAttribute = new PropertyAttribute('id');
        $idAttribute->set($fruit, $id);

        $this->assertSame($id, $fruit->id);
    }

    public function testMethodGetter()
    {
        $id = 'orange';

        $fruit = $this->getMock('stdClass', array('getName'));

        $fruit->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($id));

        $idAttribute = new PropertyAttribute('id');
        $idAttribute->setGetter('getName');

        $this->assertSame($id, $idAttribute->get($fruit));
    }

    public function testMethodSetter()
    {
        $id = 'orange';

        $fruit = $this->getMock('stdClass', array('setName'));

        $fruit->expects($this->once())
            ->method('setName')
            ->with($this->equalTo($id));

        $idAttribute = new PropertyAttribute('id');
        $idAttribute->setSetter('setName');

        $idAttribute->set($fruit, $id);
    }

    public function testCallableGetter()
    {
        $fruit = new \stdClass;
        $id = 'orange';

        $idAttribute = new PropertyAttribute('id');

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->once())
            ->method('callback')
            ->with($this->identicalTo($fruit), $this->identicalTo($idAttribute))
            ->will($this->returnValue($id));

        $idAttribute->setGetter(array($dummy, 'callback'));

        $this->assertSame($id, $idAttribute->get($fruit));
    }

    public function testCallableSetter()
    {
        $fruit = new \stdClass;
        $id = 'orange';

        $idAttribute = new PropertyAttribute('id');

        $dummy = $this->getMock('\\stdClass', array('callback'));

        $dummy->expects($this->once())
            ->method('callback')
            ->with(
                $this->identicalTo($fruit), $this->equalTo($id),
                $this->identicalTo($idAttribute)
            );

        $idAttribute->setSetter(array($dummy, 'callback'));
        $idAttribute->set($fruit, $id);
    }
}
