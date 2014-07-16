<?php
namespace PhpEmber\Tests;

use PhpEmber\JsonApiResponse;

class JsonApiResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $response = new JsonApiResponse($serializer);

        $this->assertSame($serializer, $response->getSerializer());

    }

    public function testBindOne()
    {
        $orange = new \stdClass;

        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $response = new JsonApiResponse($serializer);
        $response->bindOne($fruits, $orange);

        $this->assertSame($fruits, $response->getAdapter());
        $this->assertSame($orange, $response->getData());
    }

    public function testBindMany()
    {
        $models = array(new \stdClass);

        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $response = new JsonApiResponse($serializer);
        $response->bindMany($fruits, $models);

        $this->assertSame($fruits, $response->getAdapter());
        $this->assertSame($models, $response->getData());
    }

    public function testSendContentPrintsResponse()
    {
        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $fruits->expects($this->atLeastOnce())
            ->method('getTypeKey')
            ->will($this->returnValue('fruit'));

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $response = new JsonApiResponse($serializer);
        $response->bindMany($fruits, array());

        ob_start();
        $response->sendContent();

        $this->assertNotEmpty(ob_get_clean());
    }

    public function testSendContentCallsSerializer()
    {
        $options = array('option' => 'foo-bar');

        $orange = new \stdClass;

        $fruits = $this->getMock('\\PhpEmber\\AdapterInterface');

        $fruits->expects($this->atLeastOnce())
            ->method('getTypeKey')
            ->will($this->returnValue('fruit'));

        $serializer = $this->getMock('\\PhpEmber\\SerializerInterface');

        $serializer->expects($this->once())
            ->method('serialize')
            ->with(
                $this->identicalTo($fruits), $this->identicalTo($orange),
                $this->isInstanceOf('\\PhpEmber\\ResponseWriterInterface'),
                $this->equalTo($options)
            );

        $response = new JsonApiResponse($serializer);
        $response->setOptions($options);
        $response->bindOne($fruits, $orange);

        ob_start();
        $response->sendContent();
        ob_end_clean();
    }

}
