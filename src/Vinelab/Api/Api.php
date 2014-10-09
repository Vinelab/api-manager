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
use Vinelab\Api\ApiException;

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
     * @throws ApiException
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

        // check if the mapper uses the MappableTrait Trait.
        if ( ! key(class_uses($mapper)) == 'Vinelab\Api\MappableTrait' )
        {
            throw new ApiException('MappableTrait Trait is not used in your Mapper: ' . get_class($mapper) );
        }

        $arguments = [];
        // Check if data is instance of Paginator in order to handle the arguments in a specific way
        // by adding the total and the page manually and taking the third argument as the status
        if ($data instanceof Paginator)
        {
            $arguments[0] = $data->getTotal();
            $arguments[1] = $data->getCurrentPage();
        }

        // skip first 2 arguments and save the rest in the arguments to be merged with the result before passing them
        foreach (array_slice(func_get_args(), 2) as $arg) { $arguments[count($arguments)] = $arg; }

        // In the case of a Collection or Paginator all we need is the data as a
        // Traversable so that we iterate and map each item.
        if ($data instanceof Collection or $data instanceof Paginator) $data = $data->all();

        // call the map function of the mapper for each data in the $data array
        $result[] = (is_array($data)) ? array_map([$mapper, 'map'], $data) : $mapper->map($data);

        return call_user_func_array([$this->responder, 'respond'], array_merge($result, $arguments));
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
