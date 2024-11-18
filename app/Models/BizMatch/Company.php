<?php

namespace App\Models\BizMatch;

use App\Models\User;
use App\Traits\ModelCanExtend;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use ToneflixCode\LaravelFileable\Traits\Fileable;

/**
 * @property \Illuminate\Database\Eloquent\Collection<Appointment> $appointments
 * @property \App\Models\User $user
 */
class Company extends Model
{
    /** @use HasFactory<\Database\Factories\BizMatch\CompanyFactory> */
    use Fileable, HasFactory, ModelCanExtend;

    public function registerFileable()
    {
        $this->fileableLoader(
            file_field: ['image' => 'logo'],
            applyDefault: true,
        );
    }

    public static function registerEvents()
    {
        static::creating(function (self $model) {
            $model->slug ??= $model->generateUsername($model->name, 'slug', '-');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasManyThrough
    {
        return $this->hasManyThrough(Appointment::class, User::class, 'invitee_id');
    }
}