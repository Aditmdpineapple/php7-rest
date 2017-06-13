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
     * @return mixed
     * @throws ResourceException
     */
    public function get($id) : string;

    /**
     * List the resource
     *
     * @return mixed
     * @throws ResourceException
     */
    public function list() : string;

    /**
     * Create a new instance of the resource
     *
     * @param $resource
     * @return mixed
     * @throws ResourceException
     */
    public function create($resource) : string;

    /**
     * Update a resource instance
     *
     * @param $id
     * @param $resource
     * @return string
     * @throws ResourceException
     */
    public function update($id, $resource) : string;

    /**
     * Delete a resource instance
     *
     * @param $id
     * @return string
     * @throws ResourceException
     */
    public function delete($id);

    /**
     * Check if this resource supports a certain method.
     *
     * @param $method
     * @return bool
     */
    public function supports($method);
}
