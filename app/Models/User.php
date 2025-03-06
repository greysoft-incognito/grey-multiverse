<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Company;
use App\Notifications\OtpReceived;
use App\Notifications\SendCode;
use App\Traits\ModelCanExtend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use ToneflixCode\LaravelFileable\Traits\Fileable;
use Valorin\Random\Random;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

/**
 * @property \App\Models\BizMatch\Company $company
 */
class User extends Authenticatable
{
    use Fileable;
    use HasApiTokens;
    use HasFactory;
    use HasPermissions;
    use HasRoles;
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
        'phone_country',
        'username',
        'password',
        'otp',
    ];

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'data',
        'password',
        'remember_token',
        'access_data',
        'email_verify_code',
        'phone_verify_code',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'data' => '{}',
        'reg_status' => 'pending',
        'access_data' => '{}',
        'phone_country' => 'NG',
    ];

    /**
     * The attributes to be appended
     *
     * @var array
     */
    protected $appends = [
        'user_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
            'access_data' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
            'phone' => E164PhoneNumberCast::class . ':' . ($this->phone_country ?? 'NG'),
            'last_attempt' => 'datetime',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
        ];
    }

    public function registerFileable()
    {
        $this->fileableLoader([
            'image' => 'avatar',
        ], 'default', true);
    }

    public static function registerEvents()
    {
        static::creating(function (self $model) {
            $userName = str($model->email)->before('@');
            $model->username ??= $model->generateUsername($userName);
            if (! dbconfig('verify_email', false)) {
                $model->email_verified_at ??= now();
            }
            if (! dbconfig('verify_phone', false)) {
                $model->phone_verified_at ??= now();
            }
            unset($model->privileges);
        });
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('username', $value)
            ->firstOrFail();
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->files['image'],
        );
    }

    /**
     * Get the user's fullname.
     */
    protected function fullname(): Attribute
    {
        return Attribute::make(
            get: fn() => collect([$this->firstname, $this->lastname])->filter()->join(' '),
        );
    }

    /**
     * Alias of fullname().
     */
    protected function name(): Attribute
    {
        return $this->fullname();
    }

    public function hasVerifiedPhone()
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function privileges(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getRoleNames(),
            set: function (array|\Illuminate\Support\Collection $value) {
                if (count($value) && app()->runningInConsole()) {
                    $roles = Role::whereIn('name', $value)->orWhereIn('id', $value)->pluck('name');
                    $roles->count() > 0 && $this->syncRoles($roles);
                }
            }
        );
    }

    /**
     * Send the email verification message
     */
    public function sendEmailVerificationNotification()
    {
        $this->last_attempt = now();
        $this->email_verify_code = Random::otp(6);
        $this->save();

        $this->notify(new SendCode($this->email_verify_code, 'verify'));
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
     * Send the phone verification message
     */
    public function sendPhoneVerificationNotification()
    {
        $this->last_attempt = now();
        $this->phone_verify_code = Random::otp(6);
        $this->save();

        $this->notify(new SendCode($this->phone_verify_code, 'verify-phone'));
    }

    public function markEmailAsVerified()
    {
        $this->last_attempt = null;
        $this->email_verify_code = null;
        $this->email_verified_at = now();
        $this->save();

        if ($this->wasChanged('email_verified_at')) {
            return true;
        }

        return false;
    }

    public function markPhoneAsVerified()
    {
        $this->last_attempt = null;
        $this->phone_verify_code = null;
        $this->phone_verified_at = now();
        $this->save();

        if ($this->wasChanged('phone_verified_at')) {
            return true;
        }

        return false;
    }

    /**
     * Get all of the transactions for the User
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user's fullname.
     *
     * @return string
     */
    protected function userData(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->data,
            set: fn($value) => ['data' => is_array($value)
                ? json_encode($value, JSON_FORCE_OBJECT)
                : $value],
        );
    }

    /**
     * Get temporary User
     */
    public function temp(): BelongsTo
    {
        return $this->belongsTo(TempUser::class, 'email', 'email');
    }

    /**
     * Get all of the scanHistory for the User
     */
    public function scanHistory(): HasMany
    {
        return $this->hasMany(ScanHistory::class);
    }

    /**
     * Get all of the USER's Reservations.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Transaction::class);
    } // TODO: rename to reservations

    /**
     * Get all of the formData for the User
     */
    public function formData(): HasMany
    {
        return $this->hasMany(FormData::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'requestor_id');
    }

    /**
     * The forms assigned to the user for review.
     */
    public function reviewForms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'form_reviewer')
            ->using(FormReviewer::class)
            ->withTimestamps();
    }

    /**
     * The forms assigned to the user for review.
     */
    public function reviewFormData(): BelongsToMany
    {
        return $this->belongsToMany(FormData::class, 'form_data_reviewer', 'form_data_id')
            ->using(GenericFormDataReviewer::class)
            ->withTimestamps();
    }

    /**
     * Scope to search for user.
     */
    public function scopeDoSearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        if (stripos($search, '@') === 0) {
            $query->where('username', str_ireplace('@', '', $search));
        } else {
            $query->where('firstname', 'like', "%{$search}%");
            $query->orWhere('lastname', 'like', "%{$search}%");
            $query->orWhereRaw(
                "LOWER(CONCAT_WS(' ', firstname, lastname)) like ?",
                ['%' . mb_strtolower($search) . '%']
            );
            $query->orWhere('email', $search);
        }
    }
}
