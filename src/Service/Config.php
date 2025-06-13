<?php

declare(strict_types=1);

namespace App\Service;

use DirectoryIterator;

class Config
{
    private array $items = [];

    public function __construct(string $configPath)
    {
        if (!is_dir($configPath)) {
            // En un futuro, aquí podríamos lanzar una excepción o un log.
            return;
        }

        $files = new DirectoryIterator($configPath);

        foreach ($files as $file) {
            if ($file->isDot() || $file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $key = $file->getBasename('.php');
            $this->items[$key] = require $file->getRealPath();
        }
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}
