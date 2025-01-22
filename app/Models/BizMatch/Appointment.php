<?php

namespace App\Models\BizMatch;

use App\Models\User;
use App\Notifications\NewAppointment;
use App\Traits\Conversationable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @property \Illuminate\Database\Eloquent\Collection<Reschedule> $reschedules
 * @property \App\Models\User $requestor
 * @property \App\Models\User $invitee
 * @property \Carbon\Carbon $date
 */
class Appointment extends Model
{
    use Conversationable;

    public static $msgGroups = [
        'sender' => [
            'pending' => 'You have sent a new appointment request to :0.',
            'confirmed' => 'Your appointment request to :0 has been accepted by the recipient.',
            'canceled' => 'Your appointment request to :0 has been declined by the recipient.',
            'reschedule_pending' => ':0 is requesting to reschedule your appointment.',
        ],
        'recipient' => [
            'pending' => 'You have received a new appointment request from :1.',
            'confirmed' => 'You accepted the appointment request by :1.',
            'canceled' => 'You declined the appointment request by :1.',
            'reschedule_accepted' => ':1 has accepted your reschedule request.',
            'reschedule_declined' => ':1 has declined your reschedule request.',
        ],
        'admin' => [
            'pending' => 'A new appointment request has been created for :0 by :1.',
            'confirmed' => 'The appointment request for :0 by :1 has been accepted by :0.',
            'canceled' => 'The appointment request from :0 by :1 has been declined by :0.',
            'reschedule_pending' => 'An appointment reschedule has been requested by :0 for :1.',
            'reschedule_declined' => 'The reschedule request for :0 by :1 has been declined by :0.',
            'reschedule_accepted' => 'The reschedule request for :0 by :1 has been accepted by :0.',
        ],
    ];

    public function casts(): array
    {
        return [
            'booked_for' => 'datetime',
            'duration' => 'integer',
            'date' => 'date:Y-m-d',
        ];
    }

    public static function booted(): void
    {
        parent::booted();
        static::saved(fn(self $model) => $model->notify());
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(Reschedule::class);
    }

    public function hasPendingReschedule(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reschedules()->whereStatus('pending')->exists()
        );
    }

    public function notify()
    {
        $destinations = collect([
            'admin' => User::where(function ($query) {
                collect(config('permission-defs.admin_roles'))
                    ->each(fn($role) => $query->orWhereHas('roles', fn($q) => $q->where('name', $role)));
            })->get(),
            'sender' => collect([$this->requestor]),
            'recipient' => collect([$this->invitee]),
        ]);

        $destinations->each(function ($recipients, $type) {
            $recipients->each(fn(User $recipient) => $recipient->notify(new NewAppointment($this, $type)));
        });
    }

    public function scopeForUser($query, $userId, ?bool $sent = null): void
    {
        if ($sent === true) {
            $query->where(function ($query) use ($userId) {
                $query->where('requestor_id', $userId);
                $query->whereNotNull('requestor_id');
            });
        } elseif ($sent === false) {
            $query->where(function ($query) use ($userId) {
                $query->where('invitee_id', $userId);
                $query->whereNotNull('invitee_id');
            });
        } else {
            $query->orWhere(function ($query) use ($userId) {
                $query->where('invitee_id', $userId);
                $query->whereNotNull('invitee_id');
            });
            $query->orWhere(function ($query) use ($userId) {
                $query->where('requestor_id', $userId);
                $query->whereNotNull('requestor_id');
            });
        }
    }

    public function findNextAvailableSlot()
    {
        [$start, $end] = config("api.timemap.{$this->time_slot}");

        // Set start and end times based on the slot in `timemap`
        $startTime = $this->date->copy()->setTimeFrom(Carbon::parse($start));
        $endTime = $this->date->copy()->setTimeFrom(Carbon::parse($end));

        // Initialize the current time to start time of the time slot
        $currentTime = $startTime->copy();

        // Check for available slot within the specified range
        while ($currentTime->lessThanOrEqualTo($endTime)) {
            $existingAppointment = Appointment::where('date', $this->date)
                ->where('booked_for', $currentTime)
                ->where('status', 'confirmed')
                ->where('time_slot', $this->time_slot)
                ->first();

            if (!$existingAppointment) {
                // Set `booked_for` to the next available time slot if found
                $this->booked_for = $currentTime;
                $this->table_number = $this->assignTableNumber($currentTime, $this->date);
                return $this;
            }

            // Move to the next slot within the time slot interval
            $currentTime->addMinutes($this->duration);
        }

        // If no available time is found within the slot, throw an exception
        throw new ModelNotFoundException("No available time slots for {$this->time_slot} on selected date.");
    }

    /**
     * Assign a table number based on the time slot and date.
     */
    protected function assignTableNumber(Carbon $startTime, Carbon $date): int
    {
        // Example logic for table assignment
        $existingTables = Appointment::where('date', $date)
            ->where('booked_for', $startTime)
            ->where('status', 'confirmed')
            ->pluck('table_number')
            ->toArray();

        $allTables = collect(config('api.tables'))->shuffle();

        // Find the first available table number
        foreach ($allTables as $table) {
            if (!in_array($table, $existingTables)) {
                return $table;
            }
        }

        // Throw an error if no tables are available
        throw new ModelNotFoundException('No available tables for the selected time slots.');
    }
}
