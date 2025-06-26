<?php

namespace app\model;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webhooks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_name',
        'target_url',
        'secret',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
    ];
}