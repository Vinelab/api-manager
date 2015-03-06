<?php namespace Vinelab\Api;

/**
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 * @author  Abed Halawi <abed.halawi@vinelab.com>
 */

class ResponseHandler
{

    /**
     * @var Responder instance
     */
    protected $responder;

    /**
     * @param \Vinelab\Api\Responder $responder
     */
    public function __construct(Responder $responder)
    {
        $this->responder = $responder;
    }

    /**
     * Format a response into an elegant api manager response.
     *
     * @param array $data
     * @param null  $total
     * @param null  $page
     * @param null  $per_page
     * @param int   $status
     * @param array $headers
     * @param int   $options
     *
     * @internal param $arguments
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond(
        array $data,
        $total = null,
        $page = null,
        $per_page = null,
        $status = 200,
        $headers = [],
        $options = 0
    ) {
        $response = [
            'status' => $status,
        ];
        if (!is_null($total)) {
            $response['total'] = $total;
        }
        if (!is_null($page)) {
            $response['page'] = $page;
        }
        if (!is_null($per_page)) {
            $response['per_page'] = $per_page;
        }
        $response['data'] = $data;

        return $this->responder->respond($response, $status, $headers, $options);
    }
}
