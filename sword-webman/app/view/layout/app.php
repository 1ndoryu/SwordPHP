<?php
/**
 * Layout principal de la aplicación.
 *
 * @var string $titulo    El título de la página (se pasa desde el controlador).
 * @var string $contenido El HTML del contenido principal (se renderiza desde una vista).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Mi Proyecto', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
            color: #374151;
        }
        main {
            flex-grow: 1;
            display: grid;
            place-content: center;
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

    <main>
        <?= $contenido ?? '' ?>
    </main>

</body>
</html>