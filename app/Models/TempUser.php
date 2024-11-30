<?php

namespace App\Models;

use App\Notifications\OtpReceived;
use App\Traits\ModelCanExtend;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Valorin\Random\Random;

class TempUser extends Model
{
    use HasFactory;
    use ModelCanExtend;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'address',
        'country',
        'state',
        'city',
        'email',
        'phone',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_attempt' => 'datetime',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        self::creating(function (self $model) {
            $model->firstname ??= $model->firstname ?? str($model->email)->before('@')->toString();
        });

        self::deleting(function (self $model) {
            /** @var User */
            $user = User::whereEmail($model->email)->first();
            if ($user && $model->transactions()->count() > 0) {
                $model->transactions->each(function (Transaction $transaction) use ($user) {
                    $transaction->user_id = $user->id;
                    $transaction->temp_user_id = null;
                    $transaction->save();
                });
            }
        });
    }

    public static function createUser(array|Collection $data): self
    {
        if (empty($data['email'])) {
            throw ValidationException::withMessages([
                'email' => 'Email is required.',
            ]);
        }

        $fname = str($data['name'])->explode(' ')->first(null, $data['firstname'] ?? '');
        $lname = str($data['name'])->explode(' ')->last(fn($n) => $n !== $fname, $data['lastname'] ?? '');

        /** @var \App\Models\TempUser $user */
        return static::updateOrCreate(
            [
                'email' => $data['email'],
            ],
            [
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'lastname' => $data['lastname'] ?? $lname ?? '',
                'firstname' => $data['firstname'] ?? $fname,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    /**
     * Send OTP
     */
    public function sendOTPNotification()
    {
        $this->last_attempt = now();
        $this->otp = Random::otp(6);
        $this->save();

        $this->notify(new OtpReceived());
    }

    /**
     * Get the user's fullname .
     *
     * @return string
     */
    protected function fullname(): Attribute
    {
        return Attribute::make(
            get: fn() => collect([$this->firstname, $this->lastname])->join(' '),
        );
    }

    /**
     * Get all of the transactions for the User
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
