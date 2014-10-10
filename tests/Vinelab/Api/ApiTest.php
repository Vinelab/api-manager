<?php namespace Vinelab\Api\Tests;

use Illuminate\Support\Collection;
use Mockery as M;
use Vinelab\Api\ErrorHandler;
use Vinelab\Api\Responder;
use Vinelab\Api\Api;
use Vinelab\Api\ApiException;
use Illuminate\Http\Request;
use Vinelab\Api\ResponseHandler;

class ApiTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request();
        $this->response = new Responder($this->request);
        $this->response_handler = new ResponseHandler($this->response);
        $this->error_handler = new ErrorHandler($this->response);

        $this->config_reader = M::mock('Illuminate\Config\Repository');
        $this->config_reader->shouldReceive('get')->with('api::api')
            ->andReturn(['mappers' => 'Lib\Api\Mappers\\']);

        $this->response_handler = new Api($this->response_handler, $this->error_handler, $this->config_reader);

    }

    public function tearDown()
    {
        M::close();
        parent::tearDown();
    }

    public function testRespondWithCollection()
    {
        $mapper = new DummyMapper();

        $m_post_1 = M::mock('Post');
        $m_post_1->shouldReceive('setAttribute')->passthru();
        $m_post_1->id = 1;
        $m_post_1->text = 'Enim provident tempore reiciendis quit qui.';
        $m_post_1->active = true;
        $m_post_1->shouldReceive('getAttribute')->passthru();


        $m_post_2 = M::mock('Post');
        $m_post_2->shouldReceive('setAttribute')->passthru();
        $m_post_2->id = 2;
        $m_post_2->text = 'Provident tempore enim reiciendis quitqui.';
        $m_post_2->active = false;
        $m_post_2->shouldReceive('getAttribute')->passthru();

        $data = new Collection([$m_post_1, $m_post_2]);

        $result = $this->response_handler->respond($mapper, $data)->getContent();

        $expected_response = "{\"status\":200,\"data\":[{\"id\":1,\"text\":\"Enim provident tempore reiciendis quit qui.\",\"active\":true},{\"id\":2,\"text\":\"Provident tempore enim reiciendis quitqui.\",\"active\":false}]}";

        assertEquals($result, $expected_response);
    }

    public function testRespondWithCollectionAndPagination()
    {
        $mapper = new DummyMapper();

        $m_post_1 = M::mock('Post');
        $m_post_1->shouldReceive('setAttribute')->passthru();
        $m_post_1->id = 1;
        $m_post_1->text = 'Enim provident tempore reiciendis quit qui.';
        $m_post_1->active = true;
        $m_post_1->shouldReceive('getAttribute')->passthru();

        $m_post_2 = M::mock('Post');
        $m_post_2->shouldReceive('setAttribute')->passthru();
        $m_post_2->id = 2;
        $m_post_2->text = 'Provident tempore enim reiciendis quitqui.';
        $m_post_2->active = false;
        $m_post_2->shouldReceive('getAttribute')->passthru();

        $data = new Collection([$m_post_1, $m_post_2]);

        $result = $this->response_handler->respond($mapper, $data, 100, 1)->getContent();

        $expected_response = "{\"status\":200,\"total\":100,\"page\":1,\"data\":[{\"id\":1,\"text\":\"Enim provident tempore reiciendis quit qui.\",\"active\":true},{\"id\":2,\"text\":\"Provident tempore enim reiciendis quitqui.\",\"active\":false}]}";

        assertEquals($result, $expected_response);
    }


    public function testRespondWithPaginator()
    {
        $m_post_1 = M::mock('Post');
        $m_post_1->shouldReceive('setAttribute')->passthru();
        $m_post_1->id = 1;
        $m_post_1->text = 'Enim provident tempore reiciendis quit qui.';
        $m_post_1->active = true;
        $m_post_1->shouldReceive('getAttribute')->passthru();

        $m_post_2 = M::mock('Post');
        $m_post_2->shouldReceive('setAttribute')->passthru();
        $m_post_2->id = 2;
        $m_post_2->text = 'Provident tempore enim reiciendis quitqui.';
        $m_post_2->active = false;
        $m_post_2->shouldReceive('getAttribute')->passthru();

        $data = ([$m_post_1, $m_post_2]);

        $m_paginate = M::mock('Illuminate\Pagination\Paginator');
        $m_paginate->shouldReceive('getTotal')->once()->andReturn(100);
        $m_paginate->shouldReceive('getCurrentPage')->once()->andReturn(1);
        $m_paginate->shouldReceive('all')->andReturn($data);

        $mapper = new DummyMapper();

        $expected_response = "{\"status\":200,\"total\":100,\"page\":1,\"data\":[{\"id\":1,\"text\":\"Enim provident tempore reiciendis quit qui.\",\"active\":true},{\"id\":2,\"text\":\"Provident tempore enim reiciendis quitqui.\",\"active\":false}]}";

        $result = $this->response_handler->respond($mapper, $m_paginate)->getContent();

        assertEquals($result, $expected_response);
    }


}



class DummyMapper {

    use \Vinelab\Api\MappableTrait;

    public function map($data)
    {
        return [
            'id'     => (int) $data->id,
            'text'   => $data->text,
            'active' => (boolean) $data->active
        ];
    }
}