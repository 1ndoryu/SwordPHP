<?php

if (!function_exists('forzarReinicioServidor')) {
    /**
     * Reinicia los workers del servidor de forma inteligente según el modo de ejecución.
     */
    function forzarReinicioServidor(): void
    {
        // Workerman\Worker::getArgv() devuelve los argumentos con los que se inició el servidor.
        // Comprobamos si el flag '-d' está presente para detectar el modo daemon.
        $esModoDaemon = in_array('-d', \Workerman\Worker::getArgv());

        if ($esModoDaemon) {
            // MODO DAEMON (PRODUCCIÓN): La forma correcta es ejecutar `php start.php reload`
            \support\Log::info("HELPER: Modo DAEMON detectado. Intentando reinicio vía 'shell_exec'.");

            // Construimos la ruta al script de inicio y al ejecutable de PHP de forma dinámica.
            $startFile = base_path('start.php');
            $phpPath = PHP_BINARY; // Constante de PHP que apunta al binario en uso.

            // Validamos que shell_exec esté disponible.
            if (!function_exists('shell_exec')) {
                \support\Log::error("HELPER: 'shell_exec' está deshabilitado en php.ini. No se puede reiniciar el servidor en modo DAEMON.");
                return;
            }

            // Ejecutamos el comando de reinicio en segundo plano.
            shell_exec("{$phpPath} {$startFile} reload");
            \support\Log::info("HELPER: Comando 'reload' ejecutado. El servidor debería reiniciarse.");
        } else {
            // MODO DEBUG: Usamos el monitor de archivos, que es seguro en este entorno.
            \support\Log::info("HELPER: Modo DEBUG detectado. Intentando reinicio vía monitor de archivos (touch).");

            $monitorOptions = config('process.monitor.constructor.options', []);
            if (empty($monitorOptions['enable_file_monitor'])) {
                \support\Log::warning("HELPER: El reinicio automático está deshabilitado para el modo DEBUG.");
                return;
            }

            // Tocar un archivo de configuración es una manera estándar en webman-php de forzar la recarga
            $fileToTouch = config_path('app.php');
            if (@touch($fileToTouch)) {
                \support\Log::info("HELPER: Reinicio solicitado correctamente vía touch.");
            } else {
                \support\Log::error("HELPER: Fallo al solicitar reinicio vía touch. ¿Problemas de permisos?");
            }
        }
    }
}
