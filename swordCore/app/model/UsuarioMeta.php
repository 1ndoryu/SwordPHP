<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UsuarioMeta
 *
 * Representa los metadatos asociados a un usuario.
 *
 * @property int $umeta_id
 * @property int $usuario_id
 * @property string $meta_key
 * @property mixed $meta_value
 *
 * @package App\model
 */
class UsuarioMeta extends Model
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'usermeta';

    /**
     * La clave primaria para el modelo.
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'usuario_id',
        'meta_key',
        'meta_value',
    ];

    /**
     * Indica si el modelo debe ser sellado con tiempo.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define la relación inversa con el usuario.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
