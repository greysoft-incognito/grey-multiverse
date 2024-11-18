<?php

namespace V1\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\ModelCanExtend;
use Database\Factories\v1\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use ToneflixCode\LaravelFileable\Traits\Fileable;
use V1\Traits\Permissions;

class User extends Authenticatable
{
    use Fileable, HasApiTokens, HasFactory, HasRoles, ModelCanExtend, Notifiable, Permissions;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'phone',
        'password',
        'firstname',
        'lastname',
        'company',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'privileges' => 'array',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function registerFileable()
    {
        $this->fileableLoader('image', 'avatar');
    }

    public static function registerEvents()
    {
        static::creating(function (self $model) {
            $userName = str($model->email)->before('@');
            $model->username ??= $model->generateUsername($userName);
            unset($model->privileges);
        });
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->files['image'],
        );
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn () => collect([$this->firstname, $this->lastname])->filter()->join(' '),
        );
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function privileges(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getRoleNames(),
            set: function ($roles) {
                $roles = Role::whereIn('name', $roles)->orWhereIn('id', $roles)->pluck('name');
                $this->syncRoles($roles);
            }
        );
    }

    /**
     * Get the company name
     *
     * @return string
     */
    protected function company(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->company_name,
            set: function ($value) {
                return [
                    'company_name' => $value
                ]
            }
        );
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
    }

    /**
     * Get all of the USER's TRANSACTIONS.
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    /**
     * Get all of the formData for the User
     */
    public function formData(): HasMany
    {
        return $this->hasMany(GenericFormData::class);
    }
}