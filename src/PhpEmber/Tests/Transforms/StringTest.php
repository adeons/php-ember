<?php
namespace PhpEmber\Tests\Transforms;

use PhpEmber\Transforms\String;

class StringTest extends \PHPUnit_Framework_TestCase
{

    public function testSerializeNull()
    {
        $transform = new String;

        $this->assertNull($transform->serialize(null));
    }

    public function testSerializeNullAsEmpty()
    {
        $transform = new String(true);

        $this->assertSame('', $transform->serialize(null));
    }

    public function testSerialize()
    {
        $transform = new String;

        $this->assertEquals(
            'Hello World!', $transform->serialize('Hello World!'));
    }

}
