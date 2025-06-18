<?php

namespace App\controller;

use App\service\UsuarioService;
use support\Request;
use support\Response;

class UsuarioController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return view('admin/usuarios/create', [
            'tituloPagina' => 'Añadir Nuevo Usuario',
            'errorMessage' => $request->session()->pull('error'),
        ]);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        try {
            // Construir el array de metadatos
            $metadata = [];
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $metadata[trim($meta['clave'])] = $meta['valor'] ?? '';
                    }
                }
            }

            // Unir datos principales y metadatos
            $datosPrincipales = $request->only(['nombreusuario', 'correoelectronico', 'nombremostrado', 'clave', 'clave_confirmation', 'rol']);
            $datosPrincipales['metadata'] = $metadata;

            $this->usuarioService->crearUsuario($datosPrincipales);

            $request->session()->set('success', 'Usuario creado con éxito.');
            return redirect('/panel/usuarios');
        } catch (\support\exception\BusinessException $e) {
            $request->session()->set('error', 'No se pudo crear el usuario: ' . $e->getMessage());
            $request->session()->set('_old_input', $request->post());
            return redirect('/panel/usuarios/crear');
        } catch (\Throwable $e) {
            $request->session()->set('error', 'Ocurrió un error inesperado al crear el usuario.');
            $request->session()->set('_old_input', $request->post());
            \support\Log::error('Error al guardar usuario: ' . $e->getMessage());
            return redirect('/panel/usuarios/crear');
        }
    }

    /**
     * Muestra la lista paginada de usuarios.
     *
     * @param Request $request
     * @return Response
     */


    public function index(Request $request): Response
    {

        $usuarios = $this->usuarioService->obtenerUsuariosPaginados();
        return view('admin/usuarios/index', [
            'titulo' => 'Gestión de Usuarios',
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id): Response
    {
        try {
            // Usamos el servicio para obtener el usuario por su ID.
            $usuario = $this->usuarioService->obtenerUsuarioPorId((int)$id);

            // Cargamos explícitamente la relación 'metas' para que esté disponible en la vista.
            // Esto es necesario para el componente de gestión de metadatos.
            $usuario->load('metas');

            $errorMessage = $request->session()->pull('error');

            return view('admin/usuarios/edit', [
                'tituloPagina' => 'Editar Usuario',
                'usuario' => $usuario,
                'errorMessage' => $errorMessage
            ]);
        } catch (\Webman\Exception\NotFoundException $e) {
            $request->session()->set('error', 'El usuario que intentas editar no existe.');
            return redirect('/panel/usuarios');
        }
    }

    /**
     * Actualiza un usuario existente en la base de datos.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerUsuarioPorId((int)$id);

            // Construir metadatos
            $metadata = [];
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $metadata[trim($meta['clave'])] = $meta['valor'] ?? '';
                    }
                }
            }

            // Unir datos principales y metadatos
            $datosPrincipales = $request->only(['nombremostrado', 'correoelectronico', 'clave', 'clave_confirmation', 'rol']);
            $datosPrincipales['metadata'] = $metadata;

            $this->usuarioService->actualizarUsuario((int)$id, $datosPrincipales);

            $request->session()->set('success', 'Usuario actualizado con éxito.');
            return redirect('/panel/usuarios');
        } catch (\support\exception\BusinessException $e) {
            $request->session()->set('error', 'Error de validación: ' . $e->getMessage());
            $request->session()->set('_old_input', $request->post());
            return redirect('/panel/usuarios/editar/' . $id);
        } catch (\Throwable $e) {
            $request->session()->set('error', 'Ocurrió un error inesperado: ' . $e->getMessage());
            $request->session()->set('_old_input', $request->post());
            return redirect('/panel/usuarios/editar/' . $id);
        }
    }
    /**
     * Elimina un usuario de la base de datos.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request, $id): Response
    {
        try {
            // Llama al servicio para eliminar el usuario.
            // El servicio ya contiene la lógica de seguridad (no auto-borrado, no último admin).
            $this->usuarioService->eliminarUsuario((int)$id);
            $request->session()->set('success', 'Usuario eliminado con éxito.');
        } catch (\support\exception\BusinessException | \Webman\Exception\NotFoundException $e) {
            // Captura excepciones conocidas: de negocio o si el usuario no se encuentra.
            $request->session()->set('error', $e->getMessage());
        } catch (\Throwable $e) {
            // Captura cualquier otro error inesperado para evitar que se rompa la aplicación.
            $request->session()->set('error', 'Ocurrió un error inesperado al eliminar el usuario.');
            // Es buena práctica registrar el error real para depuración.
            \support\Log::error('Error al eliminar usuario: ' . $e->getMessage());
        }

        return redirect('/panel/usuarios');
    }
}
