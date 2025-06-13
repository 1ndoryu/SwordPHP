<?php

declare(strict_types=1);

namespace App\Service;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\IntrospectionProcessor;
use Psr\Log\LoggerInterface;
use Stringable;

final class Logger implements LoggerInterface
{
    private MonologLogger $logger;

    public function __construct(Config $config)
    {
        $loggerConfig = $config->get('logger');

        $this->logger = new MonologLogger($loggerConfig['name']);

        $this->logger->pushProcessor(new IntrospectionProcessor(
            MonologLogger::DEBUG,
            ['App\\Service\\Logger']
        ));

        // Nuevo procesador para acortar el nombre de la clase.
        $this->logger->pushProcessor(function ($record) {
            if (isset($record['extra']['class'])) {
                $classParts = explode('\\', $record['extra']['class']);
                $record['extra']['class'] = end($classParts);
            }
            return $record;
        });

        // Formato sin el placeholder '%extra%' al final.
        $formato = "[%datetime%] %channel%.%level_name%: %message% [%extra.class%::%extra.function% L%extra.line%] %context%\n";
        $formateador = new LineFormatter($formato, null, true, true);

        $manejador = new StreamHandler(
            $loggerConfig['path'],
            $loggerConfig['level']
        );
        $manejador->setFormatter($formateador);

        $this->logger->pushHandler($manejador);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
