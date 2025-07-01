<?php
// app/command/db/ResetCommand.php

namespace app\command\db;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use support\Log;

class ResetCommand extends Command
{
    protected static $defaultName = 'db:reset';
    protected static $defaultDescription = 'Drops all Sword v2 database tables';

    protected function configure()
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>Log: Operación cancelada. Utiliza --force para ejecutar el borrado de la base de datos.</error>');
            return Command::INVALID;
        }

        $output->writeln('<comment>Log: Iniciando el reseteo de la base de datos...</comment>');
        Log::channel('database')->warning('Iniciando el reseteo de la base de datos...');

        // El orden es importante para las claves foráneas
        $tables = [
            'likes',
            'comments',
            'user_follows', // <-- Añadido
            'media',
            'webhooks',
            'contents',
            'users',
            'roles',
            'options',
        ];

        try {
            // Desactivar temporalmente las restricciones de claves foráneas
            Capsule::schema()->disableForeignKeyConstraints();

            foreach ($tables as $table) {
                if (Capsule::schema()->hasTable($table)) {
                    Capsule::schema()->drop($table);
                    $output->writeln('Log: Tabla "' . $table . '" eliminada.');
                    Log::channel('database')->warning('Tabla "' . $table . '" eliminada.');
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            Log::channel('database')->error('Error durante el reseteo: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            // Siempre reactivar las restricciones
            Capsule::schema()->enableForeignKeyConstraints();
        }


        $output->writeln('<info>Log: Reseteo de la base de datos completado.</info>');
        Log::channel('database')->info('Reseteo de la base de datos completado.');

        return Command::SUCCESS;
    }
}