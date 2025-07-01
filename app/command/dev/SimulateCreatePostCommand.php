<?php
// app/command/dev/SimulateCreatePostCommand.php

namespace app\command\dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use app\model\User;
use app\Action\CreateContentAction;
use Throwable;

class SimulateCreatePostCommand extends Command
{
    protected static $defaultName = 'dev:simulate-post';
    protected static $defaultDescription = 'Simulates creating a post by directly calling the Action to test event dispatching.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Log: Iniciando simulación de creación de post...');

        // 1. Get Admin User (ID 1 as per example)
        $admin = User::find(1);
        if (!$admin) {
            $output->writeln('<error>Error: Usuario admin con ID=1 no encontrado. Asegúrate de que la base de datos esté instalada (db:install) y que el usuario exista.</error>');
            return Command::FAILURE;
        }
        $output->writeln('Log: Usuario admin (ID=1) encontrado.');

        // 2. Define the content data
        $postData = [
            'type' => 'audio_sample',
            'status' => 'published',
            'content_data' => [
                'title' => 'Melancholic Guitar Sample Test Command ' . time(),
                'media_id' => '1' // IMPORTANT: A media record with ID=1 must exist in your database
            ]
        ];
        $output->writeln('Log: Datos del post listos.');

        // 3. Instantiate the Action and call the 'execute' method directly
        $output->writeln("Log: Invocando la lógica de negocio de CreateContentAction...");
        try {
            $action = new CreateContentAction();
            $response = $action->execute($admin, $postData); // Call the new execute method

            // 4. Output the response from the Action
            $output->writeln('<info>--- RESPUESTA DE LA ACCIÓN ---</info>');
            $output->writeln('Status Code: ' . $response->getStatusCode());
            $output->writeln('Body: ');
            $output->writeln($response->rawBody());
            $output->writeln('<info>------------------------------</info>');

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                 $output->writeln('<info>Log: Simulación completada exitosamente. Revisa los logs (master.log, content.log, etc.) para verificar que los eventos se despacharon.</info>');
                 return Command::SUCCESS;
            } else {
                 $output->writeln('<error>Error: La simulación falló. La acción devolvió un código de error.</error>');
                 return Command::FAILURE;
            }

        } catch (Throwable $e) {
            $output->writeln('<error>Error: Ocurrió una excepción durante la ejecución de la acción.</error>');
            $output->writeln('Message: ' . $e->getMessage());
            $output->writeln('File: ' . $e->getFile() . ' on line ' . $e->getLine());
            return Command::FAILURE;
        }
    }
}