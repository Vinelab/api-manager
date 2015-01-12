<?php namespace Vinelab\Api\Tests;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */

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
        $error_handler = new \Vinelab\Api\ErrorHandler($this->responder);

        $expected = [
            'status' => 500,
            'error'  => [
                'code' => 0,
                'message' => $message,
            ]
        ];

        $result = $error_handler->handle($message)->original;

        $this->assertEquals($expected, $result);
    }

    public function testErrorHandlerWithExceptionAndMessage()
    {
        $exception = new \Exception('testing exception');
        $error_handler = new \Vinelab\Api\ErrorHandler($this->responder);
        $expected = [
            'status' => 500,
            'error'  => [
                'code' => 0,
                'message' => 'testing exception'
            ]
        ];

        $response = $error_handler->handle($exception)->original;

        $this->assertEquals($expected, $response);
    }

    public function testErrorHandlerWithExceptionAndMessageAndCode()
    {
        $exception = new \Exception('some exception', 1001);
        $error_handler = new \Vinelab\Api\ErrorHandler($this->responder);
        $expected = [
            'status' => 401,
            'error'  => [
                'code' => 1001,
                'message' => 'some exception'
            ]
        ];

        $response = $error_handler->handle($exception, $exception->getCode(), 401)->original;

        $this->assertEquals($expected, $response);
        $this->assertInternalType('int', $response['status']);
    }

}
