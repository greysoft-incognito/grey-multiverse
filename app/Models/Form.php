<?php

namespace App\Models;

use App\Models\Portal\LearningPath;
use App\Traits\ModelCanExtend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ToneflixCode\LaravelFileable\Traits\Fileable;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int,GenericFormField> $fields
 * @property \Illuminate\Database\Eloquent\Collection<int,GenericFormData> $data
 * @property \Illuminate\Database\Eloquent\Collection<int,FormInfo> $infos
 * @property \Illuminate\Database\Eloquent\Collection<int,LearningPath> $learningPaths
 * @property \Illuminate\Support\Collection<int,\Illuminate\Support\Stringable> $data_emails
 * @property string $name
 * @property string $logo_url
 * @property string $logo_url
 * @property bool $dont_notify
 * @property \Carbon\Carbon $deadline
 * @property bool $require_auth
 * @property array<string,array{url:string,icon:string,label:string,name:string}> $socials
 */
class Form extends Model
{
    use Fileable, HasFactory, ModelCanExtend;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'banner_url',
        'logo_url',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'config' => '{ "chartables": [], "statcards": [] }',
        'require_auth' => false,
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts()
    {
        return [
            'config' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
            'socials' => 'array',
            'deadline' => 'datetime',
            'dont_notify' => 'boolean',
            'require_auth' => 'boolean',
        ];
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
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

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
            get: fn() => $this->images['banner'],
        );
    }

    /**
     * Get all of the GenericFormData for the Form
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<GenericFormData>
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
            get: fn($a) => str($a ?? '')->explode(',')->map(fn($e) => str($e)->trim()),
        );
    }

    /**
     * Get all of the fields for the Form
     */
    public function fields(): HasMany
    {
        return $this->hasMany(GenericFormField::class)->orderBy('priority', 'desc');
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
            get: fn() => $this->images['logo'],
        );
    }

    public function socials(): Attribute
    {
        $parser = static fn($value, $name) => [
            'url' => str($value)->before('?'),
            'icon' => "fas fa-$name",
            'name' => $name,
            'label' => '@' . str(str($value)->explode('/')->last())->before('?'),
        ];

        return Attribute::make(
            get: fn($value) => collect($value)->map(function ($value, $name) use ($parser) {
                if (json_validate($value)) {
                    return collect(json_decode($value))->map(fn($v, $n) => $parser($v, $n))->values();
                }

                return $parser($value, $name);
            })->toArray()[0] ?? []
        );
    }

    /**
     * The users assigned as reviewers for the form.
     */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'form_reviewer')
            ->using(FormReviewer::class)
            ->withTimestamps();
    }

    public function scopeForReviewer(Builder $query, User|string $user): void
    {
        if ($user instanceof $user) {
            $user = $user->id;
        }

        $query->whereHas('reviewers', fn($q) => $q->where('users.id', $user));
    }
}
