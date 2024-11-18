<?php

namespace V1\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Space extends Model
{
    use Fileable, HasFactory;

    protected $fillable = [
        'name',
        'size',
        'info',
        'price',
        'data',
        'max_occupants',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'size' => '0',
    ];

    public function registerFileable()
    {
        $this->fileableLoader('image', 'default');
    }

    /**
     * Get all of the reservations for the Space
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class)
            ->whereHas('transactions', function ($query) {
                $query->where('status', 'paid')
                    ->orWhere('status', 'pending');
            });
    }

    /**
     * Get all of the reservations for the Space regardless of status
     */
    public function allReservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get all of the the users who booked for the Space
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Reservation::class, 'space_id', 'id', 'id', 'user_id')
            ->join('transactions as t', 'reservations.id', '=', 't.transactable_id')
            ->where('t.transactable_type', 'V1\Models\Reservation')
            ->where(function ($query) {
                $query->where('t.status', 'paid')
                    ->orWhere('t.status', 'pending');
            });
    }

    /**
     * Get the user that owns the Transaction
     */
    public function guests(): HasManyThrough
    {
        return $this->hasManyThrough(Guest::class, Reservation::class, 'space_id', 'id', 'id', 'user_id')
            ->join('transactions as t', 'reservations.id', '=', 't.transactable_id')
            ->where('t.transactable_type', 'V1\Models\Reservation')
            ->where(function ($query) {
                $query->where('t.status', 'paid')
                    ->orWhere('t.status', 'pending');
            });
    }

    /**
     *  Get the total number of reservations for this space.
     */
    public function totalOccupants(): Attribute
    {
        // dd($this->users);
        return new Attribute(
            get: fn () => $this->users()->count() + $this->guests()->count(),
        );
    }

    /**
     * Show number of available spots in this space
     */
    public function availableSpots(): Attribute
    {
        return new Attribute(
            get: fn () => $this->max_occupants - $this->total_occupants,
        );
    }

    /**
     * Show number of available spots in this space
     */
    public function isAvailable(): Attribute
    {
        return new Attribute(
            get: fn () => $this->available_spots > 0,
        );
    }

    /**
     * Get all reserved dates for this space.
     */
    public function reservedDates(): Attribute
    {
        return new Attribute(
            get: (function () {
                $dates = [];

                $reservations = $this->reservations()->get();

                foreach ($reservations as $reservation) {
                    $dates[] = [
                        'start_date' => $reservation->start_date,
                        'end_date' => $reservation->end_date,
                    ];
                }

                return $dates;
            }),
        );
    }

    /**
     * Get all of the space's TRANSACTIONS.
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }
}
