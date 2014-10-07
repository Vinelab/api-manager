<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */

class ApiException{

    protected $error = [];

    public function __construct($code, $status, $message = null)
    {
        $this->error['code'] = $code;
        $this->error['status'] = $status;
        $this->error['message'] = $message;
    }

    /**
     * return error details
     *
     * @param $attr
     *
     * @return mixed
     */
    public function __get($attr)
    {
        return $this->error[$attr];
    }

    /**
     * use predefined message if not provided
     *
     * @param null $code
     * @param null $status
     */
    private function message($code = null, $status = null)
    {

//        switch ($status)
//        {
//            case 401:
//                $default_message = 'Invalid something.';
//                break;
//            case 401:
//                $default_message = 'Another message.';
//                break;
//            default:
//                $default_message = 'Default message here.';
//                break;
//        }
//
//        $message = (isset($message)) ? $message : $default_message;
//
//        return $this->api_responder->error($status, $message);
    }

}
