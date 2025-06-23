<?php

namespace App\controller;

use support\Request;
use support\Response;
use support\Db;
use App\service\UsuarioService;
use App\service\OpcionService;
use Dotenv\Dotenv;
use Throwable;

class InstallerController
{
    /**
     * Muestra el paso actual del formulario de instalación.
     * Esta versión está adaptada para entornos de contenedores como Coolify.
     *
     * @param Request $request
     * @return Response
     */
    public function showStep(Request $request): Response
    {
        // 1. Priorizar las variables de entorno del sistema (provistas por Coolify)
        // Se asume que si DB_HOST está definido, las demás también lo están.
        if (!empty(env('DB_HOST'))) {
            try {
                // 2. Intentar conectar a la base de datos
                Db::connection('pgsql')->select('select 1');

                // 3. Si la conexión es exitosa, comprobar si ya está instalado (si la tabla 'usuarios' existe)
                try {
                    Db::connection('pgsql')->select('select id from usuarios limit 1');
                    // Si la tabla existe, la instalación está completa. Redirigir a la página principal.
                    return redirect('/');
                } catch (Throwable $tableNotFound) {
                    // La conexión a la BD funciona, pero las tablas no existen.
                    // Saltar directamente al paso de configuración del sitio/admin.
                    return view('installer.show', [
                        'tituloPagina' => 'Configuración del Sitio',
                        'currentStep' => 'setup', // <-- ¡Saltamos al paso 'setup'!
                        'error' => session()->pull('error'),
                        'success' => session()->pull('success'),
                        'dbConfig' => []
                    ]);
                }
            } catch (Throwable $connectionError) {
                // Las variables de entorno existen pero la conexión falla. Es un error real.
                return view('installer.show', [
                    'tituloPagina' => 'Error de Conexión',
                    'currentStep' => 'database',
                    'error' => 'Las credenciales de la base de datos proporcionadas por el entorno son incorrectas. Por favor, revísalas en Coolify. Error: ' . $connectionError->getMessage(),
                    'success' => null,
                    'dbConfig' => []
                ]);
            }
        }

        // 4. Si no hay variables de entorno, mostrar el formulario de instalación de la base de datos como último recurso.
        return view('installer.show', [
            'tituloPagina' => 'Instalación de SwordPHP',
            'currentStep' => 'database',
            'dbConfig' => [],
            'error' => session()->pull('error'),
            'success' => session()->pull('success')
        ]);
    }

    /**
     * Procesa los datos del formulario de instalación.
     *
     * @param Request $request
     * @return Response
     */
    public function processStep(Request $request): Response
    {
        $step = $request->post('step');

        if ($step === 'database') {
            return $this->processDatabaseStep($request);
        }

        if ($step === 'setup') {
            return $this->processSetupStep($request);
        }

        session()->set('error', 'Paso de instalación no válido.');
        return redirect('/install');
    }

    /**
     * Procesa el paso de configuración de la base de datos.
     *
     * @param Request $request
     * @return Response
     */
    private function processDatabaseStep(Request $request): Response
    {
        $envContent = "DB_HOST=" . $request->post('db_host', '127.0.0.1') . "\n";
        $envContent .= "DB_PORT=" . $request->post('db_port', '5432') . "\n";
        $envContent .= "DB_DATABASE=" . $request->post('db_name', 'swordphp') . "\n";
        $envContent .= "DB_USERNAME=" . $request->post('db_user', 'postgres') . "\n";
        $envContent .= "DB_PASSWORD=\"" . $request->post('db_pass', '') . "\"\n";
        $envContent .= "APP_URL=" . $request->header('origin', 'http://127.0.0.1:8787') . "\n";

        try {
            file_put_contents(base_path('.env'), $envContent);
            // Redirigir para que la nueva configuración .env sea cargada por el sistema.
            return redirect('/install');
        } catch (Throwable $e) {
            session()->set('error', 'No se pudo escribir el archivo .env. Verifica los permisos de la carpeta `swordCore`.');
            return redirect('/install');
        }
    }

    /**
     * Procesa el paso final de configuración del sitio y el admin.
     *
     * @param Request $request
     * @return Response
     */
    private function processSetupStep(Request $request): Response
    {
        // Validación de datos
        $data = $request->only(['site_title', 'admin_user', 'admin_email', 'admin_pass', 'admin_pass_confirm']);
        if (empty($data['site_title']) || empty($data['admin_user']) || empty($data['admin_email']) || empty($data['admin_pass'])) {
            session()->set('error', 'Todos los campos de configuración del sitio son obligatorios.');
            return redirect('/install');
        }
        if ($data['admin_pass'] !== $data['admin_pass_confirm']) {
            session()->set('error', 'Las contraseñas del administrador no coinciden.');
            return redirect('/install');
        }

        try {
            // 1. Crear las tablas
            Db::connection('pgsql')->unprepared($this->getTableCreationSql());

            // 2. Crear el usuario administrador
            $usuarioService = new UsuarioService();
            $usuarioService->crearUsuario([
                'nombreusuario' => $data['admin_user'],
                'correoelectronico' => $data['admin_email'],
                'clave' => $data['admin_pass'],
                'rol' => 'admin'
            ]);

            // 3. Guardar las opciones del sitio
            $opcionService = new OpcionService();
            $opcionService->updateOption('titulo_sitio', $data['site_title']);
            $opcionService->updateOption('descripcion_sitio', 'Otro sitio increíble con SwordPHP');
            $opcionService->updateOption('active_plugins', []); // Iniciar sin plugins activos

            // 4. Crear el lock file para marcar la instalación como completada
            file_put_contents(runtime_path('installed.lock'), date('c'));

            // 5. Marcar la instalación como completada para mostrar el mensaje de reinicio.
            $request->session()->set('install_step', 'completed');
            return redirect('/install');
        } catch (Throwable $e) {
            // Limpiar para un posible reintento.
            Db::connection('pgsql')->unprepared('DROP TABLE IF EXISTS media, paginas, usuarios, opciones CASCADE;');
            session()->set('error', 'Error durante la instalación: ' . $e->getMessage());
            return redirect('/install');
        }
    }
    /**
     * Devuelve el SQL para crear todas las tablas necesarias.
     * @return string
     */
    private function getTableCreationSql(): string
    {
        return <<<SQL
    CREATE TABLE IF NOT EXISTS usuarios (
      id BIGSERIAL PRIMARY KEY,
      nombreusuario VARCHAR(60) NOT NULL UNIQUE,
      correoelectronico VARCHAR(100) NOT NULL UNIQUE,
      clave VARCHAR(255) NOT NULL,
      nombremostrado VARCHAR(250),
      rol VARCHAR(50) NOT NULL DEFAULT 'suscriptor',
      api_token VARCHAR(80) NULL UNIQUE,
      metadata JSONB,
      remember_token VARCHAR(100),
      created_at TIMESTAMP WITHOUT TIME ZONE,
      updated_at TIMESTAMP WITHOUT TIME ZONE
    );
    CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);
    CREATE INDEX IF NOT EXISTS idx_usuarios_api_token ON usuarios(api_token);
    CREATE INDEX IF NOT EXISTS idx_usuarios_metadata ON usuarios USING GIN (metadata);

    CREATE TABLE IF NOT EXISTS paginas (
      id BIGSERIAL PRIMARY KEY,
      titulo TEXT NOT NULL,
      subtitulo TEXT,
      contenido TEXT,
      slug VARCHAR(255) NOT NULL UNIQUE,
      idautor BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
      estado VARCHAR(50) NOT NULL DEFAULT 'borrador',
      tipocontenido VARCHAR(50) NOT NULL DEFAULT 'pagina',
      metadata JSONB,
      created_at TIMESTAMP WITHOUT TIME ZONE,
      updated_at TIMESTAMP WITHOUT TIME ZONE
    );
    CREATE INDEX IF NOT EXISTS idx_paginas_tipocontenido_estado ON paginas(tipocontenido, estado);
    CREATE INDEX IF NOT EXISTS idx_paginas_idautor ON paginas(idautor);
    CREATE INDEX IF NOT EXISTS idx_paginas_metadata ON paginas USING GIN (metadata);

    CREATE TABLE IF NOT EXISTS media (
      id BIGSERIAL PRIMARY KEY,
      idautor BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
      titulo TEXT NOT NULL,
      leyenda TEXT,
      textoalternativo VARCHAR(255),
      descripcion TEXT,
      rutaarchivo VARCHAR(255) NOT NULL UNIQUE,
      tipomime VARCHAR(100) NOT NULL,
      metadata JSONB,
      created_at TIMESTAMP WITHOUT TIME ZONE,
      updated_at TIMESTAMP WITHOUT TIME ZONE
    );
    CREATE INDEX IF NOT EXISTS idx_media_idautor ON media(idautor);
    CREATE INDEX IF NOT EXISTS idx_media_tipomime ON media(tipomime);
    CREATE INDEX IF NOT EXISTS idx_media_metadata ON media USING GIN (metadata);

    CREATE TABLE IF NOT EXISTS opciones (
      id BIGSERIAL PRIMARY KEY,
      opcion_nombre VARCHAR(191) NOT NULL UNIQUE,
      opcion_valor TEXT,
      created_at TIMESTAMP WITHOUT TIME ZONE,
      updated_at TIMESTAMP WITHOUT TIME ZONE
    );
    
    -- NUEVA TABLA: LIKES
    CREATE TABLE IF NOT EXISTS likes (
        id BIGSERIAL PRIMARY KEY,
        user_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        content_id BIGINT NOT NULL REFERENCES paginas(id) ON DELETE CASCADE,
        created_at TIMESTAMP WITHOUT TIME ZONE,
        updated_at TIMESTAMP WITHOUT TIME ZONE,
        UNIQUE (user_id, content_id)
    );
    CREATE INDEX IF NOT EXISTS idx_likes_user_id ON likes(user_id);
    CREATE INDEX IF NOT EXISTS idx_likes_content_id ON likes(content_id);

    SQL;
    }
}
