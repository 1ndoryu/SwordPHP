<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PaginaMeta
 *
 * Representa los metadatos asociados a una página.
 *
 * @property int $meta_id
 * @property int $pagina_id
 * @property string $meta_key
 * @property mixed $meta_value
 *
 * @package App\model
 */
class PaginaMeta extends Model
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'paginameta';

    /**
     * La clave primaria para el modelo.
     *
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'pagina_id',
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
     * Define la relación inversa con la página.
     */
    public function pagina(): BelongsTo
    {
        return $this->belongsTo(Pagina::class, 'pagina_id');
    }
}
