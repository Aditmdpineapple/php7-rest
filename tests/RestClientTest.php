<?php

namespace RestClient\Test;

use PHPUnit\Framework\TestCase;
use RestClient\Request;
use RestClient\ResourceException;
use RestClient\RestClient;

date_default_timezone_set('Europe/Amsterdam');
require_once( dirname(__FILE__) . '/../vendor/autoload.php' );

class RestClientTest extends TestCase
{
    /**
     * @var RestClient
     */
    protected $client;

    const MOCK_SCHEME   = RestClient::HTTPS;
    const MOCK_HOST     = 'demo6449375.mockable.io';

    public function setUp()
    {
        $this->client = new RestClient(self::MOCK_SCHEME, self::MOCK_HOST, [
            'version' => 'v2'
        ]);
        $this->client->register('people');
        $this->assertInstanceOf('RestClient\RestClient', $this->client);
    }

    public function testGet()
    {
        $expected = ['name' => 'Roemer Bakker', 'job' => 'Software Engineer'];
        $response = $this->client->people->get(1);

        $this->assertEquals(json_encode(json_decode($response)), json_encode($expected));
    }

    public function testList()
    {
        $expected = array(
            ['name' => 'Roemer Bakker', 'job' => 'Software Engineer'],
            ['name' => 'John Appleseed', 'job' => 'System Administrator']
        );
        $response = $this->client->people->list();

        $this->assertEquals(json_encode($expected), json_encode(json_decode($response)));
    }

    public function testCreate()
    {
        $expected = ['name' => 'Jane Appleseed', 'job' => 'Researcher'];
        $response = $this->client->people->create(json_encode($expected));

        $this->assertEquals(json_encode($expected), json_encode(json_decode($response)));
    }

    public function testCustomMethod()
    {
        $this->client->register_method('people', new Request(RestClient::HTTP_GET, '{id}/job', 'address'));

        $expected = ['job' => 'Software Engineer'];

        // Get address for person with id 1
        $response = $this->client->people->address(1);

        $this->assertEquals(json_encode($expected), json_encode(json_decode($response)));
    }

    public function testClosures()
    {
        $expected = ['name' => 'Roemer Bakker', 'job' => 'Software Engineer'];
        $this->client->people->get(1, function($response) use ($expected) {
            $this->assertEquals(json_encode(json_decode($response)), json_encode($expected));
        });
    }

    public function testExceptionIsThrownWhenUnsupportedMethodIsCalled()
    {
        $ex = null;
        try
        {
            $this->client->people->foo();
        }
        catch (ResourceException $exception)
        {
            $ex = $exception;
        }

        $this->assertNotNull($ex);
    }
}