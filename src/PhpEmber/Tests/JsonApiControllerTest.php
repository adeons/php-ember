<?php
namespace PhpEmber\Tests;

use PhpEmber\JsonApiController;
use Symfony\Component\HttpFoundation\Response;

class JsonApiControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $adapter = $this->getMock('\\PhpEmber\\AdapterInterface');
        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $controller = new JsonApiController($adapter, $serializer);

        $this->assertSame($adapter, $controller->getAdapter());
        $this->assertSame($serializer, $controller->getSerializer());
    }

    public function testOne()
    {
        $adapter = $this->getMock('\\PhpEmber\\AdapterInterface');
        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $model = new \stdClass;

        $controller = new JsonApiController($adapter, $serializer);
        $response = $controller->one($model);

        $this->assertInstanceOf('\\PhpEmber\\JsonApiResponse', $response);
        $this->assertSame($response->getAdapter(), $adapter);
        $this->assertSame($response->getSerializer(), $serializer);
        $this->assertSame($response->getData(), $model);
    }

    public function testMany()
    {
        $adapter = $this->getMock('\\PhpEmber\\AdapterInterface');
        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $models = array(new \stdClass);

        $controller = new JsonApiController($adapter, $serializer);
        $response = $controller->many($models);

        $this->assertInstanceOf('\\PhpEmber\\JsonApiResponse', $response);
        $this->assertSame($response->getAdapter(), $adapter);
        $this->assertSame($response->getSerializer(), $serializer);
        $this->assertSame($response->getData(), $models);
    }

    public function testParseSerializeOptionsWithStringIncludesAttribute()
    {
        $fruits = new NullAdapter('fruit');
        $fruits->attr('id');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $controller = new JsonApiController($fruits, $serializer);

        $options = $controller->parseSerializeOptions(array(
            'fields' => 'id'
        ));

        // note that null is valid too
        $this->assertArrayNotHasKey('id', $options);
    }

    public function testParseSerializeOptionsWithStringExcludesAttribute()
    {
        $fruits = new NullAdapter('fruit');
        $fruits->attr('id');
        $fruits->attr('color');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $controller = new JsonApiController($fruits, $serializer);

        $options = $controller->parseSerializeOptions(array(
            'fields' => 'id'
        ));

        // color not included in field list
        $this->assertArrayHasKey('color', $options);
        $this->assertFalse($options['color']);
    }

    public function testParseSerializeOptionsWithArrayExcludesAttribute()
    {
        $fruits = new NullAdapter('fruit');
        $fruits->attr('id');
        $fruits->attr('color');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $controller = new JsonApiController($fruits, $serializer);

        $options = $controller->parseSerializeOptions(array(
            'fields' => array('fruits' => 'id')
        ));

        // color not included in field list
        $this->assertArrayHasKey('color', $options);
        $this->assertFalse($options['color']);
    }

    public function testParseQueryOptionsWithInvalidStart()
    {
        $fruits = new NullAdapter('fruit');
        $fruits->attr('id');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $controller = new JsonApiController($fruits, $serializer);

        $options = $controller->parseQueryOptions(array(
            'page' => '-1'
        ));

        $this->assertInstanceOf('\\Symfony\Component\HttpFoundation\Response', $options);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $options->getStatusCode());
    }

    public function testParseQueryOptionsWithPage()
    {
        $fruits = new NullAdapter('fruit');
        $fruits->attr('id');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $controller = new JsonApiController($fruits, $serializer);

        $options = $controller->parseQueryOptions(array(
            'page' => '10'
        ));

        $this->assertGreaterThan(0, $options['start']);
    }

}
