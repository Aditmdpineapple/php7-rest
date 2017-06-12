<?php

namespace RestClient\Test;

use RestClient\RestClient;

class RestClientTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiatesProperly()
    {
        $client = new RestClient(RestClient::HTTPS, 'example.com', '', array());

        $this->assertInstanceOf('RestClient\RestClient', $client);
    }
}