<?php

/**
 * Vista principal.
 *
 * @var string $tiempoCarga El tiempo de carga de la página formateado.
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Proyecto Webman</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: grid;
            place-content: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
            color: #374151;
        }

        .container {
            padding: 2rem 3rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>¡Bienvenido!</h1>
        <p>Página cargada en <?= htmlspecialchars($tiempoCarga, ENT_QUOTES, 'UTF-8') ?> ms.</p>
    </div>

</body>

</html>