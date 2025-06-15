<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para la tabla 'media'.
 * Representa un archivo en la biblioteca de medios.
 *
 * @property int $id
 * @property int $usuario_id
 * @property string|null $titulo
 * @property string $nombre_archivo
 * @property string $ruta_archivo
 * @property string $url_publica
 * @property string $tipo_mime
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Usuario $autor
 */
class Media extends Model
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * La clave primaria para el modelo.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario_id',
        'titulo',
        'nombre_archivo',
        'ruta_archivo',
        'url_publica',
        'tipo_mime',
        'descripcion',
        'tamaño',
    ];


    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define la relación con el autor (Usuario).
     *
     * @return BelongsTo
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
