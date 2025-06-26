<?php

namespace app\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'likes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_id',
        'user_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     * Likes don't necessarily need updates, just creation/deletion.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the user that owns the like.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the content that the like belongs to.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
