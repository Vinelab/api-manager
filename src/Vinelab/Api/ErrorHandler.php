<?php namespace Vinelab\Api;
/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */
use Illuminate\Support\Facades\Response;

class ErrorHandler {

    public function handle($exception, $code = 0, $status = 500, $headers = [], $options = 0)
    {

        // If the exception is on of ours then treat it as implemented.
        if ($exception instanceof ApiException)
        {
            $code = $exception->getCode();
            $message = $exception->getMessages($code, $status);
        }
        // This is a generic, non-supported exception so we'll just treat it as so.
        elseif ($exception instanceof Exception or $exception instanceof RuntimeException)
        {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        }else{
            $message = null;
        }

        return Response::json([
            'status' => $status,
            'error'  => compact('message', 'code')
        ], $status, $headers, $options);
    }
}





//
//<?php namespace Vinelab\apimanager;
//
//use Illuminate\Support\Facades\Response;
//
//class ErrorHandler {
//
//    /**
//     * @param $exception instance of (ApiException or Exception or RuntimeException) or a String
//     * @param int $error_code
//     * @param int $status
//     * @param array $headers
//     * @param int $options
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function handle($exception, $error_code = 1000, $status = 500, $headers = [], $options = 0)
//    {
//
//        // If the exception is on of ours then treat it as implemented.
//        if ($exception[0] instanceof ApiException)
//        {
//            $error_code = $exception->getCode();
//            $status     = $exception->getStatus();
//            $message    = $exception->getMessages();
//        }
//        // This is a generic, non-supported exception so we'll just treat it as so.
//        elseif ($exception instanceof Exception or $exception instanceof RuntimeException)
//        {
//            $error_code = $exception->getCode();
//            $status     = $status;
//            $message    = $exception->getMessage();
//        } else {
//            $error_code = $error_code;
//            $status     = $status;
//            $message    = $exception;
//        }
//
//        return Response::json([
//                'status' => $status,
//                'error'  => compact('message', 'error_code')
//            ], $status, $headers, $options);
//    }
//}
