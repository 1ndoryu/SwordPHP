<?php

use App\service\ManagedContentService;

if (!function_exists('swDefinirPagina')) {
    /**
     * Helper para definir una página que será gestionada por el núcleo.
     *
     * @param string $slugDefinicion Un identificador único para esta definición de página.
     * @param array $argumentos Argumentos que definen la página.
     * - 'slug' (string, opcional): El slug de la URL. Si se omite, se usa $slugDefinicion.
     * - 'titulo' (string): El título de la página.
     * - 'contenido' (string, opcional): El contenido HTML de la página.
     * - 'estado' (string, opcional): 'publicado' o 'borrador'. Por defecto 'publicado'.
     * - 'plantilla' (string, opcional): El nombre del archivo de la plantilla (ej: 'mi-plantilla.php').
     * - 'metadata' (array, opcional): Un array de metadatos personalizados.
     */
    function swDefinirPagina(string $slugDefinicion, array $argumentos = [])
    {
        $argumentos['tipo_contenido'] = 'pagina';
        // CORREGIDO: Usamos el helper container() para obtener la instancia del servicio
        container(ManagedContentService::class)->registrarContenido($slugDefinicion, $argumentos);
    }
}

if (!function_exists('swDefinirContenido')) {
    /**
     * Helper para definir una entrada de cualquier tipo de contenido.
     *
     * @param string $tipo El slug del tipo de contenido (ej: 'entradas').
     * @param string $slugDefinicion Un identificador único para esta definición.
     * @param array $argumentos Los argumentos que definen la entrada.
     */
    function swDefinirContenido(string $tipo, string $slugDefinicion, array $argumentos)
    {
        $argumentos['tipo_contenido'] = $tipo;
        // CORREGIDO: Usamos el helper container() para obtener la instancia del servicio
        container(ManagedContentService::class)->registrarContenido($slugDefinicion, $argumentos);
    }
}

// Conectamos el motor de sincronización al hook del panel de administración.
// Esto asegura que la sincronización se ejecute cada vez que se carga el panel.
// Usamos una función anónima para obtener el servicio desde el contenedor en el momento de la ejecución.
addAction('swInitAdmin', function() {
    container(ManagedContentService::class)->sincronizar();
});