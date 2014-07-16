<?php
namespace PhpEmber\Tests\Transforms;

use PhpEmber\Transforms\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{

    public function testSerializeNull()
    {
        $transform = new Date;

        $this->assertNull($transform->serialize(null));
    }

    public function testSerializeDateCallsFormat()
    {
        $date = $this->getMock('\\DateTime');

        $date->expects($this->atLeastOnce())
            ->method('format')
            ->with($this->equalTo(DATE_ISO8601));

        $transform = new Date;
        $transform->serialize($date);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSerializeStringRequiresFormat()
    {
        $transform = new Date;
        $transform->serialize('foo');
    }

    public function testSerializeString()
    {
        $transform = new Date('Y-m-d H:i:s', 'Y-m-d\TH:i:s');

        $this->assertEquals('2014-04-01T12:10:20',
            $transform->serialize('2014-4-1 12:10:20'));
    }

}
