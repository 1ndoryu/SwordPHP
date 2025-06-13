<?php
declare(strict_types=1);

use App\Controller\HomeController;
use App\Service\Config;
use App\View\View;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use function DI\create;

// Carga el autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Define la ruta a las plantillas
define('TEMPLATES_PATH', __DIR__ . '/../templates');

// Construye el contenedor de dependencias de PHP-DI
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    Config::class => create(Config::class)->constructor(
        require __DIR__ . '/../config/app.php'
    ),

    View::class => function (ContainerInterface $container) {
        $config = $container->get(Config::class);
        return new View($config->get('view.path'));
    },
    
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },
]);

$container = $containerBuilder->build();

// Crea la instancia de la aplicación Slim desde el contenedor
AppFactory::setContainer($container);
$app = AppFactory::create();

// Define la ruta principal
$app->get('/', [HomeController::class, 'index']);

// Ejecuta la aplicación
$app->run();
