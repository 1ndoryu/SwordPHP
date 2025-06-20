<?php

$titulo = $titulo ?? 'Crear una cuenta';
include __DIR__ . '/../layouts/header.php';
?>

<div class="bloque modal loginContainer">
    <h2 class="loginTitle"><?php echo htmlspecialchars($titulo); ?></h2>

    <?php if (isset($exito) && $exito): ?>
        <p class="successMessage">
            <?php echo htmlspecialchars($exito); ?>
        </p>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <p class="errorMessage">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <form action="/registro" method="POST" class="loginFormTwo">
        <?php echo csrf_field(); ?>

        <div class="formGroup">
            <label for="nombreusuario" class="formLabel">Nombre de Usuario:</label><br>
            <input type="text" id="nombreusuario" name="nombreusuario" value="<?php echo htmlspecialchars(old('nombreusuario', '')); ?>" required class="formInput">
        </div>

        <div class="formGroup">
            <label for="correoelectronico" class="formLabel">Correo Electrónico:</label><br>
            <input type="email" id="correoelectronico" name="correoelectronico" value="<?php echo htmlspecialchars(old('correoelectronico', '')); ?>" required class="formInput">
        </div>

        <div class="formGroup">
            <label for="clave" class="formLabel">Contraseña:</label><br>
            <input type="password" id="clave" name="clave" required class="formInput">
        </div>

        <div class="formGroup">
            <label for="clave_confirmation" class="formLabel">Confirmar Contraseña:</label><br>
            <input type="password" id="clave_confirmation" name="clave_confirmation" required class="formInput">
        </div>

        <button type="submit" class="submitButton borde">
            Crear Cuenta
        </button>
    </form>

    <div class="linksAdicionales">
        <a href="/login">¿Ya tienes una cuenta? Inicia Sesión</a>
    </div>
</div>

<style>
    .bloque.modal.loginContainer {
        display: flex;
        flex-direction: column;
    }

    form.loginFormTwo {
        display: flex;
        flex-direction: column;
    }

    button.submitButton.borde {
        justify-content: center;
        margin-top: 1rem;
    }

    .linksAdicionales {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9em;
    }

    .linksAdicionales a {
        color: #333;
        text-decoration: none;
    }

    .linksAdicionales a:hover {
        text-decoration: underline;
    }
</style>

<?php
include __DIR__ . '/../layouts/footer.php';
?>