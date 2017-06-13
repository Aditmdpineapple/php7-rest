<?php

namespace RestClient;

class Request
{
    protected $httpMethod;
    protected $method;
    protected $id;
    protected $payload;

    /**
     * Request constructor.
     *
     * @param $httpMethod
     * @param $method
     * @param $id
     * @param null $payload
     */
    public function __construct($httpMethod, $method, $id = null, $payload = null)
    {
        $this->httpMethod = $httpMethod;
        $this->method = $method;
        $this->id = $id;
        $this->payload = $payload;
    }

    /**
     * Get the path for the URL
     *
     * @return mixed
     */
    public function toString() : string
    {
        return str_replace('{id}', $this->getId(), $this->getMethod());
    }

    /**
     * Get the Http method
     *
     * @return string
     */
    public function getHttpMethod() : string
    {
        return $this->httpMethod;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Get the ID
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the payload of the request.
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the ID for the request
     *
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Sets the payload for the request.
     *
     * @param mixed $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }
}