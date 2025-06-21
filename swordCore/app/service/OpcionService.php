<?php

namespace App\service;

use App\model\Opcion;

/**
 * Servicio para gestionar las opciones o ajustes generales del sitio.
 * Interactúa con la tabla 'opciones' para guardar y recuperar configuraciones.
 */
class OpcionService
{
    /**
     * Cache estático para las opciones cargadas durante una única petición.
     * @var array<string, mixed>
     */
    private static array $cacheOpciones = [];

    /**
     * Obtiene el valor de una opción de la base de datos.
     * Utiliza un caché para evitar consultas repetidas en la misma petición.
     *
     * @param string $nombre El nombre de la opción (ej: 'pagina_de_inicio').
     * @param mixed $valorPorDefecto El valor a devolver si la opción no se encuentra.
     * @return mixed
     */
    public function getOption(string $nombre, $valorPorDefecto = null)
    {
        if (array_key_exists($nombre, self::$cacheOpciones)) {
            return self::$cacheOpciones[$nombre];
        }

        $opcion = Opcion::where('opcion_nombre', $nombre)->first();

        if (!$opcion) {
            self::$cacheOpciones[$nombre] = $valorPorDefecto;
            return $valorPorDefecto;
        }

        $valor = $this->intentarDeserializar($opcion->opcion_valor);

        self::$cacheOpciones[$nombre] = $valor;
        return $valor;
    }

    /**
     * Guarda o actualiza el valor de una opción.
     * Si el valor es un array o un objeto, se serializa automáticamente.
     *
     * @param string $nombre El nombre de la opción.
     * @param mixed $valor El valor a guardar.
     * @return bool
     */
    public function updateOption(string $nombre, $valor): bool
    {
        $valorAGuardar = is_array($valor) || is_object($valor) ? serialize($valor) : $valor;

        Opcion::updateOrCreate(
            ['opcion_nombre' => $nombre],
            ['opcion_valor' => $valorAGuardar]
        );

        self::$cacheOpciones[$nombre] = $valor;

        return true;
    }

    /**
     * Comprueba si un valor es una cadena serializada y la deserializa si es posible.
     *
     * @param mixed $valor
     * @return mixed
     */
    private function intentarDeserializar($valor)
    {
        if (!is_string($valor)) {
            return $valor;
        }

        $data = @unserialize($valor);
        if ($data !== false || $valor === 'b:0;') {
            return $data;
        }

        return $valor;
    }
}
