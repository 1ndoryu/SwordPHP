<?php

namespace app\services;

class Vite
{
    private static $manifest = [];

    /**
     * Get the script tags for the Vite entry point.
     * Handles both Dev mode (HMR) and Production (Manifest).
     */
    public static function assets(string $entry = 'main.tsx')
    {
        if (self::isDev()) {
            return self::devTags($entry);
        }

        return self::prodTags($entry);
    }

    public static function isDev()
    {
        // Check if Vite Dev Server is reachable
        // You can customize this check or use an ENV variable
        static $status = null;
        if ($status !== null) return $status;

        // Use 127.0.0.1 instead of localhost to avoid IPv6 issues on Windows
        $handle = @fsockopen('127.0.0.1', 5173, $errno, $errstr, 0.5);
        if ($handle) {
            fclose($handle);
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    private static function devTags($entry)
    {
        // Base matches 'base' in vite.config.ts
        $baseUrl = "http://127.0.0.1:5173/static-admin/build/";
        $entryUrl = $baseUrl . $entry;

        return '
            <script type="module">
                import RefreshRuntime from "' . $baseUrl . '@react-refresh"
                RefreshRuntime.injectIntoGlobalHook(window)
                window.$RefreshReg$ = () => {}
                window.$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>
            <script type="module" src="' . $entryUrl . '"></script>';
    }

    private static function prodTags($entry)
    {
        $manifest = self::getManifest();

        if (!isset($manifest[$entry])) {
            return "<!-- Vite entry '$entry' not found -->";
        }

        $file = $manifest[$entry]['file'];
        $tags = '';

        // CSS handled by Vite in manifest
        if (isset($manifest[$entry]['css'])) {
            foreach ($manifest[$entry]['css'] as $cssFile) {
                $tags .= '<link rel="stylesheet" href="/static-admin/build/' . $cssFile . '">';
            }
        }

        $tags .= '<script type="module" src="/static-admin/build/' . $file . '"></script>';
        return $tags;
    }

    private static function getManifest()
    {
        if (!empty(self::$manifest)) return self::$manifest;

        // Path to manifest in public/static-admin/build/.vite/manifest.json
        // Adjust relative path as needed
        $path = __DIR__ . '/../../public/static-admin/build/.vite/manifest.json';

        if (file_exists($path)) {
            self::$manifest = json_decode(file_get_contents($path), true);
        }

        return self::$manifest;
    }
}
