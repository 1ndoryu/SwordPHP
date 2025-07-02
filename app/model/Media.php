<?php
// ARCHIVO NUEVO: app/model/Media.php

namespace app\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'mime_type',
        'user_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<string, string>
     */
    protected $appends = [
        'full_url',
    ];

    /**
     * Get the user that uploaded the media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la URL pública completa del archivo.
     * Devuelve algo como "http(s)://host/uploads/media/abc.mp3".
     *
     * @return string
     */
    public function getFullUrlAttribute(): string
    {
        // Construir host y esquema a partir de la petición actual si existe
        $scheme = 'http';
        $host = 'localhost';

        if (function_exists('request')) {
            $req = request();
            if ($req) {
                // Obtener host y protocolo de forma segura sin depender de métodos inexistentes
                $host = $req->header('host', 'localhost');
                $scheme = ($req->header('x-forwarded-proto')) ?? (($req->header('https') && $req->header('https') !== 'off') ? 'https' : 'http');
            }
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
                $scheme = 'https';
            }
        }

        $base = rtrim("{$scheme}://{$host}", '/');
        $relativePath = ltrim($this->path, '/');
        return $base . '/' . $relativePath;
    }
}
