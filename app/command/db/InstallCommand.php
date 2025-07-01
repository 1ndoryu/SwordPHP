<?php
// app/command/db/InstallCommand.php

namespace app\command\db;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use support\Log;
use app\model\Role;

class InstallCommand extends Command
{
    protected static $defaultName = 'db:install';
    protected static $defaultDescription = 'Creates the required database tables for Sword v2';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Log: Iniciando instalación de la base de datos...');
        Log::channel('database')->info('Iniciando instalación de la base de datos...');

        try {
            // Roles
            if (!Capsule::schema()->hasTable('roles')) {
                Capsule::schema()->create('roles', function (Blueprint $table) {
                    $table->id();
                    $table->string('name')->unique();
                    $table->string('description')->nullable();
                    $table->jsonb('permissions')->nullable();
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "roles" creada correctamente.');
                Log::channel('database')->info('Tabla "roles" creada correctamente.');

                // Seed default roles
                Role::create([
                    'name' => 'admin',
                    'description' => 'Super Administrator with all permissions.',
                    'permissions' => ['*', 'admin.content.view']
                ]);
                Role::create([
                    'name' => 'user',
                    'description' => 'Standard user with basic content creation permissions.',
                    'permissions' => ['content.create', 'content.update.own', 'comment.create', 'comment.delete.own']
                ]);
                $output->writeln('Log: Roles por defecto ("admin", "user") creados.');
                Log::channel('database')->info('Roles por defecto ("admin", "user") creados.');
            }

            // Usuarios
            if (!Capsule::schema()->hasTable('users')) {
                Capsule::schema()->create('users', function (Blueprint $table) {
                    $table->id();
                    $table->string('username')->unique();
                    $table->string('email')->unique();
                    $table->string('password');
                    $table->unsignedBigInteger('role_id')->nullable();
                    $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
                    $table->jsonb('profile_data')->nullable();
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "users" creada correctamente.');
                Log::channel('database')->info('Tabla "users" creada correctamente.');
            }

            // Contenidos
            if (!Capsule::schema()->hasTable('contents')) {
                Capsule::schema()->create('contents', function (Blueprint $table) {
                    $table->id();
                    $table->string('slug')->unique();
                    $table->string('type')->default('post');
                    $table->string('status')->default('draft');
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->jsonb('content_data');
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "contents" creada correctamente.');
                Log::channel('database')->info('Tabla "contents" creada correctamente.');
            }

            // Media
            if (!Capsule::schema()->hasTable('media')) {
                Capsule::schema()->create('media', function (Blueprint $table) {
                    $table->id();
                    $table->string('path')->unique();
                    $table->string('mime_type');
                    $table->foreignId('user_id')->constrained('users')->onDelete('set null')->nullable();
                    $table->jsonb('metadata')->nullable();
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "media" creada correctamente.');
                Log::channel('database')->info('Tabla "media" creada correctamente.');
            }

            // Opciones
            if (!Capsule::schema()->hasTable('options')) {
                Capsule::schema()->create('options', function (Blueprint $table) {
                    $table->string('key')->primary();
                    $table->jsonb('value');
                });
                $output->writeln('Log: Tabla "options" creada correctamente.');
                Log::channel('database')->info('Tabla "options" creada correctamente.');
            }

            // Comentarios
            if (!Capsule::schema()->hasTable('comments')) {
                Capsule::schema()->create('comments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->text('body');
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "comments" creada correctamente.');
                Log::channel('database')->info('Tabla "comments" creada correctamente.');
            }

            // Likes
            if (!Capsule::schema()->hasTable('likes')) {
                Capsule::schema()->create('likes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->unique(['content_id', 'user_id']);
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "likes" creada correctamente.');
                Log::channel('database')->info('Tabla "likes" creada correctamente.');
            }
            
            // --- INICIO: NUEVA TABLA ---
            // User Follows
            if (!Capsule::schema()->hasTable('user_follows')) {
                Capsule::schema()->create('user_follows', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The one who follows
                    $table->foreignId('followed_user_id')->constrained('users')->onDelete('cascade'); // The one being followed
                    $table->timestamps();
                    $table->unique(['user_id', 'followed_user_id']);
                });
                $output->writeln('Log: Tabla "user_follows" creada correctamente.');
                Log::channel('database')->info('Tabla "user_follows" creada correctamente.');
            }
            // --- FIN: NUEVA TABLA ---

            // Webhooks
            if (!Capsule::schema()->hasTable('webhooks')) {
                Capsule::schema()->create('webhooks', function (Blueprint $table) {
                    $table->id();
                    $table->string('event_name')->index();
                    $table->string('target_url');
                    $table->string('secret')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
                $output->writeln('Log: Tabla "webhooks" creada correctamente.');
                Log::channel('database')->info('Tabla "webhooks" creada correctamente.');
            }

        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            Log::channel('database')->error('Error durante la instalación: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln('Log: Instalación de la base de datos completada.');
        Log::channel('database')->info('Instalación de la base de datos completada.');

        return Command::SUCCESS;
    }
}