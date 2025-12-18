<?php

namespace app\command\dev;

use app\model\Content;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * Comando para listar contenidos en la base de datos.
 * Util para depuracion y verificacion de datos.
 */
class ListContentsCommand extends Command
{
    protected static $defaultName = 'dev:contents';
    protected static $defaultDescription = 'Lista los contenidos almacenados en la base de datos';

    protected function configure(): void
    {
        $this
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Filtrar por tipo (post, page, etc.)')
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'Filtrar por estado (published, draft)')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limite de resultados', 20)
            ->addOption('trashed', null, InputOption::VALUE_NONE, 'Mostrar solo contenidos en papelera');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        $status = $input->getOption('status');
        $limit = (int) $input->getOption('limit');
        $trashed = $input->getOption('trashed');

        $output->writeln('');
        $output->writeln('<info>Consultando contenidos...</info>');
        $output->writeln('');

        try {
            $query = $trashed
                ? Content::onlyTrashed()->with('user')
                : Content::with('user');

            if ($type) {
                $query->where('type', $type);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $contents = $query->orderBy('created_at', 'desc')->limit($limit)->get();

            if ($contents->isEmpty()) {
                $output->writeln('<comment>No se encontraron contenidos.</comment>');
                return Command::SUCCESS;
            }

            $output->writeln("<info>Total: {$total} | Mostrando: {$contents->count()}</info>");
            $output->writeln('');

            $table = new Table($output);
            $table->setHeaders(['ID', 'Tipo', 'Slug', 'Titulo', 'Estado', 'Autor', 'Creado']);

            foreach ($contents as $content) {
                $contentData = $content->content_data ?? [];
                $titulo = $contentData['title'] ?? '(sin titulo)';

                if (strlen($titulo) > 30) {
                    $titulo = substr($titulo, 0, 27) . '...';
                }

                $autor = $content->user ? $content->user->username : '(N/A)';
                $fecha = $content->created_at ? $content->created_at->format('Y-m-d H:i') : '(N/A)';

                $table->addRow([
                    $content->id,
                    $content->type,
                    $content->slug,
                    $titulo,
                    $content->status,
                    $autor,
                    $fecha
                ]);
            }

            $table->render();

            // Mostrar resumen por tipo
            $output->writeln('');
            $output->writeln('<info>Resumen por tipo:</info>');

            $tipos = Content::selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            foreach ($tipos as $tipo => $cantidad) {
                $output->writeln("  - {$tipo}: {$cantidad}");
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('');
        return Command::SUCCESS;
    }
}
