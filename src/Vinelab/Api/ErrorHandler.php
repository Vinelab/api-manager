<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */

use Vinelab\Api\ApiException;
use Exception, RuntimeException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;

class ErrorHandler {

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
     * @param       $exception
     * @param int   $code
     * @param int   $status
     * @param array $headers
     * @param int   $options
     *
     * @return \Illuminate\Http\JsonResponse|string
     * @throws \Vinelab\Api\ApiException
     */
    public function handle($exception, $code = 0, $status = 500, $headers = [], $options = 0)
    {

        if (is_string($exception))
        {
            $message = $exception;
        }
        // This is a generic, non-supported exception so we'll just treat it as so.
        elseif ($exception instanceof Exception or $exception instanceof RuntimeException)
        {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        }
        // if not Exception or RuntimeException or a string then throw and API exception
        else
        {
            throw new ApiException('Argument 1 passed Vinelab\Api\ErrorHandler::handle() must be an instance of
                                    Exception or RuntimeException or a string, ' . get_class($exception) . ' is given.');
        }

        $response = [
            'status' => $status,
            'error'  => compact('message', 'code')
        ];

        return $this->responder->respond($response, $status, $headers, $options);
    }
}
