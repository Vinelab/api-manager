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

        $serialized_data = "O:8:\"stdClass\":2:{s:6:\"status\";i:200;s:4:\"data\";O:8:\"stdClass\":3:{s:2:\"id\";i:1;s:4:\"text\";s:43:\"Enim provident tempore reiciendis quit qui.\";s:6:\"active\";b:1;}}";

        $responder = new \Vinelab\Api\Responder();

        $result = serialize($responder->respond($data)->getData());

        assertEquals($result, $serialized_data);

    }

}
