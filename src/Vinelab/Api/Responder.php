<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class Responder {

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
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function respond($response, $status, $headers, $options)
    {
        // Default is JSON
        switch ($this->getRequestFormat())
        {
            case 'html':
                return Response::make($response, $status, $headers, $options)
                    ->header('Content-Type', 'text/html');

            default :
                return Response::json($response, $status, $headers, $options);
        }

    }

    /**
     * Get url format from the content type header property
     *
     * @return mixed
     */
    public function getRequestFormat()
    {
        return $this->request->format();
    }

}
