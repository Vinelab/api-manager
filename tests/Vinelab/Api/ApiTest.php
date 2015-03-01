<?php namespace Vinelab\Api\Tests;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */

use Illuminate\Support\Collection;
use Mockery as M;
use Vinelab\Api\ErrorHandler;
use Vinelab\Api\Responder;
use Vinelab\Api\Api;
use Illuminate\Http\Request;
use Vinelab\Api\ResponseHandler;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class ApiTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request();
        $this->response = new Responder($this->request);
        $this->response_handler = new ResponseHandler($this->response);
        $this->error_handler = new ErrorHandler($this->response);

        $this->config_reader = M::mock('Illuminate\Config\Repository');
        $this->config_reader->shouldReceive('get')->with('api')
            ->andReturn(['mappers' => 'Lib\Api\Mappers\\', 'limit' => '50']);

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


        $expected = [
            'status' => 200,
            'total' => 100,
            'page' => 1,
            'per_page' => 5,
            'data'   => [
                [
                    'id'     => 1,
                    'text'   => 'Enim provident tempore reiciendis quit qui.',
                    'active' => true
                ],
                [
                    'id'     => 2,
                    'text'   => 'Provident tempore enim reiciendis quitqui.',
                    'active' => false
                ]
            ]
        ];

        $response = M::mock('Illuminate\Http\Response');
        $response->original = $expected;
        $response->shouldReceive('header')->once()->with('Content-Type', 'text/html')->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $result = $this->response_handler->respond($mapper, $data, 100, 1, 5)->original;

        $this->assertEquals($result, $expected);
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


        $expected = [
            'status' => 200,
            'total' => 100,
            'page' => 1,
            'per_page' => 5,
            'data' => [
                [
                    'id'     => 1,
                    'text'   => 'Enim provident tempore reiciendis quit qui.',
                    'active' => true
                ],
                [
                    'id'     => 2,
                    'text'   => 'Provident tempore enim reiciendis quitqui.',
                    'active' => false
                ]
            ]
        ];

        $response = M::mock('Illuminate\Http\Response');
        $response->original = $expected;
        $response->shouldReceive('header')->once()->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $result = $this->response_handler->respond($mapper, $data, 100, 1, 5)->original;

        assertEquals($result, $expected);
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
        $m_paginate->shouldReceive('getTotal')->once()->andReturn(300);
        $m_paginate->shouldReceive('getCurrentPage')->once()->andReturn(1);
        $m_paginate->shouldReceive('getPerPage')->once()->andReturn(25);
        $m_paginate->shouldReceive('all')->andReturn($data);

        $mapper = new DummyMapper();

        $expected = [
            'status' => 200,
            'total' =>  300,
            'page' => 1,
            'per_page' => 25,
            'data' => [
                [
                    'id'     => 1,
                    'text'   => 'Enim provident tempore reiciendis quit qui.',
                    'active' => true
                ],
                [
                    'id'     => 2,
                    'text'   => 'Provident tempore enim reiciendis quitqui.',
                    'active' => false
                ]
            ]
        ];

        $response = M::mock('Illuminate\Http\Response');
        $response->original = $expected;
        $response->shouldReceive('header')->once()->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $result = $this->response_handler->respond($mapper, $m_paginate)->original;

        assertEquals($result, $expected);
    }


    public function testRespondWithModel()
    {
        $m_post_1 = M::mock('Post');
        $m_post_1->shouldReceive('setAttribute')->passthru();
        $m_post_1->id = 1;
        $m_post_1->text = 'Enim provident tempore reiciendis quit qui.';
        $m_post_1->active = true;
        $m_post_1->shouldReceive('getAttribute')->passthru();

        $mapper = new DummyMapper();
        $expected = [
            'status' => 200,
            'data'   => [
                'id'     => 1,
                'text'   => 'Enim provident tempore reiciendis quit qui.',
                'active' => true
            ]
        ];

        $response = M::mock('Illuminate\Http\Response');
        $response->original = $expected;
        $response->shouldReceive('header')->once()->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $result = $this->response_handler->respond($mapper, $m_post_1)->original;

        $this->assertEquals($result, $expected);
    }

    public function testGettingContentOnly()
    {
        $mPost = M::mock('Post');
        $mPost->shouldReceive('setAttribute')->passthru();
        $mPost->id = 1;
        $mPost->text = 'Enim provident tempore reiciendis quit qui.';
        $mPost->active = true;
        $mPost->shouldReceive('getAttribute')->passthru();

        $mapper = new DummyMapper();

        $expected = [
            'status' => 200,
            'data'   => [
                'id'     => 1,
                'text'   => 'Enim provident tempore reiciendis quit qui.',
                'active' => true
            ]
        ];

        $response = M::mock('Illuminate\Http\Response');
        $response->shouldReceive('getOriginalContent')->once()->andReturn($expected);
        $response->shouldReceive('header')->once()->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $result = $this->response_handler->content($mapper, $mPost);

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testCallingMapperAsArrayWithStringName()
    {
        $mPost = M::mock('Post');
        $mPost->shouldReceive('setAttribute')->passthru();
        $mPost->id = 1;
        $mPost->text = 'Enim provident tempore reiciendis quit qui.';
        $mPost->active = true;
        $mPost->shouldReceive('getAttribute')->passthru();


        $expected = [
            'status' => 200,
            'data'   => [
                'id'     => 1,
                'text'   => 'Enim provident tempore reiciendis quit qui.',
                'active' => true
            ]
        ];

        $this->response_handler->setMapperNamespace('Vinelab\Api\Tests\\');
        $mapper = ['DummyMapper', 'mapDatThing'];

        $mMapper = M::mock('Vinelab\Api\Tests\DummyMapper');
        $mMapper->shouldReceive('mapDatThing')->once()->with($mPost)->andReturn($expected['data']);

        App::shouldReceive('make')->once()->with('Vinelab\Api\Tests\DummyMapper')
            ->andReturn($mMapper);

        $response = M::mock('Illuminate\Http\Response');
        $response->shouldReceive('getOriginalContent')->once()->andReturn($expected);
        $response->shouldReceive('header')->once()->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $result = $this->response_handler->content($mapper, $mPost);

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testCallingMapperAsArrayWithInstance()
    {
        $mPost = M::mock('Post');
        $mPost->shouldReceive('setAttribute')->passthru();
        $mPost->id = 1;
        $mPost->text = 'Enim provident tempore reiciendis quit qui.';
        $mPost->active = true;
        $mPost->shouldReceive('getAttribute')->passthru();

        $expected = [
            'status' => 200,
            'data'   => [
                'id'     => 1,
                'text'   => 'Enim provident tempore reiciendis quit qui.',
                'active' => true
            ]
        ];

        $response = M::mock('Illuminate\Http\Response');
        $response->shouldReceive('getOriginalContent')->once()->andReturn($expected);
        $response->shouldReceive('header')->once()->andReturn($response);

        Response::shouldReceive('make')->once()
            ->with($expected, 200, [], 0)
            ->andReturn($response);

        $this->response_handler->setMapperNamespace('Vinelab\Api\Tests\\');

        $mapper = [new DummyMapper(), 'mapDatThing'];

        $result = $this->response_handler->content($mapper, $mPost);

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * Regression for an issue that occurred when passing associative
     * arrays as data, the data translator had considered it to be an array of multiple
     * records (multiple key-value arrays or models) while it shouldn't.
     */
    public function testRespondingWithKeyValuArray()
    {
        $data = ['key' => 'value', 'another' => 'value'];

        $expected = ['tinkered' => 'data'];

        $mapper = M::mock('Vinelab\Api\DummyMapper');
        $mapper->shouldReceive('mapArray')->once()->with($data)->andReturn($expected);

        $this->assertEquals(['tinkered' => 'data'], $this->response_handler->data([$mapper, 'mapArray'], $data));
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

    public function mapDatThing($data)
    {
        return $this->map($data);
    }
}
