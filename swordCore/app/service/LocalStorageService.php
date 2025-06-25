<?php

namespace App\service;

use Psr\Http\Message\StreamInterface;
use support\Request;
use Webman\Http\UploadFile;

class LocalStorageService implements StorageServiceInterface
{
    public function upload(Request $request, array $file, int $userId): array
    {
        /** @var UploadFile $uploadFile */
        $uploadFile = $file['file'];
        $publicPath = public_path();
        $filePath = DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . date('Ym');
        $fullPath = $publicPath . $filePath;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $fileName = uniqid() . '.' . $uploadFile->getUploadExtension();
        $uploadFile->move($fullPath . DIRECTORY_SEPARATOR . $fileName);

        return [
            'provider' => 'local',
            'path' => $filePath . DIRECTORY_SEPARATOR . $fileName,
            'url' => request()->getRealHost() . $filePath . DIRECTORY_SEPARATOR . $fileName,
        ];
    }

    public function download(string $filePath): StreamInterface
    {
        $fullPath = public_path() . $filePath;
        if (!file_exists($fullPath)) {
            throw new \Exception('File not found.');
        }

        return fopen($fullPath, 'r');
    }
}
