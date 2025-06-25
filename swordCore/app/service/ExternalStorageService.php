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
        $this->client = new Client(['timeout' => 10]); // Añadir un timeout
    }

    public function upload(Request $request, array $data, int $userId): array
    {
        if (!isset($data['url']) || !filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('La URL proporcionada no es válida.');
        }

        // --- INICIO DE LA CORRECCIÓN ---
        // 1. Descargar el archivo y obtener sus propiedades.
        $response = $this->client->get($data['url']);
        $fileContents = $response->getBody()->getContents();
        $originalName = basename(parse_url($data['url'], PHP_URL_PATH));
        
        // Determinar el tipo MIME desde el contenido o la cabecera.
        $mimeType = 'application/octet-stream'; // Valor por defecto
        if ($response->hasHeader('Content-Type')) {
            $mimeType = explode(';', $response->getHeaderLine('Content-Type'))[0];
        } else if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $fileContents);
            finfo_close($finfo);
        }

        $size = strlen($fileContents);
        // --- FIN DE LA CORRECCIÓN ---

        // 2. Guardar el archivo localmente.
        $filePathDir = date('Ym');
        $fullPathDir = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePathDir;

        if (!is_dir($fullPathDir)) {
            mkdir($fullPathDir, 0755, true);
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $newFileName = uniqid() . ($extension ? ".$extension" : '');
        file_put_contents($fullPathDir . DIRECTORY_SEPARATOR . $newFileName, $fileContents);
        
        $relativePath = $filePathDir . DIRECTORY_SEPARATOR . $newFileName;
        $urlPath = url_contenido('media/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath));

        // 3. Devolver la estructura de datos completa.
        return [
            'provider'        => 'external', // Aunque es externo, lo estamos "localizando".
            'path'            => $relativePath,
            'url'             => $urlPath,
            'titulo'          => $data['titulo'],
            'mime_type'       => $mimeType,
            'size'            => $size,
            'nombre_original' => $originalName,
        ];
    }

    public function download(string $filePath): StreamInterface
    {
        // La implementación actual guarda los archivos externos localmente, por lo que la descarga es igual a la local.
        $fullPath = SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $filePath;
        if (!file_exists($fullPath)) {
            throw new \Exception('Archivo no encontrado.');
        }
        return fopen($fullPath, 'r');
    }
}