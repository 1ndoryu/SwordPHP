<?php

namespace App\model;

use App\model\traits\GestionaMetadatos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagina extends Model
{
    use GestionaMetadatos;

    protected $table = 'paginas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'titulo',
        'subtitulo',
        'contenido',
        'slug',
        'idautor',
        'estado',
        'tipocontenido',
        'metadata' // Añadir metadata a los fillable
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * El cast 'array' le dice a Eloquent que maneje esta columna como JSON.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Define la relación con el autor (Usuario).
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idautor');
    }
}