<?php

namespace App\model;

use App\model\traits\GestionaMetadatos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pagina extends Model
{
    // Cargar el trait para habilitar la gestión de metadatos
    use GestionaMetadatos;

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

    /**
     * Define la relación con los metadatos de la página.
     * Esta relación es requerida por el trait GestionaMetadatos.
     */
    public function metas(): HasMany
    {
        return $this->hasMany(PaginaMeta::class);
    }
}