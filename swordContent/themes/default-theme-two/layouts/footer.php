<?php

/**
 * PIE DE PÁGINA GLOBAL DEL SITIO
 */
?>

<?php // Fin del contenido principal 
?>

<?php
// Imprime las etiquetas <script> de los JS encolados para el tema.
echo assetService()->imprimirAssetsFooter();

// Hook de acción para que plugins o el tema puedan añadir contenido antes de cerrar el body.
hacerAccion('pieDePagina');
?>

</body>

</html>