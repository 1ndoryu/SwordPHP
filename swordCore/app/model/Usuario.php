<?php

namespace App\model;

use App\model\traits\GestionaMetadatos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'metadata',
        'api_token' // Añadido
    ];

    protected $hidden = [
        'clave',
        'remember_token',
        'api_token' // Añadido
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
     * Genera un token de API único y lo asigna al modelo.
     *
     * @return $this
     */
    public function generarApiToken(): self
    {
        $token = Str::random(60);

        // Bucle para asegurar que el token sea único en la base de datos.
        while (static::where('api_token', $token)->exists()) {
            $token = Str::random(60);
        }

        $this->api_token = $token;
        return $this;
    }
}