<?php

namespace App\Models\BizMatch;

use App\Models\User;
use App\Notifications\NewAppointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<Reschedule> $reschedules
 * @property \App\Models\User $requestor
 * @property \App\Models\User $invitee
 */
class Appointment extends Model
{
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
        static::saved(fn (self $model) => $model->notify());
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

    public function notify()
    {
        $destinations = collect([
            'admin' => User::where(function ($query) {
                collect(config('permission-defs.admin_roles'))
                    ->each(fn ($role) => $query->orWhereHas('roles', fn ($q) => $q->where('name', $role)));
            })->get(),
            'sender' => collect([$this->requestor]),
            'recipient' => collect([$this->invitee]),
        ]);

        $destinations->each(function ($recipients, $type) {
            $recipients->each(fn (User $recipient) => $recipient->notify(new NewAppointment($this, $type)));
        });
    }

    public function scopeForUser($query, $userId): void
    {
        $query->orWhere(function($query) use ($userId) {
            $query->where('invitee_id', $userId);
            $query->whereNotNull('invitee_id');
        })->orWhere(function ($query) use ($userId) {
            $query->where('requestor_id', $userId);
            $query->whereNotNull('requestor_id');
        });
    }
}
