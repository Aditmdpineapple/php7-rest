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
    protected $apiVersion;
    protected $options;
    protected $headers;
    protected $resources = array();

    protected $ch;
    protected $methods;
    protected $response;
    protected $responseCode;

    /**
     * RestClient constructor.
     *
     * @param $scheme
     * @param string $host
     * @param array $options
     */
    public function __construct($scheme = self::HTTPS, $host = '', $options = array()) {
        // Initiate cURL
        $this->ch = curl_init();

        // Strip possible '://' from scheme
        $scheme = str_replace('/', '', $scheme);
        $scheme = str_replace(':', '', $scheme);
        $this->scheme   = $scheme;

        // Strip possible trailing / from the host
        if ($host[strlen($host) - 1] === '/')
            $host = substr($host, 0, -1);

        // User may have already supplied a port in the host
        $hostColon = strpos($host, ':');
        if ($hostColon !== false)
        {
            $port = substr($host, $hostColon);
            if ((int) $port !== 0)
            {
                // The port is in the host. Override options.
                $this->options['port'] = (int) $port;
            }
        }
        $this->host = $host;

        // Set and apply options
        $this->options = $options;
        $this->apply_options();
    }

    /**
     * Used to query resources.
     *
     * @param $name
     * @return mixed
     * @throws ResourceException
     * @internal param $args
     */
    public function __get($name)
    {
        $name = strtolower($name);
        if (isset($this->resources[$name]) && !is_null($this->resources[$name]))
        {
            return $this->resources[$name];
        }

        throw new ResourceException(sprintf('\'%s\' is not a resource.', $name));
    }

    /**
     * Evaluates the provided options and applies the preferences
     */
    private function apply_options()
    {
        // version
        if (isset($this->options['version']))
        {
            $this->apiVersion = $this->options['version'];
        }

        // port
        if (isset($this->options['port']) && (int) $this->options['port'] !== 0 && (int) $this->options['port'] > 0)
        {
            $this->port = $this->options['port'];
        }

        // cert
        if (isset($this->options['cert']) && is_file($this->options['cert']))
        {
            curl_setopt($this->ch, CURLOPT_CAINFO, $this->options['cert']);
        }

        // content_type
        // Check if it exists in options and is not empty
        if (isset($this->options['content_type']) && !empty($this->options['content_type']))
        {
            // Content type is custom, set it
            $this->addHeader('Content-Type', $this->options['content_type']);
        }
        else if (!isset($this->options['content_type']))
        {
            // Content type does not exist in options. Use default.
            $this->addHeader('Content-Type', __CLASS__. '/' . self::VERSION);
        }
        else if (isset($this->options['content_type']) && $this->options['content_type'] === false)
        {
            // It is set and set to false, make sure it's not included in the request
            if (isset($this->headers['Content-Type']))
            {
                unset($this->headers['Content-Type']);
            }
        }

        // user_agent
        // Check if it exists in options and is not empty
        if (isset($this->options['user_agent']) && !empty($this->options['user_agent']))
        {
            // User agent is custom, set it
            $this->addHeader('User-Agent', $this->options['user_agent']);
        }
        else if (!isset($this->options['user_agent']))
        {
            // User agent does not exist in options. Use default.
            $this->addHeader('User-Agent', __CLASS__. '/' . self::VERSION);
        }
        else if (isset($this->options['user_agent']) && $this->options['user_agent'] === false)
        {
            // It is set and set to false, make sure it's not included in the request
            if (isset($this->headers['User-Agent']))
            {
                unset($this->headers['User-Agent']);
            }
        }

        if (isset($this->options['insecure']) && $this->options['insecure'])
        {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        }
    }

    public function register($name, $supports)
    {
        $resource = new RestResource($this, $name, $supports);
        $this->resources[$name] = $resource;
    }

    /**
     * Executes a HTTP request
     *
     * @param Request $req
     * @param string $resource
     * @return mixed
     */
    public function do(Request $req, $resource)
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $req->getHttpMethod());
        curl_setopt($this->ch, CURLOPT_URL, $this->url($req, $resource));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

        switch($req->getHttpMethod())
        {
            case self::HTTP_POST:
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $req->getPayload());
        }

        $this->setResponse(curl_exec($this->ch));
        $this->setResponseCode(curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE));
        $this->setResponse(curl_exec($this->ch));

        return $this->getLastResponse();
    }

    /**
     * Returns a prepared URL
     *
     * @param Request $req
     * @param string $resource
     * @return string
     */
    public function url(Request $req, $resource)
    {
        $url = '';
        $url .= $this->scheme . '://';
        $url .= $this->host;
        if (isset($this->options['port']) && !is_null($this->options['port']))
            $url .= ':' . $this->options['port'];
        if (!is_null($this->options['version']))
            $url .= '/' . $this->options['version'] . '/';
        $url .= $resource . '/';
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

    /**
     * @return mixed
     */
    public function getLastResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param mixed $responseCode
     */
    private function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }
}
