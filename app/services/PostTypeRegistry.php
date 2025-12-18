<?php

namespace app\services;

use app\model\Content;

/**
 * Registro de Post Types.
 * Combina tipos predefinidos en codigo con tipos detectados automaticamente desde la BD.
 */
class PostTypeRegistry
{
    /**
     * Post Types registrados manualmente.
     * @var array<string, array>
     */
    private static array $postTypes = [];

    /**
     * Post Types detectados desde la BD.
     * @var array<string, array>
     */
    private static array $postTypesDinamicos = [];

    /**
     * Indica si ya se inicializaron los tipos por defecto.
     */
    private static bool $initialized = false;

    /**
     * Indica si la detección de dinámicos fue exitosa.
     * Si es false, se reintentará en la próxima llamada.
     */
    private static bool $dinamicosDetectadosExitoso = false;

    /**
     * Inicializa los Post Types por defecto (predefinidos en codigo).
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::register('post', [
            'nombre' => 'Entradas',
            'nombreSingular' => 'Entrada',
            'icono' => 'file-text',
            'descripcion' => 'Publicaciones del blog',
            'soporta' => ['titulo', 'editor', 'extracto', 'miniatura'],
            'publico' => true,
            'enMenu' => true,
            'orden' => 10,
            'esPredefinido' => true
        ]);

        self::register('page', [
            'nombre' => 'Paginas',
            'nombreSingular' => 'Pagina',
            'icono' => 'file',
            'descripcion' => 'Paginas estaticas del sitio',
            'soporta' => ['titulo', 'editor', 'miniatura'],
            'publico' => true,
            'enMenu' => true,
            'jerarquico' => true,
            'orden' => 20,
            'esPredefinido' => true
        ]);

        self::$initialized = true;
    }

    /**
     * Detecta Post Types desde la base de datos.
     * Registra automaticamente cualquier tipo que exista en contents pero no este predefinido.
     * Si la deteccion falla, se reintentara en la proxima llamada.
     */
    public static function detectarDinamicos(): void
    {
        // Si ya detectamos exitosamente, no reintentar
        if (self::$dinamicosDetectadosExitoso && !empty(self::$postTypesDinamicos)) {
            return;
        }

        self::init();

        try {
            $tiposEnBd = Content::select('type')
                ->distinct()
                ->pluck('type')
                ->toArray();

            $orden = 100;
            foreach ($tiposEnBd as $tipo) {
                if (!isset(self::$postTypes[$tipo]) && !isset(self::$postTypesDinamicos[$tipo])) {
                    self::$postTypesDinamicos[$tipo] = [
                        'slug' => $tipo,
                        'nombre' => self::formatearNombre($tipo, true),
                        'nombreSingular' => self::formatearNombre($tipo, false),
                        'icono' => 'folder',
                        'descripcion' => "Contenido tipo {$tipo} (detectado automaticamente)",
                        'soporta' => ['titulo', 'editor'],
                        'publico' => true,
                        'enMenu' => true,
                        'jerarquico' => false,
                        'orden' => $orden,
                        'campos' => [],
                        'esPredefinido' => false,
                        'esDinamico' => true
                    ];
                    $orden += 10;
                }
            }

            // Marcar como exitoso solo si la consulta funciono
            self::$dinamicosDetectadosExitoso = true;
        } catch (\Throwable $e) {
            // Log del error para depuracion, se reintentara la proxima vez
            error_log("PostTypeRegistry::detectarDinamicos() error: " . $e->getMessage());
        }
    }

    /**
     * Formatea un slug a nombre legible.
     * Ej: audio_sample -> Audio Samples / Audio Sample
     */
    private static function formatearNombre(string $slug, bool $plural): string
    {
        $nombre = str_replace(['_', '-'], ' ', $slug);
        $nombre = ucwords($nombre);

        if ($plural && !str_ends_with(strtolower($nombre), 's')) {
            $nombre .= 's';
        }

        return $nombre;
    }

    /**
     * Registra un nuevo Post Type manualmente.
     */
    public static function register(string $slug, array $config): void
    {
        $defaults = [
            'nombre' => ucfirst($slug),
            'nombreSingular' => ucfirst($slug),
            'icono' => 'file',
            'descripcion' => '',
            'soporta' => ['titulo', 'editor'],
            'publico' => true,
            'enMenu' => true,
            'jerarquico' => false,
            'orden' => 100,
            'campos' => [],
            'esPredefinido' => false,
            'esDinamico' => false
        ];

        self::$postTypes[$slug] = array_merge($defaults, $config, ['slug' => $slug]);
    }

    /**
     * Obtiene un Post Type por su slug.
     */
    public static function get(string $slug): ?array
    {
        self::init();
        self::detectarDinamicos();

        return self::$postTypes[$slug]
            ?? self::$postTypesDinamicos[$slug]
            ?? null;
    }

    /**
     * Obtiene todos los Post Types (predefinidos + dinamicos).
     */
    public static function all(): array
    {
        self::init();
        self::detectarDinamicos();

        return array_merge(self::$postTypes, self::$postTypesDinamicos);
    }

    /**
     * Obtiene los Post Types que deben mostrarse en el menu del admin.
     */
    public static function paraMenu(): array
    {
        self::init();
        self::detectarDinamicos();

        $todos = array_merge(self::$postTypes, self::$postTypesDinamicos);
        $enMenu = array_filter($todos, fn($tipo) => $tipo['enMenu'] === true);

        uasort($enMenu, fn($a, $b) => $a['orden'] <=> $b['orden']);

        return $enMenu;
    }

    /**
     * Verifica si existe un Post Type (predefinido o dinamico).
     */
    public static function existe(string $slug): bool
    {
        self::init();
        self::detectarDinamicos();

        return isset(self::$postTypes[$slug]) || isset(self::$postTypesDinamicos[$slug]);
    }

    /**
     * Obtiene los slugs de todos los Post Types.
     */
    public static function slugs(): array
    {
        self::init();
        self::detectarDinamicos();

        return array_keys(array_merge(self::$postTypes, self::$postTypesDinamicos));
    }

    /**
     * Fuerza la re-deteccion de tipos dinamicos.
     * Util despues de crear nuevo contenido con un tipo nuevo.
     */
    public static function refrescar(): void
    {
        self::$dinamicosDetectadosExitoso = false;
        self::$postTypesDinamicos = [];
    }
}
