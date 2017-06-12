<?php

namespace RestClient\Test;

use RestClient\Request;
use RestClient\RestClient;

class RestClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestClient
     */
    protected $client;

    const MOCK_SCHEME   = RestClient::HTTPS;
    const MOCK_HOST     = 'demo6449375.mockable.io';

    public function setUp()
    {
        parent::setUp();

        $this->client = new RestClient(self::MOCK_SCHEME, self::MOCK_HOST, [
            'version' => 'v2'
        ]);
        $this->client->register('people', ['list', 'get', 'create', 'update', 'delete']);
        $this->assertInstanceOf('RestClient\RestClient', $this->client);
    }

    public function testGet()
    {
        $expected = ['name' => 'Roemer Bakker', 'job' => 'Software Engineer'];
        $response = $this->client->people->get(1);

        $this->assertEquals(json_encode(json_decode($response)), json_encode($expected));
    }
}