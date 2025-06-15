<?php
/**
 * CABECERA GLOBAL DEL SITIO
 *
 * Se espera que la página que lo incluya defina una variable $titulo.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo ?? 'SwordPHP'); ?></title>
    <?php
        // Imprime los <link> de los estilos CSS encolados para el tema.
        //assetService()->imprimirEstilos();
    ?>
</head>
<body>

<?php // Aquí comienza el contenido principal ?>