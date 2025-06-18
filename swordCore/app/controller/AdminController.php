<?php

namespace App\controller;

use App\service\DashboardWidgetService;
use support\Request;
use Webman\Http\Response;

class AdminController
{
    /**
     * Muestra la página principal del panel de administración (dashboard).
     *
     * @param Request $request
     * @return Response
     */
    public function inicio(Request $request): Response
    {
        // 1. Registrar widgets (eventualmente, esto lo harán los plugins y el núcleo a través de hooks).

        // Widget de Bienvenida de ejemplo.
        agregarWidgetDashboard(
            'bienvenida_sword',
            'Bienvenido a SwordPHP',
            function () {
                echo '<p>Este es el panel de administración de tu nuevo sitio. Desde aquí puedes gestionar tu contenido. Esta es una demostración del nuevo sistema de widgets del dashboard.</p>';
            },
            1, // Columna
            1  // Prioridad (el número más bajo se muestra primero)
        );

        // Widget de marcador de posición para futuras actualizaciones.
        agregarWidgetDashboard(
            'actualizaciones_sword',
            'Actualizaciones',
            function () {
                echo '<p>Buscando actualizaciones de SwordPHP...</p>';
                echo '<p style="opacity: 0.7;">(Este es un widget de ejemplo. La funcionalidad real se implementará más adelante.)</p>';
            },
            2, // Columna
            5  // Prioridad
        );

        // 2. Obtener todos los widgets registrados y ordenados desde el servicio.
        $widgetService = DashboardWidgetService::getInstancia();
        $widgetsPorColumna = $widgetService->obtenerWidgetsOrdenados();

        // 3. Pasar los widgets a la vista para su renderización.
        return view('admin.inicio', [
            'tituloPagina' => 'Inicio',
            'widgetsColumna1' => $widgetsPorColumna[0],
            'widgetsColumna2' => $widgetsPorColumna[1],
        ]);
    }
}