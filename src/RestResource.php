<?php

namespace RestClient;

/**
 * Interface RESTResource
 * @package RestClient
 */
interface RestResource {

    /**
     *
     */
    const LIST = '{resource}';
    /**
     *
     */
    const POST = '{resource}';
    /**
     *
     */
    const GET = '{resource/{id}';
    /**
     *
     */
    const UPDATE = '{resource}/{id}';
    /**
     *
     */
    const DELETE = '{resource}/{id}';

    /**
     * RESTResource constructor.
     * @param RestClient $client
     * @param $resource
     * @param array $supports
     */
    public function __construct(RestClient $client, $resource, $supports = []);

    /**
     * Check if this resource supports a certain method.
     *
     * @param $method
     * @return mixed
     */
    public function supports($method);
}
