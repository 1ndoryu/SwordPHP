<?php
// 1. Inicia el loop de SwordPHP.
if (hayEntradas()) :
    while (hayEntradas()) :
        laEntrada();

        // 2. Define el título que usará el header.php.
        // Se obtiene después de iniciar la entrada en el loop.
        $titulo = obtenerElTitulo();

        // 3. Carga la cabecera del tema.
        getHeader();
?>

        <?php // -- COMIENZO DEL CONTENIDO ESPECÍFICO DE LA PÁGINA -- 
        ?>

        <h1><?php elTitulo(); ?></h1>
        <div>
            <?php
            // La función elContenido() se encarga de mostrar el contenido
            // y aplicará los filtros necesarios en el futuro (ej. para shortcodes).
            elContenido();
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
endif; // Fin de la comprobación hayEntradas().

// Carga el pie de página del tema.
getFooter();
?>