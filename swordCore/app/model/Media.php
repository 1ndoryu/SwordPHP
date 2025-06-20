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
        'rutaarchivo', // Columna real de la BD
        'tipomime',
        'metadata'
    ];

    /**
     * ¡CORRECCIÓN! Este array SOLO debe contener los nombres de los ACCESORS (atributos virtuales).
     * 'rutaarchivo' es una columna real de la base de datos, NO debe estar aquí.
     * 'url_publica' es el atributo virtual que calculamos.
     * * Asegúrate de que esta línea esté así:
     */
    protected $appends = ['url_publica'];

    protected $casts = [
        'metadata' => 'array',
    ];
    
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idautor');
    }

    /**
     * ¡CORRECCIÓN! El nombre del método debe corresponder al accesor: getUrlPublicaAttribute.
     * Este método USA el valor de la columna 'rutaarchivo' para generar la URL.
     *
     * Asegúrate de que el nombre de esta función sea "getUrlPublicaAttribute":
     */
    public function getUrlPublicaAttribute(): ?string
    {
        if ($this->rutaarchivo) {
            return url_contenido('media/' . $this->rutaarchivo);
        }
        return null;
    }
}