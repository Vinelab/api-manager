<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <inbox@mahmoudzalt.com>
 * @author Abed Halawi <halawi.abed@gmail.com>
 */

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Lib\Api\Mappers\MapperInterface;
use Illuminate\Support\Facades\App;
use Vinelab\Api\Api;

class ApiManager {

    // TODO: get this from the config file
    private $mappers_base_namespace = 'Lib\Api\Mappers\\';

    protected $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
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
        if ( ! $mapper instanceof \Vinelab\Api\MapperInterface) dd('throw Exception...');

        // Check whether we should be iterating through data and mapping each item
        // or we've been passed an instance so that we pass it on as is.
        // We also have support for paginators so where we extract the required data as needed,
        // which helps dev pass in a paginator instance without messing around with it.
        // When we get a paginator we format the args of the ApiResponder as needed.
        if ($data instanceof Paginator)
        {
            $total = $data->getTotal();
            $page = $data->getCurrentPage();

            // TODO: merge other arguments ex: status...
        }
        // Otherwise we take the arguments passed to this function and pass them as is.
        else
        {
            $arguments = array_slice(func_get_args(), 2);

            $total = $arguments[0];
            $page = $arguments[1];
        }

        // In the case of a collection all we need is the data as a Traversable so that we
        // iterate and map each item.
        if ($data instanceof Collection) $data = $data->all();
        // Leave traversing data till the end of the pipeline so that any transformation
        // that happened so far must have transformed them into an array.
        if ($data instanceof Paginator) $data = $data->toArray()['data'];
        //                    ^^^^^^ replaces Traversable

        // call the map function of the mapper for each data in the $data array
        $data = array_map([$mapper, 'map'], $data);


        // Finally, respond.
        return call_user_func_array([$this->api, 'respond'], [$data, $total, $page]);
    }

    /**
     * An error occurred, respond accordingly.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function error()
    {
        return call_user_func([$this->api, 'respondWithError'], func_get_args());
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
