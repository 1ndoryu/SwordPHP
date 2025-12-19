<?php

/**
 * Header del tema Developer
 * 
 * @package Developer Theme
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php laCabezaSeo(); ?>
    <link rel="stylesheet" href="<?php echo urlAsset('css/variables.css'); ?>">
    <link rel="stylesheet" href="<?php echo urlAsset('css/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo urlAsset('css/components.css'); ?>">
    <link rel="stylesheet" href="<?php echo urlAsset('css/layout.css'); ?>">
</head>

<body>
    <header id="cabecera-principal" class="cabeceraPrincipal">
        <div class="contenedor">
            <a href="<?php echo urlInicio(); ?>" class="logoSitio">
                <?php elNombreSitio(); ?>
            </a>
            <nav id="navegacion-principal" class="navegacionPrincipal">
                <a href="<?php echo urlInicio(); ?>">Inicio</a>
                <a href="<?php echo urlInicio(); ?>/blog">Blog</a>
            </nav>
        </div>
    </header>
    <main id="contenido-principal" class="contenidoPrincipal">