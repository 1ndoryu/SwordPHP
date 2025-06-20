<?php

namespace App\service;

use App\model\Usuario;
use Throwable;
use support\Log;

class UsuarioService
{
    /**
     * Crea un nuevo usuario en la base de datos.
     * Asigna un rol por defecto si no se especifica.
     * Realiza validaciones de campos obligatorios, unicidad y contraseña.
     *
     * @param array $datosUsuario Los datos del usuario a crear.
     * @return Usuario El modelo del usuario creado.
     * @throws \support\exception\BusinessException Si falla la validación.
     */
    public function crearUsuario(array $datosUsuario): Usuario
    {
        try {
            // Validación 1: Campos obligatorios y unicidad
            if (empty($datosUsuario['nombreusuario']) || Usuario::where('nombreusuario', $datosUsuario['nombreusuario'])->exists()) {
                throw new \support\exception\BusinessException('El nombre de usuario es obligatorio y ya está en uso.');
            }
            if (empty($datosUsuario['correoelectronico']) || Usuario::where('correoelectronico', $datosUsuario['correoelectronico'])->exists()) {
                throw new \support\exception\BusinessException('El correo electrónico es obligatorio y ya está en uso.');
            }
            if (empty($datosUsuario['clave'])) {
                throw new \support\exception\BusinessException('La contraseña es obligatoria.');
            }

            // Validación 2: Confirmación de contraseña (si se proporciona el campo de confirmación)
            if (isset($datosUsuario['clave_confirmation']) && $datosUsuario['clave'] !== $datosUsuario['clave_confirmation']) {
                throw new \support\exception\BusinessException('Las contraseñas no coinciden.');
            }

            // Lógica de asignación de rol: si no viene del formulario, se asigna el de por defecto.
            if (!isset($datosUsuario['rol'])) {
                $esPrimerUsuario = Usuario::count() === 0;
                $datosUsuario['rol'] = $esPrimerUsuario ? 'admin' : 'suscriptor';
            }

            // Hashear la clave antes de guardar
            $datosUsuario['clave'] = password_hash($datosUsuario['clave'], PASSWORD_BCRYPT);

            return Usuario::create($datosUsuario);
        } catch (\Throwable $e) {
            // Si ya es una de nuestras excepciones de negocio, la relanzamos para que el controlador la maneje.
            if ($e instanceof \support\exception\BusinessException) {
                throw $e;
            }
            // Si es otro tipo de error (ej. de la BD), lo registramos y lanzamos una excepción genérica.
            Log::channel('default')->error(
                'Error al crear el usuario: ' . $e->getMessage(),
                ['exception' => $e]
            );
            throw new \support\exception\BusinessException('No se pudo crear el usuario por un error interno del sistema.');
        }
    }

    /**
     * Verifica si una contraseña en texto plano coincide con una hasheada.
     *
     * @param string $clavePlana La contraseña enviada por el usuario.
     * @param string $claveHasheada La contraseña almacenada en la base de datos.
     * @return bool True si la contraseña es correcta, false en caso contrario.
     */
    public function verificarClave(string $clavePlana, string $claveHasheada): bool
    {
        return password_verify($clavePlana, $claveHasheada);
    }

    /**
     * Obtiene un usuario por su ID.
     *
     * @param int $id El ID del usuario a buscar.
     * @return Usuario El modelo del usuario encontrado.
     * @throws \Webman\Exception\NotFoundException Si no se encuentra el usuario.
     */
    public function obtenerUsuarioPorId(int $id): Usuario
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            throw new \Webman\Exception\NotFoundException('Usuario no encontrado.');
        }
        return $usuario;
    }

    /**
     * Autentica a un usuario basado en un identificador y una contraseña.
     *
     * @param string $identificador El correo electrónico o nombre de usuario.
     * @param string $clavePlana La contraseña en texto plano.
     * @return Usuario|null El modelo del usuario si la autenticación es exitosa, de lo contrario null.
     */
    public function autenticarUsuario(string $identificador, string $clavePlana): ?Usuario
    {
        $usuario = Usuario::where('correoelectronico', $identificador)
            ->orWhere('nombreusuario', $identificador)
            ->first();

        if (!$usuario) {
            return null;
        }

        if ($this->verificarClave($clavePlana, $usuario->clave)) {
            return $usuario;
        }

        return null;
    }

    /**
     * Obtiene una lista paginada de todos los usuarios.
     *
     * @param int $porPagina
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function obtenerUsuariosPaginados(int $porPagina = 15)
    {
        // Ordenamos por fecha de creación descendente para ver los más nuevos primero.
        return Usuario::latest()->paginate($porPagina);
    }

    /**
     * Actualiza un usuario existente con los datos proporcionados.
     *
     * @param int   $id    El ID del usuario a actualizar.
     * @param array $datos Los nuevos datos para el usuario.
     * @return Usuario El modelo del usuario actualizado.
     * @throws \Webman\Exception\NotFoundException si el usuario no existe.
     * @throws \support\exception\BusinessException si hay errores de validación.
     */
    public function actualizarUsuario(int $id, array $datos): Usuario
    {
        $usuario = $this->obtenerUsuarioPorId($id);

        // Validación 1: Correo electrónico único si se ha modificado.
        if (!empty($datos['correoelectronico']) && $datos['correoelectronico'] !== $usuario->correoelectronico) {
            if (Usuario::where('correoelectronico', $datos['correoelectronico'])->where('id', '!=', $id)->exists()) {
                throw new \support\exception\BusinessException('El correo electrónico ya está en uso por otro usuario.');
            }
        }

        // Validación y asignación de la contraseña si se está cambiando.
        // Solo se actualiza si se proporciona una nueva contraseña.
        if (!empty($datos['clave'])) {
            if ($datos['clave'] !== ($datos['clave_confirmation'] ?? null)) {
                throw new \support\exception\BusinessException('Las contraseñas no coinciden.');
            }
            // Hashear y asignar la nueva contraseña.
            $usuario->clave = password_hash($datos['clave'], PASSWORD_BCRYPT);
        }

        // Quitar la clave y su confirmación de los datos a rellenar masivamente
        // para no sobreescribir la existente con un valor vacío.
        unset($datos['clave'], $datos['clave_confirmation']);

        // Asignamos el resto de los datos que están en el array $fillable.
        $usuario->fill($datos);

        // Asignación de los metadatos.
        if (isset($datos['metadata']) && is_array($datos['metadata'])) {
            $usuario->metadata = $datos['metadata'];
        }

        $usuario->save();

        return $usuario;
    }
    /**
     * Elimina un usuario y sus metadatos asociados.
     * Previene la auto-eliminación y la eliminación del último administrador.
     *
     * @param int $id El ID del usuario a eliminar.
     * @return bool
     * @throws \support\exception\BusinessException Si se intenta una operación no permitida.
     * @throws \Webman\Exception\NotFoundException si el usuario no existe.
     */
    public function eliminarUsuario(int $id): bool
    {
        // Validación 1: Prevenir que un usuario se elimine a sí mismo.
        if ($id === idUsuarioActual()) {
            throw new \support\exception\BusinessException('No puedes eliminar tu propia cuenta de usuario.');
        }

        $usuario = $this->obtenerUsuarioPorId($id);

        // Validación 2: Prevenir la eliminación del último administrador.
        if ($usuario->rol === 'admin') {
            $numeroAdmins = Usuario::where('rol', 'admin')->count();
            if ($numeroAdmins <= 1) {
                throw new \support\exception\BusinessException('No se puede eliminar al único administrador del sitio.');
            }
        }

        // Usamos una transacción para asegurar que el usuario y sus metas se borren juntos.
        \Illuminate\Database\Capsule\Manager::transaction(function () use ($usuario) {
            $usuario->metas()->delete();
            $usuario->delete();
        });

        return true;
    }
}
