<?php
/**
 * Fichero de configuración del contenedor de inyección de dependencias
 */

// Creamos el constructor del contenedor de PHP-DI
$builder = new \DI\ContainerBuilder();

// Le decimos que cargue las definiciones de config/dependence.php
$builder->addDefinitions(config('dependence', []));

// Activamos la inyección automática (autowiring)
$builder->useAutowiring(true);

// ¡CORRECCIÓN! Activamos el uso de atributos de PHP 8 en lugar de anotaciones
$builder->useAttributes(true);

// Construimos y devolvemos el contenedor final
return $builder->build();