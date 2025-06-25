<?php

namespace App\service;

use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use support\Request;

class CasielStorageService implements StorageServiceInterface
{
    private Client $client;
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = env('CASIEL_BASE_URL', 'http://localhost:8788');
        $this->apiKey = env('CASIEL_INTERNAL_API_KEY');
    }

    public function upload(Request $request, array $data, int $userId): array
    {
        $response = $this->client->post($this->baseUrl . '/v1/media/upload', [
            'headers' => [
                'X-Internal-Auth-Key' => $this->apiKey,
            ],
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($data['file']->getRealPath(), 'r'),
                    'filename' => $data['file']->getUploadName(),
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function download(string $filePath): StreamInterface
    {
        $response = $this->client->get($this->baseUrl . '/v1/media/download/' . $filePath, [
            'headers' => [
                'X-Internal-Auth-Key' => $this->apiKey,
            ],
            'stream' => true,
        ]);

        return $response->getBody();
    }
}
