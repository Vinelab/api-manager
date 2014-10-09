<?php namespace Vinelab\Api\Tests;

use Illuminate\Support\Collection;
use Mockery as M;

class ErrorHandlerTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        M::close();
        parent::tearDown();
    }

    public function testErrorHandlerWithMessage()
    {

        $message = "Something went wrong";

        $serialized_data = "O:8:\"stdClass\":2:{s:6:\"status\";i:500;s:5:\"error\";O:8:\"stdClass\":2:{s:7:\"message\";s:20:\"Something went wrong\";s:4:\"code\";i:0;}}";

        $responder = new \Vinelab\Api\ErrorHandler();

        $result = serialize($responder->handle($message)->getData());

        assertEquals($result, $serialized_data);

    }

    /**
     * @expectedException Vinelab\Api\ApiException
     */
    public function testErrorHandlerWithException()
    {

        $exception = new \Exception();

        $responder = new \Vinelab\Api\ErrorHandler();

        $responder->handle($exception);

    }


}
