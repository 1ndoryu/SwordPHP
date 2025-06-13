<?php
declare(strict_types=1);

use App\Controller\HomeController;
use App\View\View; // 1. Importar la clase View
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use function DI\create; // 2. Importar la función 'create' de DI
use function DI\get;    // 3. Importar la función 'get' de DI

// Carga el autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Carga las variables de entorno desde el archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Crea una instancia del constructor del contenedor
$containerBuilder = new ContainerBuilder();

// Añadimos nuestras configuraciones (definiciones) al contenedor
$containerBuilder->addDefinitions([
    // Mantenemos la definición de settings igual
    'settings' => [
        'app' => [
            'env' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'name' => $_ENV['APP_NAME'] ?? 'SwordPHP'
        ],
        'templates' => [
            'path' => $_ENV['TEMPLATES_PATH']
        ]
    ],

    // 4. Añadimos una definición explícita para la clase View
    View::class => create(View::class)->constructor(get('settings')),
]);

// Construye el contenedor de inyección de dependencias
$container = $containerBuilder->build();

// Le decimos a Slim que utilice nuestro contenedor para crear objetos
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/', [HomeController::class, 'home']);

// Añade el Middleware de Ruteo
$app->addRoutingMiddleware();

// Obtenemos la configuración de depuración del contenedor
$displayErrorDetails = $container->get('settings')['app']['debug'];
$app->addErrorMiddleware($displayErrorDetails, true, true);

// Ejecuta la aplicación
$app->run();