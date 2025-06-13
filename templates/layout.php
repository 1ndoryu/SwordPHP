<?php
/**
 * @var League\Plates\Template\Template $this
 * @var string $executionTime
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($this->e($titulo ?? 'SwordPHP')) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            color: #1c1e21;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #dddfe2;
        }

        h1 {
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <div class="container">
        <?= $this->section('content') ?>
    </div>

    <footer class="container" style="text-align: center; margin-top: 2rem;">
        <small>Tiempo de carga: <?= $this->e($executionTime) ?> ms</small>
    </footer>
</body>

</html>