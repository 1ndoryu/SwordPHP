<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?> - SwordPHP Admin</title>
    <?= \app\services\AssetManager::css('admin/css/variables.css') ?>
    <?= \app\services\AssetManager::css('admin/css/init.css') ?>
    <?= \app\services\AssetManager::css('admin/css/style.css') ?>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--fondoPrimario);
        }
    </style>
</head>

<body>
    <div class="tarjetaLogin">
        <?php if (isset($content)) echo $content; ?>
    </div>
</body>

</html>