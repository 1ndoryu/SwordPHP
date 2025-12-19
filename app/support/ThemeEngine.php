<?php

namespace app\support;

use app\model\Option;
use support\Log;

/**
 * Motor de Temas para SwordPHP
 * 
 * Maneja la carga, renderizado y gestión de temas.
 * Soporta tres modos: PHP puro, SSG y SSR.
 */
class ThemeEngine
{
    private static ?ThemeEngine $instancia = null;

    private ?string $temaActivo = null;
    private ?array $configuracionTema = null;
    private string $rutaTemas;

    private function __construct()
    {
        $this->rutaTemas = base_path() . '/themes';
        $this->cargarTemaActivo();
    }

    public static function instancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Carga el tema activo desde la base de datos
     */
    private function cargarTemaActivo(): void
    {
        try {
            $temaGuardado = get_option('tema_activo', 'developer');

            if ($this->temaExiste($temaGuardado)) {
                $this->temaActivo = $temaGuardado;
                $this->configuracionTema = $this->cargarConfiguracion($temaGuardado);
                $this->cargarFuncionesTema();
            } else {
                Log::warning("Tema '{$temaGuardado}' no encontrado, usando 'developer'");
                $this->temaActivo = 'developer';
                $this->configuracionTema = $this->cargarConfiguracion('developer');
                $this->cargarFuncionesTema();
            }
        } catch (\Throwable $e) {
            Log::error("Error cargando tema: " . $e->getMessage());
            $this->temaActivo = 'developer';
        }
    }

    /**
     * Verifica si un tema existe
     */
    public function temaExiste(string $nombre): bool
    {
        $ruta = $this->rutaTemas . '/' . $nombre;
        return is_dir($ruta) && file_exists($ruta . '/theme.json');
    }

    /**
     * Carga la configuración de un tema desde theme.json
     */
    public function cargarConfiguracion(string $nombre): ?array
    {
        $archivoConfig = $this->rutaTemas . '/' . $nombre . '/theme.json';

        if (!file_exists($archivoConfig)) {
            return null;
        }

        $contenido = file_get_contents($archivoConfig);
        $config = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Error parseando theme.json de '{$nombre}': " . json_last_error_msg());
            return null;
        }

        return $config;
    }

    /**
     * Carga el archivo functions.php del tema
     */
    private function cargarFuncionesTema(): void
    {
        $archivoFunciones = $this->obtenerRutaTema() . '/functions.php';

        if (file_exists($archivoFunciones)) {
            require_once $archivoFunciones;
        }
    }

    /**
     * Obtiene el nombre del tema activo
     */
    public function obtenerTemaActivo(): string
    {
        return $this->temaActivo ?? 'developer';
    }

    /**
     * Obtiene la configuración del tema activo
     */
    public function obtenerConfiguracion(): ?array
    {
        return $this->configuracionTema;
    }

    /**
     * Obtiene la ruta completa al tema activo
     */
    public function obtenerRutaTema(): string
    {
        return $this->rutaTemas . '/' . $this->temaActivo;
    }

    /**
     * Obtiene la ruta a las plantillas del tema
     */
    public function obtenerRutaPlantillas(): string
    {
        return $this->obtenerRutaTema() . '/templates';
    }

    /**
     * Lista todos los temas instalados
     */
    public function listarTemas(): array
    {
        $temas = [];
        $directorios = glob($this->rutaTemas . '/*', GLOB_ONLYDIR);

        foreach ($directorios as $dir) {
            $nombre = basename($dir);
            $config = $this->cargarConfiguracion($nombre);

            if ($config !== null) {
                /* Construir URL del screenshot si existe */
                $screenshotUrl = null;
                if (!empty($config['screenshot'])) {
                    $screenshotPath = $dir . '/' . $config['screenshot'];
                    if (file_exists($screenshotPath)) {
                        $screenshotUrl = '/themes/' . $nombre . '/' . $config['screenshot'];
                    }
                }

                $temas[] = [
                    'slug' => $nombre,
                    'nombre' => $config['name'] ?? $nombre,
                    'version' => $config['version'] ?? '1.0.0',
                    'autor' => $config['author'] ?? 'Desconocido',
                    'descripcion' => $config['description'] ?? '',
                    'screenshot' => $screenshotUrl,
                    'modo' => $config['mode'] ?? 'php',
                    'activo' => $nombre === $this->temaActivo
                ];
            }
        }

        return $temas;
    }

    /**
     * Activa un tema
     */
    public function activarTema(string $nombre): bool
    {
        if (!$this->temaExiste($nombre)) {
            return false;
        }

        try {
            Option::updateOrCreate(
                ['key' => 'tema_activo'],
                ['value' => $nombre]
            );

            $this->temaActivo = $nombre;
            $this->configuracionTema = $this->cargarConfiguracion($nombre);
            $this->cargarFuncionesTema();

            return true;
        } catch (\Throwable $e) {
            Log::error("Error activando tema '{$nombre}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resuelve la plantilla a usar según la jerarquía
     * 
     * @param string $tipo Tipo de contenido (page, post, etc.)
     * @param string|null $slug Slug del contenido
     * @param int|null $id ID del contenido
     * @return string|null Ruta a la plantilla encontrada
     */
    public function resolverPlantilla(string $tipo, ?string $slug = null, ?int $id = null): ?string
    {
        $rutaPlantillas = $this->obtenerRutaPlantillas();
        $candidatas = [];

        if ($tipo === 'page') {
            if ($slug) {
                $candidatas[] = "page-{$slug}.php";
            }
            if ($id) {
                $candidatas[] = "page-{$id}.php";
            }
            $candidatas[] = 'page.php';
            $candidatas[] = 'single.php';
        } elseif ($tipo === 'archive') {
            if ($slug) {
                $candidatas[] = "archive-{$slug}.php";
            }
            $candidatas[] = 'archive.php';
        } else {
            if ($slug) {
                $candidatas[] = "single-{$tipo}-{$slug}.php";
            }
            $candidatas[] = "single-{$tipo}.php";
            $candidatas[] = 'single.php';
        }

        $candidatas[] = 'index.php';

        foreach ($candidatas as $plantilla) {
            $ruta = $rutaPlantillas . '/' . $plantilla;
            if (file_exists($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    /**
     * Renderiza una plantilla con los datos proporcionados
     */
    public function renderizar(string $rutaPlantilla, array $datos = []): string
    {
        if (!file_exists($rutaPlantilla)) {
            Log::error("Plantilla no encontrada: {$rutaPlantilla}");
            return '';
        }

        ob_start();
        extract($datos);

        /* Variables globales disponibles en todas las plantillas */
        $tema = $this;

        require $rutaPlantilla;

        return ob_get_clean();
    }

    /**
     * Obtiene la URL base del tema para assets
     */
    public function obtenerUrlAssets(): string
    {
        return '/themes/' . $this->temaActivo . '/assets';
    }

    /**
     * Obtiene el modo de renderizado del tema
     */
    public function obtenerModo(): string
    {
        return $this->configuracionTema['mode'] ?? 'php';
    }
}
