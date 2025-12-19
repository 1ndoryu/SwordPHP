<?php

/**
 * Layout de autenticación para el panel de administración.
 * Variables disponibles: $title, $content
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?> - SwordPHP Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e4e4e7;
        }

        .contenedorAuth {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .tarjetaLogin {
            background: rgba(30, 30, 46, 0.95);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logoAuth {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logoAuth h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #67e8f9;
            letter-spacing: -0.5px;
        }

        .logoAuth p {
            color: #a1a1aa;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .formularioLogin {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .grupoInput {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .grupoInput label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #d4d4d8;
        }

        .grupoInput input {
            padding: 0.75rem 1rem;
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid rgba(63, 63, 70, 0.8);
            border-radius: 8px;
            color: #fafafa;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
        }

        .grupoInput input:focus {
            outline: none;
            border-color: #67e8f9;
            box-shadow: 0 0 0 3px rgba(103, 232, 249, 0.15);
        }

        .grupoInput input::placeholder {
            color: #71717a;
        }

        .botonLogin {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #0ea5e9 0%, #0891b2 100%);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .botonLogin:hover {
            background: linear-gradient(135deg, #38bdf8 0%, #06b6d4 100%);
            transform: translateY(-1px);
        }

        .botonLogin:active {
            transform: translateY(0);
        }

        .alertaError {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .estadoDb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #a1a1aa;
            margin-top: 1.5rem;
            justify-content: center;
        }

        .estadoDb .indicador {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .estadoDb .indicador.conectado {
            background: #22c55e;
            box-shadow: 0 0 6px rgba(34, 197, 94, 0.5);
        }

        .estadoDb .indicador.desconectado {
            background: #ef4444;
            box-shadow: 0 0 6px rgba(239, 68, 68, 0.5);
        }
    </style>
</head>

<body>
    <div class="contenedorAuth">
        <?= $content ?? '' ?>
    </div>
</body>

</html>