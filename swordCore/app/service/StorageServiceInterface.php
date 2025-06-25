<?php

namespace App\service;

use Psr\Http\Message\StreamInterface;
use support\Request;

interface StorageServiceInterface
{
    public function upload(Request $request, array $data, int $userId): array;

    public function download(string $filePath): StreamInterface;
}
