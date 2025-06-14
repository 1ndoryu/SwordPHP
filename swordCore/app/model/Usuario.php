<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Usuario
 * * @package App\model
 *
 * @property int $id
 * @property string $nombreUsuario
 * @property string $correoElectronico
 * @property string $clave
 * @property string|null $nombreMostrado
 * @property \Carbon\Carbon $fechaRegistro
 * @property string $rol
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Usuario extends Model
{
    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * La clave primaria para el modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombreusuario',
        'correoelectronico',
        'clave',
        'nombremostrado',
        'rol'
    ];

    /**
     * Los atributos que deben ocultarse para las serializaciones.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'clave',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecharegistro' => 'datetime',
    ];

    /**
     * Indica si el modelo debe tener timestamps.
     * Eloquent gestionará created_at y updated_at automáticamente.
     *
     * @var bool
     */
    public $timestamps = true;
}
