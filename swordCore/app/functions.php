<?php

/**
 * Archivo para funciones de ayuda (helpers) globales.
 * Se organiza incluyendo ficheros especializados desde la carpeta /helpers.
 */

require_once __DIR__ . '/helpers/core.php';
require_once __DIR__ . '/helpers/asset.php';
require_once __DIR__ . '/helpers/user.php';
require_once __DIR__ . '/helpers/theming.php';
require_once __DIR__ . '/helpers/hooks.php';

if (env('CMS_ENABLED', true)) {

    require_once __DIR__ . '/helpers/view.php';
    require_once __DIR__ . '/helpers/form.php';
    require_once __DIR__ . '/helpers/plugin.php';
    require_once __DIR__ . '/helpers/formPlugin.php';
    require_once __DIR__ . '/helpers/ajax.php';
    require_once __DIR__ . '/helpers/shortcode.php';
    require_once __DIR__ . '/helpers/dashboard.php';
    require_once __DIR__ . '/helpers/defaultContent.php';
    require_once __DIR__ . '/helpers/managedContent.php';
    require_once __DIR__ . '/helpers/iconos.php';
    require_once __DIR__ . '/helpers/server.php';
}
