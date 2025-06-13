<?php
declare(strict_types=1);

use App\Controller\HomeController;
use DI\ContainerBuilder; // 1. Importamos el ContainerBuilder de PHP-DI
use Slim\Factory\AppFactory;

// Carga el autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Crea una instancia del constructor del contenedor
$containerBuilder = new ContainerBuilder();

// En el futuro, aquí podríamos añadir configuraciones específicas
// para nuestras clases (llamadas "definiciones").

// 3. Construye el contenedor de inyección de dependencias
$container = $containerBuilder->build();

// 4. Le decimos a Slim que utilice nuestro contenedor para crear objetos
AppFactory::setContainer($container);
$app = AppFactory::create();

// La ruta no cambia. Slim ahora usará el contenedor para instanciar HomeController.
$app->get('/', [HomeController::class, 'home']);

// Añade el Middleware de Ruteo
$app->addRoutingMiddleware();

// Añade el Middleware de Manejo de Errores
$app->addErrorMiddleware(true, true, true);

// Ejecuta la aplicación
$app->run();
