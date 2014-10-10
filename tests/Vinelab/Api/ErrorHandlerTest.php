<?php namespace Vinelab\Api\Tests;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Mockery as M;

class ErrorHandlerTest extends TestCase {

    public function setUp()
    {
        $this->request = new Request();
        $this->responder = new \Vinelab\Api\Responder($this->request);

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

        $error_handler = new \Vinelab\Api\ErrorHandler($this->responder);

        $result = $error_handler->handle($message)->getContent();

        assertEquals($result, $expected_response);

    }

    /**
     * @expectedException Vinelab\Api\ApiException
     */
    public function testErrorHandlerWithException()
    {
        $exception = new \Exception();

        $error_handler = new \Vinelab\Api\ErrorHandler($this->responder);

        $error_handler->handle($exception);
    }


}
