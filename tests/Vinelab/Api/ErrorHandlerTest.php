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

        $expected_response = "{\"status\":500,\"error\":{\"message\":\"Something went wrong\",\"code\":0}}";

        $responder = new \Vinelab\Api\ErrorHandler();

        $result = $responder->handle($message)->getContent();

        assertEquals($result, $expected_response);

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
