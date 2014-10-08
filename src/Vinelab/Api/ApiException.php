<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */

class ApiException extends \Exception{

    /**
     * use predefined message if not provided
     *
     * @param null $code
     * @param null $status
     *
     * @return string
     */
    public function getMessages($code = null, $status = null)
    {

        switch ($status)
        {
            case 401:
                $default_message = 'Invalid something.';
                break;
            case 401:
                $default_message = 'Another message.';
                break;
            default:
                $default_message = 'Default message here.';
                break;
        }

        return (isset($message)) ? $message : $default_message;
    }

}
