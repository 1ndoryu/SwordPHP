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

            // === INICIO: LÓGICA IMAGEN DESTACADA (DE PERFIL) ===
            $idImagenDestacada = $request->post('_imagen_destacada_id');
            if (!empty($idImagenDestacada) && is_numeric($idImagenDestacada)) {
                $metadata['_imagen_destacada_id'] = (int)$idImagenDestacada;
            }
            // === FIN: LÓGICA IMAGEN DESTACADA ===

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
        // Filtros
        $searchTerm = $request->input('search_term');
        $roleFilter = $request->input('role_filter');
        $dateFilter = $request->input('date_filter'); // Filtro por fecha de creación

        // Paginación (se mantiene la lógica del servicio, pero la query se construye aquí)
        $page = (int)$request->input('page', 1);
        $perPage = 10; // O el valor que uses en tu servicio

        $query = \App\model\Usuario::query();

        // Aplicar filtros
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombreusuario', 'like', "%{$searchTerm}%")
                  ->orWhere('nombremostrado', 'like', "%{$searchTerm}%")
                  ->orWhere('correoelectronico', 'like', "%{$searchTerm}%");
            });
        }

        if ($roleFilter) {
            $query->where('rol', $roleFilter);
        }

        if ($dateFilter) {
            $query->whereDate('created_at', $dateFilter);
        }

        // Obtener el total de items para la paginación ANTES de aplicar limit/offset
        $totalItems = $query->count();

        $usuarios = $query->orderBy('created_at', 'desc')
                          ->offset(($page - 1) * $perPage)
                          ->limit($perPage)
                          ->get();

        // Crear un paginador manualmente para compatibilidad con la vista existente
        // Nota: Esto es una simplificación. Idealmente, tu servicio devolvería un LengthAwarePaginator
        // o la vista se adaptaría para manejar una colección y datos de paginación separados.
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $usuarios,
            $totalItems,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );


        // Roles disponibles para el filtro (podrías obtenerlos de una constante o configuración)
        $rolesDisponibles = ['admin', 'editor', 'autor', 'colaborador', 'suscriptor'];


        return view('admin/usuarios/index', [
            'tituloPagina' => 'Gestión de Usuarios', // Cambiado de 'titulo' para consistencia
            'usuarios' => $paginator, // Usar el paginador
            'rolesDisponibles' => $rolesDisponibles,
            'filtrosActuales' => [
                'search_term' => $searchTerm,
                'role_filter' => $roleFilter,
                'date_filter' => $dateFilter,
            ]
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

            // La relación 'metas' ya no existe, los datos están en la columna 'metadata'.
            // La línea que cargaba la relación ya no es necesaria.

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

            // Obtenemos los metadatos existentes para no perderlos
            $metadata = $usuario->metadata ?? [];

            // Procesamos los metadatos personalizados del formulario
            $metadatosFormulario = $request->post('meta', []);
            if (is_array($metadatosFormulario)) {
                foreach ($metadatosFormulario as $meta) {
                    if (isset($meta['clave']) && trim($meta['clave']) !== '') {
                        $clave = trim($meta['clave']);
                        $metadata[$clave] = $meta['valor'] ?? '';
                    }
                }
            }

            // === INICIO: LÓGICA IMAGEN DESTACADA (DE PERFIL) ===
            $idImagenDestacada = $request->post('_imagen_destacada_id');
            if (!empty($idImagenDestacada) && is_numeric($idImagenDestacada)) {
                $metadata['_imagen_destacada_id'] = (int)$idImagenDestacada;
            } else {
                // Si el input llega vacío, se elimina la meta
                unset($metadata['_imagen_destacada_id']);
            }
            // === FIN: LÓGICA IMAGEN DESTACADA ===

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
