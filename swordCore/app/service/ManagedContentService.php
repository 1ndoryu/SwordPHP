<?php

namespace App\service;

use App\model\Pagina;

/**
 * Servicio para gestionar contenido definido por código (páginas, etc.).
 * Su ciclo de vida es gestionado por el contenedor de inyección de dependencias.
 */
class ManagedContentService
{
    /**
     * Almacena las definiciones de contenido.
     * La clave es un slug único que identifica la definición.
     * @var array<string, array>
     */
    private array $definiciones = [];

    /**
     * El constructor ahora es público para permitir la instanciación
     * por parte del contenedor de dependencias.
     */
    public function __construct() {}

    /**
     * Registra una página o cualquier tipo de contenido.
     *
     * @param string $slugDefinicion Identificador único para esta definición.
     * @param array $argumentos Los datos de la página/contenido.
     * @return void
     */
    public function registrarContenido(string $slugDefinicion, array $argumentos): void
    {
        if (!isset($this->definiciones[$slugDefinicion])) {
            $this->definiciones[$slugDefinicion] = $argumentos;
        }
    }
    
    /**
     * Obtiene una definición de contenido específica por su slug.
     *
     * @param string $slugDefinicion
     * @return array|null
     */
    public function obtenerDefinicion(string $slugDefinicion): ?array
    {
        return $this->definiciones[$slugDefinicion] ?? null;
    }

    /**
     * Lógica principal que se ejecuta en el panel de admin para sincronizar.
     * Compara el contenido definido en código con el de la BD.
     */
    public function sincronizar(): void
    {
        // 1. Obtener todas las páginas gestionadas existentes en la BD.
        $paginasGestionadasEnDB = Pagina::whereNotNull('metadata->_managed_source_slug')->get()->keyBy(function ($item) {
            return $item->obtenerMeta('_managed_source_slug');
        });

        $definicionesRegistradas = $this->definiciones;

        // 2. RECONCILIAR: Crear las que faltan.
        foreach ($definicionesRegistradas as $slugDef => $args) {
            if (!$paginasGestionadasEnDB->has($slugDef)) {
                $this->crearEntradaDesdeDefinicion($slugDef, $args);
            }
        }

        // 3. RECONCILIAR: Borrar las que sobran.
        foreach ($paginasGestionadasEnDB as $slugDB => $pagina) {
            if (!isset($definicionesRegistradas[$slugDB])) {
                $pagina->delete();
            }
        }
    }

    /**
     * Crea una nueva entrada en la base de datos a partir de una definición.
     *
     * @param string $slugDefinicion
     * @param array $args
     */
    private function crearEntradaDesdeDefinicion(string $slugDefinicion, array $args): void
    {
        $paginaService = container(PaginaService::class); // Usamos el container para obtener el servicio
        $slugPagina = $args['slug'] ?? $slugDefinicion;
        
        // Prevenir colisiones de slug
        $slugFinal = $paginaService->asegurarSlugUnico($slugPagina);

        $datosParaCrear = [
            'titulo'        => $args['titulo'] ?? 'Sin Título',
            'contenido'     => $args['contenido'] ?? '',
            'tipocontenido' => $args['tipo_contenido'] ?? 'pagina',
            'estado'        => $args['estado'] ?? 'publicado',
            'slug'          => $slugFinal,
            'metadata'      => $args['metadata'] ?? []
        ];

        // Añadimos el metadato clave que lo identifica como gestionado.
        $datosParaCrear['metadata']['_managed_source_slug'] = $slugDefinicion;
        
        // Si se especifica una plantilla, se añade a los metadatos.
        if (isset($args['plantilla'])) {
            $datosParaCrear['metadata']['_plantilla_pagina'] = $args['plantilla'];
        }

        $paginaService->crearPagina($datosParaCrear);
    }
}