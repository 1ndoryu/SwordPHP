<?php

namespace App\model;

use App\model\traits\GestionaMetadatos;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use GestionaMetadatos;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombreusuario',
        'correoelectronico',
        'clave',
        'nombremostrado',
        'rol',
        'metadata' // Añadir metadata a los fillable
    ];
    
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
        'fecharegistro' => 'datetime', // Esto puede eliminarse si ya no existe la columna
        'metadata' => 'array',
    ];
}