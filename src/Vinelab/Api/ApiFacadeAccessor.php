<?php namespace Vinelab\Api;

use Illuminate\Support\Facades\Facade;

class ApiFacadeAccessor extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'api';
    }
}
