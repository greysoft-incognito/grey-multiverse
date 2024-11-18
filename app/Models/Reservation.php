<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_id',
        'user_id',
        'user_type',
        'scan_date',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'scan_date' => 'datetime',
    ];

    /**
     * Get the space that owns the Reservation
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Get the user that owns the Reservation
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
        return $this->belongsTo(Guest::class, 'user_id', 'id');
    }

    /**
     *  Get the duration of this reservation.
     */
    public function duration(): Attribute
    {
        return new Attribute(
            get: fn () => $this->start_date->diffInDays($this->end_date),
        );
    }

    /**
     * Get the total price for this reservation.
     */
    public function cost(): Attribute
    {
        return new Attribute(
            get: fn () => $this->space->price * $this->getDuration(),
        );
    }

    public function status(): Attribute
    {
        return new Attribute(
            get: function () {
                $user = $this->user_type === 'guest' ? $this->guest : $this->user;
                $transaction = $user ? $this->transactions()->whereUserId($user->id)->latest()->first() : null;

                return $transaction
                    ? ($transaction->status === 'pending' || $transaction->status === 'paid' ? 'reserved' : $transaction->status)
                    : 'pending';
            },
        );
    }

    public function transaction(): Attribute
    {
        return new Attribute(
            get: function () {
                $user = $this->user_type === 'guest' ? $this->guest : $this->user;

                return $user ? $this->transactions()->whereUserId($user->id)->latest()->first() : null;
            },
        );
    }

    /**
     * Get all of the reservation's TRANSACTIONS.
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }
}
