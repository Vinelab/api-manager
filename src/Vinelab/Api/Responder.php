<?php

namespace Vinelab\Api;

/*
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class Responder
{
    /**
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $response
     * @param $status
     * @param $headers
     * @param $options
     *
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function respond($response, $status, $headers, $options)
    {
        switch ($this->getRequestFormat()) {
            case 'html' :
            case 'text/html' :
                return Response::make($response, $status, $headers, $options)
                    ->header('Content-Type', 'text/html');
            default :
                // whether 'Content-Type' is NULL or equal to anything such as 'application/json' response will be JSON
                return Response::json($response, $status, $headers, $options);
        }
    }

    /**
     * Get request format from the content type header property.
     *
     * @return string
     */
    public function getRequestFormat()
    {
        return $this->request->header('Content-Type');
    }
}
