<?php

namespace App\model;

use App\model\traits\GestionaMetadatos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use GestionaMetadatos;

    protected $table = 'media';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'idautor',
        'titulo',
        'leyenda',
        'textoalternativo',
        'descripcion',
        'rutaarchivo',
        'tipomime',
        'metadata'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Define la relación con el autor (Usuario) del archivo.
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idautor');
    }
}