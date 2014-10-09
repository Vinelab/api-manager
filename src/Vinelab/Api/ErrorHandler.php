<?php namespace Vinelab\Api;
/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */
use Illuminate\Support\Facades\Response;
use Vinelab\Api\ApiException;

class ErrorHandler {

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
            throw new ApiException('handle() first parameter expects an instance of Exception or
                                    RuntimeException or a string, ' . get_class($exception) . ' is
                                    given.');
        }

        return Response::json([
            'status' => $status,
            'error'  => compact('message', 'code')
        ], $status, $headers, $options);
    }
}
