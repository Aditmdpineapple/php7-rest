<?php

namespace RestClient;

/**
 * Class AbstractResource
 * @package RestClient
 */
class RestResource implements RestResourceContract
{

    /**
     * The client.
     *
     * @var RestClient
     */
    protected $client;

    /**
     * Name of the resource. This is what'll be called.
     *
     * @var string
     */
    protected $resource;

    /**
     * Array of supported methods.
     *
     * @var array
     */
    protected $supports;

    /**
     * Entries in the supports array in Request form.
     *
     * @var Request[]
     */
    protected $requests;

    /**
     * RestResource constructor.
     *
     * @param RestClient $client
     * @param null $resource
     * @param array $supports
     */
    public function __construct(RestClient $client, $resource = null, $supports = [])
    {
        if (is_null($resource))
            return;

        $this->client   = $client;
        $this->resource = $resource;
        $this->supports = $supports;

        // Initiate default requests.
        foreach ($supports as $supportedMethod)
        {
            $http   = self::get_http_method_for_verb($supportedMethod);
            $path   = self::get_path_for_verb($supportedMethod);

            $this->requests[$supportedMethod] = new Request($http, $path);
        }
    }

    /**
     * Magic method for custom methods
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws ResourceException
     */
    public function __call($name, $arguments)
    {
        foreach($this->requests as $request)
        {
            if ($request->getName() === $name)
            {
                /*
                 * Check if the arguments are set. Currently, only an ID is supported to be included in the request path.
                 * As this is the only parameter supported, we check for the first index of the arguments array.
                 */
                if (!is_null($arguments[0]))
                    $request->setId($arguments[0]);

                return $this->client->do($request, $this->getResource());
            }
        }

        throw new ResourceException(sprintf('Resource \'%s\' does not have a method \'%s\'.', __CLASS__, $name));
    }

    /**
     * Registers a custom method
     *
     * @param Request $request
     */
    public function register_method(Request $request)
    {
        $this->requests[$request->getMethod()] = $request;
    }

    /**
     * Gets resource string
     *
     * @return string
     */
    public function getResource() : string
    {
        return $this->resource;
    }

    /**
     * Get a specific resource instance
     *
     * @param $id
     * @return mixed
     * @throws ResourceException
     */
    public function get($id) : string
    {
        // Check if the resource supports get
        if (!$this->supports(RestClient::METHOD_GET))
            throw new ResourceException(sprintf('%s does not support \'%s\'.', __CLASS__, RestClient::METHOD_GET));

        // It requires an ID. Check if it's valid.
        if (is_null($id) || empty($id))
            throw new ResourceException(sprintf('The method %s requires an ID. You provided none.', RestClient::METHOD_GET));

        // Find the request and set the ID
        $req = $this->requests[RestClient::METHOD_GET];
        $req->setId($id);

        // Return the result of the execution of the request.
        return $this->client->do($req, $this->getResource());
    }

    /**
     * List the resource
     *
     * @return mixed
     * @throws ResourceException
     */
    public function list() : string
    {
        // Check if the resource supports listing
        if (!$this->supports(RestClient::METHOD_LIST))
            throw new ResourceException(sprintf('%s does not support \'%s\'.', __CLASS__, RestClient::METHOD_LIST));

        // Return the result of the execution of the request.
        return $this->client->do($this->requests[RestClient::METHOD_LIST], $this->getResource());
    }

    /**
     * Create a new instance of the resource
     *
     * @param $resource
     * @return mixed
     * @throws ResourceException
     */
    public function create($resource) : string
    {
        // Check if the resource supports creation
        if (!$this->supports(RestClient::METHOD_CREATE))
            throw new ResourceException(sprintf('%s does not support \'%s\'.', __CLASS__, RestClient::METHOD_CREATE));

        // Check if the payload is provided
        if (is_null($resource) || empty($resource))
            throw new ResourceException(sprintf('The method %s requires a resource. You provided none.', RestClient::METHOD_CREATE));

        // Set the payload for the request.
        $req = $this->requests[RestClient::METHOD_CREATE];
        $req->setPayload($resource);

        // Return the result of the execution of the request.
        return $this->client->do($req, $this->getResource());
    }

    /**
     * Update a resource instance
     *
     * @param $id
     * @param $resource
     * @return string
     * @throws ResourceException
     */
    public function update($id, $resource) : string
    {
        // Check if the resource supports patching
        if (!$this->supports(RestClient::METHOD_UPDATE))
            throw new ResourceException(sprintf('%s does not support \'%s\'.', __CLASS__, RestClient::METHOD_UPDATE));

        // Check if an ID is provided
        if (is_null($id) || empty($id))
            throw new ResourceException(sprintf('The method %s requires an ID. You provided none.', RestClient::METHOD_UPDATE));

        // Check if the payload is provided
        if (is_null($resource) || empty($resource))
            throw new ResourceException(sprintf('The method %s requires a resource. You provided none.', RestClient::METHOD_UPDATE));

        // Set the ID and payload
        $req = $this->requests[RestClient::METHOD_UPDATE];
        $req->setId($id);
        $req->setPayload($resource);

        // Return the result of the execution of the request.
        return $this->client->do($req, $this->getResource());
    }

    /**
     * Delete a resource instance
     *
     * @param $id
     * @return string
     * @throws ResourceException
     */
    public function delete($id)
    {
        if (!$this->supports(RestClient::METHOD_DELETE))
            throw new ResourceException(sprintf('%s does not support \'%s\'.', __CLASS__, RestClient::METHOD_DELETE));

        if (is_null($id) || empty($id))
            throw new ResourceException(sprintf('The method %s requires an ID. You provided none.', RestClient::METHOD_DELETE));

        // Set the ID
        $req = $this->requests[RestClient::METHOD_DELETE];
        $req->setId($id);

        // Return the result of the execution of the request.
        return $this->client->do($req, $this->getResource());
    }

    /**
     * Check if this resource supports a certain method.
     *
     * @param $method
     * @return bool
     */
    public function supports($method) : bool
    {
        return in_array($method, $this->supports);
    }

    /**
     * Get the Http method for default methods.
     *
     * @param $verb
     * @return string
     */
    private static function get_http_method_for_verb($verb) : string
    {
        $method = '';
        switch ($verb)
        {
            case RestClient::METHOD_LIST:
            case RestClient::METHOD_GET:
                $method = RestClient::HTTP_GET;
                break;
            case RestClient::METHOD_CREATE:
                $method = RestClient::HTTP_POST;
                break;
            case RestClient::METHOD_DELETE:
                $method = RestClient::HTTP_DELETE;
                break;
            case RestClient::METHOD_UPDATE:
                $method = RestClient::HTTP_PATCH;
                break;
            default:
                $method = RestClient::HTTP_GET;
                break;
        }

        return $method;
    }

    /**
     * Get the path for default methods.
     *
     * @param $verb
     * @return string
     */
    private static function get_path_for_verb($verb) : string
    {
        $path = '';
        switch($verb)
        {
            case RestClient::METHOD_GET:
            case RestClient::METHOD_UPDATE:
            case RestClient::METHOD_DELETE:
                $path = '{id}';
                break;
            case RestClient::METHOD_LIST:
            case RestClient::METHOD_CREATE:
                $path = '';
                break;
        }

        return $path;
    }

    /**
     * Check if a default method requires an ID.
     *
     * @param $verb
     * @return bool
     */
    private static function has_id($verb) : bool
    {
        $hasId = ['get', 'update', 'delete'];

        return in_array($verb, $hasId);
    }
}
