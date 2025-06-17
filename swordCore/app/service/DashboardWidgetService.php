<?php

namespace App\service;

/**
 * Servicio para gestionar los widgets del dashboard.
 * Implementa el patrón Singleton para asegurar un registro único.
 */
class DashboardWidgetService
{
    private static ?self $instancia = null;

    /**
     * Almacena los widgets registrados.
     * La estructura es: ['id_widget' => ['titulo' => string, 'columna' => int, 'prioridad' => int, 'callback' => callable]]
     * @var array<string, array>
     */
    private array $widgets = [];

    private function __construct() {}
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar un singleton.");
    }

    /**
     * Obtiene la instancia única del servicio.
     * @return self
     */
    public static function getInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Registra un nuevo widget en el dashboard.
     *
     * @param string $id El identificador único del widget.
     * @param string $titulo El título que se mostrará en la cabecera del widget.
     * @param callable $callback La función que renderiza el contenido HTML del widget.
     * @param int $columna La columna donde se mostrará (1 o 2).
     * @param int $prioridad Orden de aparición (menor número, más arriba).
     */
    public function registrar(string $id, string $titulo, callable $callback, int $columna = 1, int $prioridad = 10): void
    {
        $this->widgets[$id] = [
            'titulo'    => $titulo,
            'callback'  => $callback,
            'columna'   => ($columna === 2) ? 2 : 1, // Asegura que solo sea 1 o 2
            'prioridad' => $prioridad,
        ];
    }

    /**
     * Obtiene todos los widgets registrados, ordenados por columna y prioridad.
     *
     * @return array<int, array> Un array con dos sub-arrays (índices 0 y 1 para columnas 1 y 2),
     * cada uno con sus widgets ordenados.
     */
    public function obtenerWidgetsOrdenados(): array
    {
        $columna1 = [];
        $columna2 = [];

        foreach ($this->widgets as $id => $widget) {
            $widgetConId = ['id' => $id] + $widget;
            if ($widget['columna'] === 1) {
                $columna1[] = $widgetConId;
            } else {
                $columna2[] = $widgetConId;
            }
        }

        // Función de comparación para ordenar por prioridad
        $ordenarPorPrioridad = fn($a, $b) => $a['prioridad'] <=> $b['prioridad'];

        // Ordenar cada columna
        usort($columna1, $ordenarPorPrioridad);
        usort($columna2, $ordenarPorPrioridad);

        return [$columna1, $columna2];
    }
}