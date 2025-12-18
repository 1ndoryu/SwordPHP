<?php

namespace app\command\dev;

use app\services\PostTypeRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * Comando para listar Post Types registrados.
 */
class ListPostTypesCommand extends Command
{
    protected static $defaultName = 'dev:post-types';
    protected static $defaultDescription = 'Lista los Post Types registrados (predefinidos y dinamicos)';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('');
        $output->writeln('<info>Post Types Registrados:</info>');
        $output->writeln('');

        try {
            $tipos = PostTypeRegistry::all();

            if (empty($tipos)) {
                $output->writeln('<comment>No hay Post Types registrados.</comment>');
                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table->setHeaders(['Slug', 'Nombre', 'Singular', 'Icono', 'En Menu', 'Predefinido', 'Dinamico']);

            foreach ($tipos as $slug => $config) {
                $table->addRow([
                    $slug,
                    $config['nombre'] ?? '-',
                    $config['nombreSingular'] ?? '-',
                    $config['icono'] ?? '-',
                    ($config['enMenu'] ?? false) ? 'Si' : 'No',
                    ($config['esPredefinido'] ?? false) ? 'Si' : 'No',
                    ($config['esDinamico'] ?? false) ? 'Si' : 'No'
                ]);
            }

            $table->render();

            $output->writeln('');
            $output->writeln('<info>Para Menu:</info>');

            $paraMenu = PostTypeRegistry::paraMenu();
            foreach ($paraMenu as $slug => $config) {
                $output->writeln("  - {$slug}: {$config['nombre']}");
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('');
        return Command::SUCCESS;
    }
}
