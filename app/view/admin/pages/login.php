<h1 class="tituloLogin">Iniciar Sesión</h1>
<?php if (isset($error)): ?>
    <div style="background:var(--error); color:white; padding:10px; border-radius:4px; margin-bottom:15px; font-size:var(--fuentePequena);">
        <?= $error ?>
    </div>
<?php endif; ?>
<form method="POST" action="/admin/login">
    <div class="grupoFormulario">
        <label for="email">Email o Usuario</label>
        <input type="text" id="email" name="email" required placeholder="admin@example.com o username">
    </div>
    <div class="grupoFormulario">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
    </div>
    <button type="submit" class="botonPrimario">Entrar</button>
</form>

<?php if (isset($estadoBd)): ?>
    <div id="indicador-estado-bd" class="indicadorEstado">
        <span class="etiquetaEstado <?= $estadoBd['conectado'] ? 'estadoConectado' : 'estadoError' ?>">
            <span class="puntoEstado"></span>
            BD: <?= htmlspecialchars($estadoBd['mensaje']) ?>
        </span>
    </div>
<?php endif; ?>