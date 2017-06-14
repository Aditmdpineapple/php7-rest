<?php

namespace RestClient;

/**
 * Interface RestResourceContract
 * @package RestClient
 */
interface RestResourceContract {

    /**
     * RESTResource constructor.
     * @param RestClient $client
     * @param $resource
     * @param array $supports
     */
    public function __construct(RestClient $client, $resource, $supports = []);

    /**
     * Magic method for custom methods
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments);

    /**
     * @param Request $request
     */
    public function register_method(Request $request);

    /**
     * Gets resource string
     *
     * @return string
     */
    public function getResource() : string;

    /**
     * Get a specific resource instance
     *
     * @param $id
     * @param callable $closure
     * @return mixed|string
     */
    public function get($id, callable $closure = null) : string;

    /**
     * List the resource
     *
     * @param callable $closure
     * @return mixed|string
     */
    public function list(callable $closure = null) : string;

    /**
     * Create a new instance of the resource
     *
     * @param $resource
     * @param callable $closure
     * @return mixed|string
     */
    public function create($resource, callable $closure = null) : string;

    /**
     * Update a resource instance
     *
     * @param $id
     * @param $resource
     * @return string
     * @throws ResourceException
     */
    public function update($id, $resource, callable $closure = null) : string;

    /**
     * Delete a resource instance
     *
     * @param $id
     * @param callable $closure
     * @return string
     */
    public function delete($id, callable $closure = null);

    /**
     * Check if this resource supports a certain method.
     *
     * @param $method
     * @return bool
     */
    public function supports($method);
}
