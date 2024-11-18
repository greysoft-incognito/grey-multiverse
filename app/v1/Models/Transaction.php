<?php

namespace V1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'method',
        'amount',
        'status',
        'data',
        'tax',
        'due',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user that owns the Transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that owns the Transaction
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'user_id');
    }

    /**
     * Get the transactable that owns the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactable()
    {
        return $this->morphTo();
    }
}
