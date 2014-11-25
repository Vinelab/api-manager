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
use Vinelab\Api\ResponseHandler;
use Vinelab\Api\ApiException;

/**
 * Class Api
 * @package Vinelab\Api
 */
class Api {

    private $mappers_base_namespace;

    /**
     * @var mixed
     */
    protected $configurations;

    public function __construct(
        ResponseHandler         $response_handler,
        ErrorHandler            $error_handler,
        Repository              $config_reader
    ) {
        $this->response_handler = $response_handler;
        $this->error            = $error_handler;
        $this->config_reader    = $config_reader;

        // reading the config file and storing it in the 'configurations' variable
        $this->configurations         = $this->config_reader->get('api-manager::api');

        // assigning the 'mappers_base_namespace' to its value from the config file
        $this->mappers_base_namespace = $this->configurations['mappers'];
    }

    /**
     * Map and respond
     *
     * @param string|mixed $mapper
     * @param mixed $data
     *
     * @throws ApiException
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function respond($mapper, $data)
    {
        $arguments = [];
        // if data is instance of Paginator then get the values of the total and the page from the paginator,
        // and add them to the arguments array (total, page)
        if ($data instanceof Paginator)
        {
            $arguments[0] = $data->getTotal();
            $arguments[1] = $data->getCurrentPage();
            $arguments[2] = $data->getPerPage();
        }

        // skip the first 2 arguments and save the rest to the 'arguments array':
        // > in case data is instance of Paginator then this will append all the arguments to the 'arguments array'
        // starting by the third arguments which should be the 'status'.
        // > in case data is is not instance of Paginator (means total and page and per_page are added manually as
        // arguments) then this will add all the arguments to the 'arguments array' starting by the third arguments
        // which should be the 'page'.
        foreach (array_slice(func_get_args(), 2) as $arg) {
            $arguments[count($arguments)] = $arg;
        }

        $result[] = $this->data($mapper, $data);

        return call_user_func_array([$this->response_handler, 'respond'], array_merge($result, $arguments));
    }

    /**
     * Get the formatted data out of the given mapper and data.
     *
     * @param  string|mixed $mapper
     * @param  mixed $data
     *
     * @return array
     */
    public function data($mapper, $data)
    {
        // check whether $mapper is an actual mapper instance, otherwise
        // resolve the mapper class name into a mapper instance.
        if ( ! is_object($mapper)) $mapper = $this->resolveMapperClassName($mapper);

        // check if the mapper uses the MappableTrait Trait.
        if ( ! array_key_exists('Vinelab\Api\MappableTrait', class_uses($mapper)) )
        {
            throw new ApiException('MappableTrait Trait is not used in your Mapper: ' . get_class($mapper) );
        }

        // In the case of a Collection or Paginator all we need is the data as a
        // Traversable so that we iterate and map each item.
        if ($data instanceof Collection or $data instanceof Paginator) $data = $data->all();

        // call the map function of the mapper for each data in the $data array
        return (is_array($data)) ? array_map([$mapper, 'map'], $data) : $mapper->map($data);
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
