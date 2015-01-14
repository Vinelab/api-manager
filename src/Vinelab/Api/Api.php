<?php namespace Vinelab\Api;

/**
 * @author Mahmoud Zalt <inbox@mahmoudzalt.com>
 * @author Abed Halawi <halawi.abed@gmail.com>
 */

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository;
use Vinelab\Api\ResponseHandler;
use Vinelab\Api\ErrorHandler;
use Vinelab\Api\ApiException;
use \Input;

/**
 * Class Api
 * @package Vinelab\Api
 */
class Api {

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var string
     */
    private $mappers_base_namespace;

    /**
     * @var \Vinelab\Api\ResponseHandler
     */
    private $response_handler;

    /**
     * @var \Vinelab\Api\ErrorHandler
     */
    private $error;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $config_reader;


    /**
     * @param \Vinelab\Api\ResponseHandler  $response_handler
     * @param \Vinelab\Api\ErrorHandler     $error_handler
     * @param \Illuminate\Config\Repository $config_reader
     */
    public function __construct(
        ResponseHandler         $response_handler,
        ErrorHandler            $error_handler,
        Repository              $config_reader
    ) {
        $this->response_handler = $response_handler;
        $this->error            = $error_handler;
        $this->config_reader    = $config_reader;

        // get config file values and store them in attributes
        $this->readConfigFile();
    }

    /**
     * get config file values and store them in attributes
     */
    private function readConfigFile()
    {
        // reading the config file to be stored in the 'configurations' variable below
        $configurations = $this->config_reader->get('api-manager::api');

        $this->mappers_base_namespace = $configurations['mappers'];

        $this->limit = $configurations['limit'];
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
     * Get the content only from the response.
     *
     * @param  mixed $mapper
     * @param  mixed $data
     *
     * @return array
     */
    public function content($mapper, $data)
    {
        $response = call_user_func_array([$this, 'respond'], func_get_args());

        return $response->getOriginalContent();
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
        return App::make($this->getMapperNamespace() . $classname);
    }

    /**
     * get the value of mappers_base_namespace from the config file
     *
     * @return string
     */
    public function getMapperNamespace()
    {
        return $this->mappers_base_namespace;
    }

    /**
     * get the limit value from the config file which represents the default and the maximum limit
     * 
     * @return int
     */
    public function getMaximumLimit()
    {
        // get the default limit value of results per request from the config file
        return (int) $this->limit;
    }

    /**
     * validate the requested limit doesn't exceed the default predefined limit from the config file
     * the user is allowed to override the default limit (form the config file) if and only if it is
     * less then the default limit.
     *
     * @param $limit
     *
     * @return int
     */
    private function validateRequestedLimitValue($request_limit)
    {
        // get limit from config file
        $limit = $this->getMaximumLimit();

        // check if limit value exceed the allowed predefined default limit value
        return ($request_limit <= $limit) ? (int) $request_limit : (int) $limit;
    }


    /**
     * this function will be accessed as facade from anywhere to get the limit number of data for the endpoint call
     *
     * @return int
     */
    public function limit()
    {
        // get limit from config file
        $limit = $this->getMaximumLimit();

        // get the limit from the request if available else get the the default predefined limit from the config file
        if(Input::get('limit') && is_numeric(Input::get('limit'))){
            $limit = Input::get('limit');
        }

        // validate the limit does not exceed the allowed value
        return $this->validateRequestedLimitValue($limit);
    }

    /**
     * override the limit of the config file
     *
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }
    
}
