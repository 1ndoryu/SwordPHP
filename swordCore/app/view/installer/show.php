<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina); ?></title>
    <link rel="stylesheet" href="/css/panel/init.css">

    <style>
        .bloque.modal.installer-container {
            display: flex;
            flex-direction: column;
            max-width: 450px;
            width: 100%;
        }

        form {
            gap: 10px;
        }

        p {
            font-size: 11px !important;
        }

        label {
            font-size: 13px;
            color: unset !important;
            FONT-WEIGHT: 600;
        }

        button.btn {
            border: var(--borde);
            justify-content: center;
        }

    </style>
</head>

<body>
    <div class="bloque modal installer-container">

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="/install" method="POST">
            <?php echo csrf_field(); ?>

            <?php if ($currentStep === 'database'): ?>
                <input type="hidden" name="step" value="database">
                <h2>Conexión de Base de Datos</h2>
                <p>Por favor, proporciona los detalles de tu base de datos PostgreSQL.</p>
                <div class="form-group">
                    <label for="db_name">Nombre de la base de datos</label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($dbConfig['name'] ?? 'swordphp'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_user">Usuario</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($dbConfig['user'] ?? 'postgres'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_pass">Contraseña</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                <div class="form-group">
                    <label for="db_host">Host de la base de datos</label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($dbConfig['host'] ?? '127.0.0.1'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_port">Puerto</label>
                    <input type="text" id="db_port" name="db_port" value="<?php echo htmlspecialchars($dbConfig['port'] ?? '5432'); ?>" required>
                </div>
                <button type="submit" class="btn">Guardar y Conectar</button>

            <?php elseif ($currentStep === 'setup'): ?>
                <input type="hidden" name="step" value="setup">
                <h2>Configuración del Sitio</h2>
                <p>¡Conexión exitosa! Ahora, configuremos tu sitio.</p>
                <div class="form-group">
                    <label for="site_title">Título del Sitio</label>
                    <input type="text" id="site_title" name="site_title" required>
                </div>
                <div class="form-group">
                    <label for="admin_user">Nombre de Usuario</label>
                    <input type="text" id="admin_user" name="admin_user" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Correo Electrónico</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label for="admin_pass">Contraseña</label>
                    <input type="password" id="admin_pass" name="admin_pass" required>
                </div>
                <div class="form-group">
                    <label for="admin_pass_confirm">Confirmar Contraseña</label>
                    <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" required>
                </div>
                <button type="submit" class="btn">Instalar SwordPHP</button>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>