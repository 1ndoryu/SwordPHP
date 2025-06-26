<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseInterface;

abstract class TestCase extends BaseTestCase
{
    protected Client $http;

    /**
     * The token for the admin user.
     * @var string|null
     */
    protected static ?string $adminToken = null;

    /**
     * The token for the regular user.
     * @var string|null
     */
    protected static ?string $userToken = null;


    protected function setUp(): void
    {
        parent::setUp();
        $this->http = new Client([
            'base_uri' => env('APP_URL', 'http://127.0.0.1:8787'),
            'http_errors' => false, // Do not throw exceptions on 4xx/5xx status codes
            'timeout' => 10.0,
        ]);
    }

    /**
     * Helper to make a JSON POST request.
     *
     * @param string $uri
     * @param array $data
     * @param string|null $token
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function postJson(string $uri, array $data, ?string $token = null): ResponseInterface
    {
        $options = ['json' => $data];
        if ($token) {
            $options['headers'] = ['Authorization' => 'Bearer ' . $token];
        }
        return $this->http->post($uri, $options);
    }

    /**
     * Helper to make a JSON GET request.
     *
     * @param string $uri
     * @param string|null $token
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function getJson(string $uri, ?string $token = null): ResponseInterface
    {
        $options = [];
        if ($token) {
            $options['headers'] = ['Authorization' => 'Bearer ' . $token];
        }
        return $this->http->get($uri, $options);
    }

    /**
     * Helper to make a JSON DELETE request.
     *
     * @param string $uri
     * @param string|null $token
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function deleteJson(string $uri, ?string $token = null): ResponseInterface
    {
        $options = [];
        if ($token) {
            $options['headers'] = ['Authorization' => 'Bearer ' . $token];
        }
        return $this->http->delete($uri, $options);
    }
}
