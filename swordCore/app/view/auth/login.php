<?php

$titulo = $titulo ?? 'Iniciar Sesión';

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

    <form action="/login" method="POST" class="loginFormTwo">
        <?php echo csrf_field(); ?>

        <div class="formGroup">
            <label for="identificador" class="formLabel">Correo Electrónico o Usuario:</label><br>
            <input type="text" id="identificador" name="identificador" required class="formInput">
        </div>

        <div class="formGroup">
            <label for="clave" class="formLabel">Contraseña:</label><br>
            <input type="password" id="clave" name="clave" required class="formInput">
        </div>

        <button type="submit" class="submitButton borde">
            Iniciar Sesión
        </button>
    </form>
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
    }
</style>
<?php
include __DIR__ . '/../layouts/footer.php';
?>