<?php namespace Vinelab\Api\Tests;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use Vinelab\Api\Responder;
use Illuminate\Http\Request;

use Mockery as M;

class ResponseHandlerTest extends TestCase {

    public function setUp()
    {

        $this->request = new Request();
        $this->responder = new Responder($this->request);

        parent::setUp();
    }

    public function tearDown()
    {
        M::close();
        parent::tearDown();
    }

    public function testResponderWithArray()
    {

       $data = [
          'id' => 1,
          'text' => 'Enim provident tempore reiciendis quit qui.',
          'active' => true
        ];

        $expected_response = "{\"status\":200,\"data\":{\"id\":1,\"text\":\"Enim provident tempore reiciendis quit qui.\",\"active\":true}}";

        $response_handler = new \Vinelab\Api\ResponseHandler($this->responder);

        $result = $response_handler->respond($data)->getContent();

        assertEquals($result, $expected_response);
    }

}
