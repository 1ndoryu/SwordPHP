<?php

/**
 * Página de login del panel de administración.
 * Variables disponibles: $error, $estadoBd
 */
?>
<div class="tarjetaLogin">
    <div class="logoAuth">
        <h1>SwordPHP</h1>
        <p>Panel de Administración</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alertaError">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login" class="formularioLogin">
        <div class="grupoInput">
            <label for="email">Usuario o Email</label>
            <input
                type="text"
                id="email"
                name="email"
                placeholder="tu@email.com"
                autocomplete="username"
                required>
        </div>

        <div class="grupoInput">
            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="current-password"
                required>
        </div>

        <button type="submit" class="botonLogin">
            Iniciar Sesión
        </button>
    </form>

    <?php if (isset($estadoBd)): ?>
        <div class="estadoDb">
            <span class="indicador <?= $estadoBd['conectado'] ? 'conectado' : 'desconectado' ?>"></span>
            <span><?= htmlspecialchars($estadoBd['mensaje']) ?></span>
        </div>
    <?php endif; ?>
</div>