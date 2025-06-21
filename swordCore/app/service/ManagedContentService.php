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

        // 4. LÓGICA DE PÁGINA DE INICIO: Establecer si es necesario.
        $this->sincronizarPaginaDeInicio();
    }
    /**
     * Establece la página de inicio definida en el código si no hay una configurada
     * o si la página de inicio actual fue eliminada.
     */
    private function sincronizarPaginaDeInicio(): void
    {
        $opcionService = container(OpcionService::class);
        $paginaInicioActualSlug = $opcionService->getOption('pagina_de_inicio_slug');

        $procederASetearHomepage = false;
        if (empty($paginaInicioActualSlug)) {
            // No hay página de inicio configurada, podemos establecer una.
            $procederASetearHomepage = true;
        } else {
            // Hay una configurada, comprobamos si todavía existe.
            if (!Pagina::where('slug', $paginaInicioActualSlug)->exists()) {
                // La página de inicio actual ya no existe, podemos establecer una nueva.
                $procederASetearHomepage = true;
            }
        }

        if ($procederASetearHomepage) {
            $slugPaginaInicioDefinida = null;
            foreach ($this->definiciones as $slugDef => $args) {
                // Buscamos la primera definición marcada como 'es_inicio'.
                if (!empty($args['es_inicio']) && $args['es_inicio'] === true && ($args['tipo_contenido'] ?? 'pagina') === 'pagina') {
                    // La página ya debe existir en la BD, la buscamos por su slug de definición.
                    $pagina = Pagina::where('metadata->_managed_source_slug', $slugDef)->first();
                    if ($pagina) {
                        $slugPaginaInicioDefinida = $pagina->slug;
                        break; // Nos quedamos con la primera que encontremos.
                    }
                }
            }

            if ($slugPaginaInicioDefinida) {
                $opcionService->updateOption('pagina_de_inicio_slug', $slugPaginaInicioDefinida);
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
            'titulo'    => $args['titulo'] ?? 'Sin Título',
            'contenido'  => $args['contenido'] ?? '',
            'tipocontenido' => $args['tipo_contenido'] ?? 'pagina',
            'estado'    => $args['estado'] ?? 'publicado',
            'slug'     => $slugFinal,
            'metadata'   => $args['metadata'] ?? []
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
