<?php namespace Vinelab\Api;

/**
 * @author  Abed Halawi <abed.halawi@vinelab.com>
 */

use Illuminate\Support\Facades\App;

class Api {

    /**
     * @var \Lib\Api\Responder
     */
    protected $responder;

    /**
     * @var \Lib\Api\ErrorHandler
     */
    protected $error;

    public function __construct()
    {
        $this->error = App::make('Vinelab\Api\ErrorHandler');
        $this->responder = App::make('Vinelab\Api\Responder');
    }

    public function respond()
    {
        // Call the Responder's respond() with any args passed to this function.
        return call_user_func_array([$this->responder, 'respond'], func_get_args());
    }

    public function respondWithError()
    {
        // Call the ErrorHandler's handle() with any args passed to this function.
        return call_user_func_array([$this->error, 'handle'], func_get_args());
    }
}
