<?php

namespace app\command\db;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use support\Log;

/**
 * Comando para agregar la columna deleted_at a la tabla contents.
 * Esto habilita el sistema de papelera (soft deletes).
 */
class AddSoftDeletesCommand extends Command
{
    protected static $defaultName = 'db:add-soft-deletes';
    protected static $defaultDescription = 'Agrega la columna deleted_at a la tabla contents para soft deletes';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Log: Verificando columna deleted_at en tabla contents...');
        Log::channel('database')->info('Verificando columna deleted_at en tabla contents...');

        try {
            if (!Capsule::schema()->hasColumn('contents', 'deleted_at')) {
                Capsule::schema()->table('contents', function ($table) {
                    $table->softDeletes();
                });
                $output->writeln('Log: Columna "deleted_at" agregada correctamente a la tabla "contents".');
                Log::channel('database')->info('Columna "deleted_at" agregada correctamente a la tabla "contents".');
            } else {
                $output->writeln('Log: La columna "deleted_at" ya existe en la tabla "contents".');
                Log::channel('database')->info('La columna "deleted_at" ya existe en la tabla "contents".');
            }
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            Log::channel('database')->error('Error al agregar columna deleted_at: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln('Log: Proceso completado.');
        return Command::SUCCESS;
    }
}
