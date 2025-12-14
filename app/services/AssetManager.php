<?php

namespace app\services;

class AssetManager
{
    /**
     * Devuelve la URL del activo con versionado automático.
     * En desarrollo (o si cambia el archivo) usa filemtime.
     *
     * @param string $path Ruta relativa desde public/ (ej: 'admin/css/style.css')
     * @return string
     */
    public static function url(string $path): string
    {
        $publicPath = public_path();
        $filePath = $publicPath . '/' . ltrim($path, '/');

        $version = '';

        if (file_exists($filePath)) {
            // Usamos la fecha de modificación del archivo para caché busting eficiente
            $version = '?v=' . filemtime($filePath);
        } else {
            // Si no existe el archivo, usamos timestamp actual si queremos debug, o nada.
            // Para asegurar cache busting en dev si el archivo falla, o evitar cache agresivo.
            $version = '?v=' . time();
        }

        return '/' . ltrim($path, '/') . $version;
    }

    /**
     * Genera etiqueta <link> para CSS
     */
    public static function css(string $path): string
    {
        $url = self::url($path);
        return '<link rel="stylesheet" href="' . $url . '">';
    }

    /**
     * Genera etiqueta <script> para JS
     */
    public static function js(string $path, bool $defer = true): string
    {
        $url = self::url($path);
        $attr = $defer ? 'defer' : '';
        return '<script src="' . $url . '" ' . $attr . '></script>';
    }
}
