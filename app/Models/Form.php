<?php

namespace App\Models;

use App\Models\Portal\LearningPath;
use App\Traits\ModelCanExtend;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Form extends Model
{
    use Fileable, HasFactory, ModelCanExtend;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deadline' => 'datetime',
        'socials' => 'array',
        'dont_notify' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'banner_url',
        'logo_url',
    ];

    public function registerFileable()
    {
        $this->fileableLoader([
            'banner' => 'banner',
            'logo' => 'logo',
        ]);
    }

    public static function registerEvents()
    {
        static::creating(function (self $model) {
            $model->slug ??= $model->generateUsername($model->title, 'slug', '-');
        });
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function bannerUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->images['banner'],
        );
    }

    /**
     * Get all of the data for the Form
     */
    public function data(): HasMany
    {
        return $this->hasMany(GenericFormData::class);
    }

    /**
     * Get the emails that recieve data reports.
     *
     * @return string
     */
    protected function dataEmails(): Attribute
    {
        return Attribute::make(
            get: fn ($a) => str($a ?? '')->explode(',')->map(fn ($e) => str($e)->trim()),
        );
    }

    /**
     * Get all of the fields for the Form
     */
    public function fields(): HasMany
    {
        return $this->hasMany(GenericFormField::class);
    }

    /**
     * Get all of the infos for the Form
     */
    public function infos(): HasMany
    {
        return $this->hasMany(FormInfo::class)->orderBy('priority');
    }

    /**
     * Get all of the pages for the Portal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function learningPaths(): MorphMany
    {
        return $this->morphMany(LearningPath::class, 'learnable');
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->images['logo'],
        );
    }

    public function socials(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => collect($value)->map(function ($value, $key) {
                return [
                    'url' => $value,
                    'icon' => "fas fa-$key",
                    'label' => '@'.str($value)->explode('/')->last(),
                ];
            })->toArray()
        );
    }
}
