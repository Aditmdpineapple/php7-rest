<?php

namespace RestClient;

class RestClient {

    const VERSION = '1.0';
    const AGENT = 'RestClient';

    const HTTP = 'http';
    const HTTPS = 'https';

    const HTTP_GET      = "GET";
    const HTTP_POST     = "POST";
    const HTTP_DELETE   = "DELETE";
    const HTTP_PATCH    = "PATCH";

    const METHOD_GET    = 'get';
    const METHOD_CREATE = 'create';
    const METHOD_LIST   = 'list';
    const METHOD_UPDATE = 'update';
    const METHOD_DELETE = 'delete';

    protected $scheme;
    protected $host;
    protected $options;
    protected $headers;
    protected $resources;

    protected $ch;
    protected $methods;
    protected $response;

    /**
     * RestClient constructor.
     *
     * @param $scheme
     * @param string $host
     * @param array $options
     */
    public function __construct($scheme = self::HTTPS, $host = '', $options = array()) {
        $this->scheme   = $scheme;
        $this->host     = $host;
        $this->options  = $options;

        $this->ch = curl_init();
        $this->addHeader('User-Agent', self::AGENT . '/' . self::VERSION);
        $this->addHeader('Content-Type', 'application/json');
    }

    /**
     * Used to query resources.
     *
     * @param $name
     * @param $args
     * @return mixed
     * @throws ResourceException
     */
    public function __call($name, $args)
    {
        $name = strtolower($name);
        if (in_array($name, $this->methods))
        {
            return $this->methods[$name]($args);
        }

        throw new ResourceException(sprintf('%s is not a resource.'));
    }

    /**
     * Executes a HTTP request
     *
     * @param Request $req
     * @return mixed
     */
    public function do(Request $req)
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $req->getHttpMethod());
        curl_setopt($this->ch, CURLOPT_URL, $this->url($req));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        $this->setResponse(curl_exec($this->ch));

        return $this->getLastResponse();
    }

    /**
     * Returns a prepared URL
     *
     * @param Request $req
     * @return string
     */
    protected function url(Request $req)
    {
        $url = '';
        $url .= $this->scheme . '://';
        $url .= $this->host;
        $url .= $req->toString();

        return $url;
    }

    /**
     * Adds a header
     *
     * @param string $key
     * @param string $value
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Deletes an existing header
     *
     * @param string $key
     */
    public function delHeader($key)
    {
        unset($this->headers[$key]);
    }

    /**
     * protected function to set the response of a request
     *
     * @param $response
     */
    protected function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Gives the last response
     *
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->response;
    }
}
