<?php

namespace App\Models\BizMatch;

use App\Models\User;
use App\Notifications\AppointmentRescheduled;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Appointment $appointment
 * @property \App\Models\User $requestor
 * @property \App\Models\User $invitee
 */
class Reschedule extends Model
{
    protected $fillable = [
        'appointment_id',
        'proposed_date',
        'proposed_duration',
        'proposed_time_slot',
    ];

    public static $msgGroups = [
        'sender' => [
            'pending' => 'You have requested to reschedule your appointment with :1.',
            'accepted' => ':1 has accepted your reschedule request.',
            'declined' => ':1 has declined your reschedule request.',
        ],
        'recipient' => [
            'pending' => ':0 is requesting to reschedule your appointment with them.',
            'accepted' => 'You accepted the reschedule request for your appointment with :0.',
            'declined' => 'You declined the reschedule request for your appointment with :0.',
        ],
        'admin' => [
            'pending' => 'An appointment reschedule has been requested by :0 for :1.',
            'declined' => 'The reschedule request for :1 by :0 has been declined by :1.',
            'accepted' => 'The reschedule request for :1 by :0 has been accepted by :1.',
        ],
    ];

    public static function booted(): void
    {
        parent::booted();
        static::saved(fn(self $model) => $model->notify());
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function requestor(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->appointment->requestor,
        );
    }

    public function invitee(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->appointment->invitee,
        );
    }

    public function notify()
    {
        $destinations = collect([
            'admin' => User::where(function ($query) {
                collect(config('permission-defs.admin_roles'))
                ->each(fn($role) => $query->orWhereHas('roles', fn($q) => $q->where('name', $role)));
            })->get(),
            'sender' => collect([$this->invitee]),
            'recipient' => collect([$this->requestor]),
        ]);

        $destinations->each(function ($recipients, $type) {
            $recipients->each(fn(User $recipient) => $recipient->notify(new AppointmentRescheduled($this, $type)));
        });
    }

    public function scopeForUser($query, $userId, ?bool $sent = null): void
    {
        $query->whereHas('appointment', function ($query) use ($userId, $sent) {
            if ($sent === false) {
                $query->where(function ($query) use ($userId) {
                    $query->where('requestor_id', $userId);
                    $query->whereNotNull('requestor_id');
                });
            } elseif ($sent === true) {
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
        });
    }
}