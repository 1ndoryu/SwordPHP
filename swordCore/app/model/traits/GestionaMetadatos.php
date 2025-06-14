<?php

namespace App\model\traits;

/**
 * Trait GestionaMetadatos
 *
 * Proporciona métodos para gestionar metadatos asociados a un modelo de Eloquent.
 * El modelo que use este trait debe implementar una relación `metas()`.
 */
trait GestionaMetadatos
{
    /**
     * Guarda o actualiza un metadato para el modelo.
     * Si el valor es un array o un objeto, se serializa automáticamente.
     * Esta función sobrescribe cualquier valor previo para la misma clave.
     *
     * @param string $metaKey La clave del metadato.
     * @param mixed $metaValue El valor del metadato.
     * @return \Illuminate\Database\Eloquent\Model El modelo de metadato creado o actualizado.
     */
    public function guardarMeta(string $metaKey, $metaValue)
    {
        $valor = is_array($metaValue) || is_object($metaValue) ? serialize($metaValue) : $metaValue;

        return $this->metas()->updateOrCreate(
            ['meta_key' => $metaKey],
            ['meta_value' => $valor]
        );
    }

    /**
     * Obtiene un metadato del modelo.
     * Deserializa el valor si es necesario.
     *
     * @param string $metaKey La clave del metadato a obtener.
     * @param bool $single Si es true, devuelve un solo valor; de lo contrario, una colección de valores.
     * @return mixed Null si no se encuentra, o el valor/colección de valores.
     */
    public function obtenerMeta(string $metaKey, bool $single = true)
    {
        // Eager load metas si no están cargadas para optimizar
        if (!$this->relationLoaded('metas')) {
            $this->load('metas');
        }

        $metas = $this->metas->where('meta_key', $metaKey);

        if ($metas->isEmpty()) {
            return $single ? null : collect();
        }

        $values = $metas->pluck('meta_value')->map(function ($value) {
            return $this->esSerializado($value) ? unserialize($value) : $value;
        });

        return $single ? $values->first() : $values;
    }

    /**
     * Elimina todos los metadatos de un modelo que coincidan con una clave.
     *
     * @param string $metaKey La clave del metadato a eliminar.
     * @return bool True si se eliminó algún registro, false en caso contrario.
     */
    public function eliminarMeta(string $metaKey): bool
    {
        return (bool) $this->metas()->where('meta_key', $metaKey)->delete();
    }

    /**
     * Comprueba si una cadena de texto está serializada.
     *
     * @param mixed $data
     * @return bool
     */
    private function esSerializado($data): bool
    {
        if (!is_string($data) || trim($data) === '') {
            return false;
        }
        // El error se suprime con @ para evitar E_NOTICE con data inválida.
        // Se compara estrictamente con false porque una cadena serializada de `false` es `b:0;`
        return @unserialize($data) !== false || $data === 'b:0;';
    }
}
