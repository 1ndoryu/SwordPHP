<h1 class="tituloLogin">Iniciar Sesión</h1>
<form method="POST" action="/admin/login">
    <div class="grupoFormulario">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="admin@example.com">
    </div>
    <div class="grupoFormulario">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
    </div>
    <button type="submit" class="botonPrimario">Entrar</button>
</form>