<?php
declare(strict_types=1);

use App\Controller\HomeController;
use App\Service\Config;
use App\View\View;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = new Config(dirname(__DIR__) . '/config');

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    // Registramos la instancia de Config en el contenedor
    Config::class => $config,

    // Actualizamos la creación de View para que use el servicio Config
    View::class => function (ContainerInterface $c) {
        return new View($c->get(Config::class)->get('templates.path'));
    }
]);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/', [HomeController::class, 'index']);

$app->run();