<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <inbox@mahmoudzalt.com>
 * @author Abed Halawi <halawi.abed@gmail.com>
 */

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Config\Repository;
use Vinelab\Api\ErrorHandler;
use Vinelab\Api\Responder;


/**
 * Class Api
 * @package Vinelab\Api
 */
class Api {

    private $mappers_base_namespace;

    /**
     * @var
     */
    protected $api;

    /**
     * @var mixed
     */
    protected $configurations;

    public function __construct(
        Responder               $responder,
        ErrorHandler            $error_handler,
        Repository              $config_reader
    ) {
        $this->responder        = $responder;
        $this->error            = $error_handler;
        $this->config_reader    = $config_reader;

        // reading the config file and storing it in the 'configurations' variable
        $this->configurations         = $this->config_reader->get('api::api');
        // assigning the 'mappers_base_namespace' to its value from the config file
        $this->mappers_base_namespace = $this->configurations['mappers'];
    }

    /**
     * Map and respond
     *
     * @param $mapper
     * @param $data
     *
     * @internal param \the $model model name
     * @internal param int $status
     * @internal param null $total
     * @internal param null $page
     * @internal param array $headers
     * @internal param int $options
     * @internal param $mapper
     * @return Illuminate\Http\JsonResponse
     */
    public function respond($mapper, $data)
    {
        // check whether $mapper is an actual mapper instance, otherwise
        // resolve the mapper class name into a mapper instance.
        if ( ! is_object($mapper)) $mapper = $this->resolveMapperClassName($mapper);

        // All mappers must implement or extend the mapper interface.
//        if ( ! $mapper instanceof \Vinelab\Api\Mappable) dd('throw Exception...');

        // Check whether we should be iterating through data and mapping each item
        // or we've been passed an instance so that we pass it on as is.
        // We also have support for paginators so where we extract the required data as needed,
        // which helps dev pass in a paginator instance without messing around with it.
        // When we get a paginator we format the args of the ApiResponder as needed.
        if ($data instanceof Paginator)
        {
            $total = $data->getTotal();
            $page = $data->getCurrentPage();
//            $arguments = array_slice(func_get_args(), 2);
//            $status = ( isset($arguments[0]) ) ? $arguments[0] : null;
        }
        // Otherwise we take the arguments passed to this function and pass them as is.
        else
        {
            $arguments = array_slice(func_get_args(), 2);

            $total = ( isset($arguments[0]) ) ? $arguments[0] : null;
            $page = ( isset($arguments[1]) ) ? $arguments[1] : null;
//            $status = ( isset($arguments[2]) ) ? $arguments[2] : null;
        }

        // In the case of a collection all we need is the data as a Traversable so that we
        // iterate and map each item.
        if ($data instanceof Collection) $data = $data->toArray();
        // Leave traversing data till the end of the pipeline so that any transformation
        // that happened so far must have transformed them into an array.
        if ($data instanceof Paginator) $data = $data->toArray()['data'];

        // call the map function of the mapper for each data in the $data array
        $data = (is_array($data)) ? array_map([$mapper, 'map'], $data) : $mapper->map($data);

        return call_user_func_array([$this->responder, 'respond'], [$data, $total, $page]);
    }

    /**
     * An error occurred, respond accordingly.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function error()
    {
        return call_user_func_array([$this->error, 'handle'], func_get_args());
    }

    /**
     * Resolve a class name of a mapper into the actual instance.
     *
     * @param  string $classname
     *
     * @return mixed
     */
    private function resolveMapperClassName($classname)
    {
        return App::make($this->mappers_base_namespace . $classname);
    }

}
