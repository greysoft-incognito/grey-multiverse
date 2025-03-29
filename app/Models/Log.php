<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Log extends Model
{
    /** @use HasFactory<\Database\Factories\LogFactory> */
    use HasFactory;

    protected $fillable = [
        'action',
        'description',
        'properties',
        'loggable_id',
        'loggable_type',
        'user_id',
        'user_type'
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    /**
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForAction(Builder $query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForUser(Builder $query, $user)
    {
        return $query->where('user_id', $user->id)
            ->where('user_type', get_class($user));
    }
}
