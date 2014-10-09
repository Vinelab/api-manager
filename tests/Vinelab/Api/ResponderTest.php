<?php namespace Vinelab\Api\Tests;

use Illuminate\Support\Collection;
use Mockery as M;

class ResponderTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        M::close();
        parent::tearDown();
    }

    public function testResponder()
    {

       $data = [
          'id' => 1,
          'text' => 'Enim provident tempore reiciendis quit qui.',
          'active' => true
        ];

        $expected_response = "{\"status\":200,\"data\":{\"id\":1,\"text\":\"Enim provident tempore reiciendis quit qui.\",\"active\":true}}";

        $responder = new \Vinelab\Api\Responder();

        $result = $responder->respond($data)->getContent();

        assertEquals($result, $expected_response);

    }

}
