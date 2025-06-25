<?php

namespace App\service;

use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use support\Request;

class ExternalStorageService implements StorageServiceInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function upload(Request $request, array $data, int $userId): array
    {
        if (!isset($data['url']) || !filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('La URL proporcionada no es válida.');
        }

        $response = $this->client->get($data['url']);
        $fileContents = $response->getBody()->getContents();
        $fileName = basename(parse_url($data['url'], PHP_URL_PATH));

        $publicPath = public_path();
        $filePath = DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . date('Ym');
        $fullPath = $publicPath . $filePath;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $newFileName = uniqid() . '-' . $fileName;
        file_put_contents($fullPath . DIRECTORY_SEPARATOR . $newFileName, $fileContents);
        
        $urlPath = str_replace(DIRECTORY_SEPARATOR, '/', $filePath . DIRECTORY_SEPARATOR . $newFileName);

        return [
            'provider' => 'external',
            'path' => $filePath . DIRECTORY_SEPARATOR . $newFileName,
            'url' => request()->host() . $urlPath,
        ];
    }

    public function download(string $filePath): StreamInterface
    {
        // For external files, the path is a URL, so we just redirect.
        // This method might not be directly used if we store external files locally.
        // If we do, the implementation would be similar to LocalStorageService.
        throw new \Exception('Not implemented for external storage. Files are downloaded and stored locally during upload.');
    }
}
