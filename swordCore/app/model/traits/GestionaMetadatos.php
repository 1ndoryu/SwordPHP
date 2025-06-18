<?php

namespace App\model\traits;

/**
 * Trait GestionaMetadatos
 *
 * Proporciona métodos para gestionar metadatos almacenados en una columna JSONB.
 * El modelo que use este trait debe tener una propiedad pública `$casts` que defina
 * la columna 'metadata' como 'array'.
 */
trait GestionaMetadatos
{
    /**
     * Guarda o actualiza un metadato específico.
     *
     * @param string $clave La clave del metadato.
     * @param mixed $valor El valor a guardar.
     * @return bool
     */
    public function guardarMeta(string $clave, $valor): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata[$clave] = $valor;
        $this->metadata = $metadata;
        // El guardado se delega al controlador para hacerlo en una sola operación.
        return true; 
    }

    /**
     * Obtiene un metadato.
     *
     * @param string $clave La clave del metadato.
     * @param mixed|null $porDefecto Valor a devolver si la clave no existe.
     * @return mixed
     */
    public function obtenerMeta(string $clave, $porDefecto = null)
    {
        // El 'cast' de Eloquent ya ha decodificado el JSON a un array.
        return $this->metadata[$clave] ?? $porDefecto;
    }

    /**
     * Elimina un metadato.
     *
     * @param string $clave La clave a eliminar.
     * @return bool Devuelve true si la clave existía y fue eliminada.
     */
    public function eliminarMeta(string $clave): bool
    {
        $metadata = $this->metadata ?? [];
        if (array_key_exists($clave, $metadata)) {
            unset($metadata[$clave]);
            $this->metadata = $metadata;
            return true;
        }
        return false;
    }
    
    /**
     * Reemplaza todos los metadatos con un nuevo conjunto.
     * Ideal para sincronizar con los datos de un formulario.
     *
     * @param array $nuevosMetadatos El array completo de nuevos metadatos.
     */
    public function sincronizarMetas(array $nuevosMetadatos): void
    {
        $this->metadata = $nuevosMetadatos;
    }
}