<?php
namespace PhpEmber\Tests;

use PhpEmber\MemoryResponseWriter;

class MemoryResponseWriterTest extends \PHPUnit_Framework_TestCase
{

    public function testGetPrimaryType()
    {
        $output = new MemoryResponseWriter('fruit');

        $this->assertEquals('fruit', $output->getPrimaryType());
    }

    public function testGetMeta()
    {
        $meta = array('total' => 10);

        $output = new MemoryResponseWriter('fruit', null, $meta);

        $this->assertEquals($meta, $output->getMeta());
    }

    public function testLock()
    {
        $output = new MemoryResponseWriter('fruit');

        $this->assertTrue($output->lock('fruit', 'orange'));
    }

    public function testWriteAndLock()
    {
        $output = new MemoryResponseWriter('fruit');
        $output->write('fruit', 'orange', array('id' => 'orange'));

        $this->assertFalse($output->lock('fruit', 'orange'));
    }

}
