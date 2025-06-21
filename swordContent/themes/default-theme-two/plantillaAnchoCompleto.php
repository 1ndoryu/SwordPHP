<?php
/*
Template Name: Ancho Completo
*/

// 1. Inicia el loop de SwordPHP.
if (havePost()) :
    while (havePost()) :
        thePost();

        // 2. Define el título que usará el header.php.
        // Se obtiene después de iniciar la entrada en el loop.
        $titulo = getTitle();

        // 3. Carga la cabecera del tema.
        getHeader();
?>

        <?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA --
        ?>

        <h1><?php theTitle(); ?></h1>
        <div>
            <?php
            // La función theContent() se encarga de mostrar el contenido
            // y aplicará los filtros necesarios en el futuro (ej. para shortcodes).
            theContent();
            ?>
        </div>
        <hr>
        <p>✅ Vista cargada desde: default-theme-two, plantilla anchoCompleto</p>

        <?php // -- FIN DEL CONTENIDO ESPECÍFICO DE LA PÁGINA --
        ?>

    <?php
    endwhile; // Fin del loop principal.
else :
    // Opcional: Contenido a mostrar si no se encuentran entradas.
    $titulo = 'Contenido no encontrado';
    getHeader();
    ?>
    <h1>Contenido no encontrado</h1>
    <p>Lo sentimos, no pudimos encontrar lo que buscabas.</p>
<?php
endif; // Fin de la comprobación havePost().

// Carga el pie de página del tema.
getFooter();
?>