<?php
/**
 * Start file for windows
 */
chdir(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use support\App;
use Workerman\Worker;

// DEFINICIÓN DE CONSTANTES PARA EL SCRIPT MONITOR (windows.php)
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', realpath(base_path() . '/..'));
    define('SWORD_CORE_PATH', base_path());
    define('SWORD_CONTENT_PATH', PROJECT_ROOT . '/swordContent');
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . '/themes');
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . '/plugins');
    define('SWORD_UPLOADS_PATH', SWORD_CONTENT_PATH . '/uploads');
}

ini_set('display_errors', 'on');
error_reporting(E_ALL);

if (class_exists('Dotenv\Dotenv') && file_exists(base_path() . '/.env')) {
    if (method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
        Dotenv::createUnsafeImmutable(base_path())->load();
    } else {
        Dotenv::createMutable(base_path())->load();
    }
}

// Esta llamada ahora funcionará porque las constantes ya existen.
App::loadAllConfig(['route']);

$errorReporting = config('app.error_reporting');
if (isset($errorReporting)) {
    error_reporting($errorReporting);
}

$runtimeProcessPath = runtime_path() . DIRECTORY_SEPARATOR . '/windows';
$paths = [
    $runtimeProcessPath,
    runtime_path('logs'),
    runtime_path('views')
];
foreach ($paths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

$processFiles = [];
if (config('server.listen')) {
    $processFiles[] = __DIR__ . DIRECTORY_SEPARATOR . 'start.php';
}
foreach (config('process', []) as $processName => $config) {
    $processFiles[] = write_process_file($runtimeProcessPath, $processName, '');
}

foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project)) {
            continue;
        }
        foreach ($project['process'] ?? [] as $processName => $config) {
            $processFiles[] = write_process_file($runtimeProcessPath, $processName, "$firm.$name");
        }
    }
    foreach ($projects['process'] ?? [] as $processName => $config) {
        $processFiles[] = write_process_file($runtimeProcessPath, $processName, $firm);
    }
}

function write_process_file($runtimeProcessPath, $processName, $firm): string
{
    $processParam = $firm ? "plugin.$firm.$processName" : $processName;
    $configParam = $firm ? "config('plugin.$firm.process')['$processName']" : "config('process')['$processName']";
    $fileContent = <<<EOF
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Webman\Config;
use support\App;

// DEFINICIÓN DE CONSTANTES PARA LOS SCRIPTS DE TRABAJADORES
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', realpath(base_path() . '/..'));
    define('SWORD_CORE_PATH', base_path());
    define('SWORD_CONTENT_PATH', PROJECT_ROOT . '/swordContent');
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . '/themes');
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . '/plugins');
    define('SWORD_UPLOADS_PATH', SWORD_CONTENT_PATH . '/uploads');
}

ini_set('display_errors', 'on');
error_reporting(E_ALL);

if (is_callable('opcache_reset')) {
    opcache_reset();
}

if (!\$appConfigFile = config_path('app.php')) {
    throw new RuntimeException('Config file not found: app.php');
}
\$appConfig = require \$appConfigFile;
if (\$timezone = \$appConfig['default_timezone'] ?? '') {
    date_default_timezone_set(\$timezone);
}

App::loadAllConfig(['route']);

worker_start('$processParam', $configParam);

if (DIRECTORY_SEPARATOR != "/") {
    Worker::\$logFile = config('server')['log_file'] ?? Worker::\$logFile;
    TcpConnection::\$defaultMaxPackageSize = config('server')['max_package_size'] ?? 10*1024*1024;
}

Worker::runAll();

EOF;
    $processFile = $runtimeProcessPath . DIRECTORY_SEPARATOR . "start_$processParam.php";
    file_put_contents($processFile, $fileContent);
    return $processFile;
}

if ($monitorConfig = config('process.monitor.constructor')) {
    $monitorHandler = config('process.monitor.handler');
    $monitor = new $monitorHandler(...array_values($monitorConfig));
}

function popen_processes($processFiles)
{
    $cmd = '"' . PHP_BINARY . '" ' . implode(' ', $processFiles);
    $descriptorspec = [STDIN, STDOUT, STDOUT];
    $resource = proc_open($cmd, $descriptorspec, $pipes, null, null, ['bypass_shell' => true]);
    if (!$resource) {
        exit("Can not execute $cmd\r\n");
    }
    return $resource;
}

$resource = popen_processes($processFiles);
echo "\r\n";
while (1) {
    sleep(1);
    if (!empty($monitor) && $monitor->checkAllFilesChange()) {
        $status = proc_get_status($resource);
        $pid = $status['pid'];
        shell_exec("taskkill /F /T /PID $pid");
        proc_close($resource);
        $resource = popen_processes($processFiles);
    }
}