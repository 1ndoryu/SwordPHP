<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class XdebugProfiler implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // Definimos un "disparador secreto" que solo nosotros conocemos.
        // Puede ser un parámetro en la URL, una cabecera, o una cookie.
        // Usaremos un parámetro en la URL por simplicidad: ?_profile=1

        $isProfiling = $request->get('_profile');

        if ($isProfiling && function_exists('xdebug_start_profiling')) {
            // Si el parámetro ?_profile=1 existe y la función de Xdebug está disponible...

            // 1. Iniciamos el perfilado. Podemos darle un nombre único al archivo.
            xdebug_start_profiling(uniqid('profile_'));

            // 2. Dejamos que la aplicación maneje la petición normalmente.
            $response = $handler($request);

            // 3. Una vez que la aplicación ha terminado, detenemos el perfilado.
            xdebug_stop_profiling();

            // 4. Devolvemos la respuesta al usuario.
            return $response;
        }

        // Si no se activó el trigger, simplemente pasamos a la siguiente fase sin hacer nada.
        return $handler($request);
    }
}
