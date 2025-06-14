<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Pagina
 * @package App\model
 *
 * @property int $id
 * @property string $titulo
 * @property string|null $subtitulo
 * @property string|null $contenido
 * @property string $slug
 * @property int $idautor
 * @property string $estado
 * @property string $tipocontenido
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read Usuario $autor
 */
class Pagina extends Model
{
    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'paginas';

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
        'titulo',
        'subtitulo',
        'contenido',
        'slug',
        'idautor',
        'estado',
        'tipocontenido'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'fecha_publicacion' => 'datetime', // Ejemplo para futuras implementaciones
    ];

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Define la relación con el autor (Usuario).
     *
     * @return BelongsTo
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idautor');
    }
}
