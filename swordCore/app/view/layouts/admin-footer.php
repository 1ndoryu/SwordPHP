<?php

/**
 * PIE DE PÁGINA PARA EL PANEL DE ADMINISTRACIÓN
 */
?>
</div>
</main>
</div> <?php
        // Encolar el script de filtros usando la función del sistema
        encolarScript('filtros-listado', '/js/panel/filtrosListado.js');

        // Imprime las etiquetas <script> de los JS encolados.
        sw_admin_footer(); // Esta función debería imprimir lo encolado por encolarScript
        ?>
</body>

</html>