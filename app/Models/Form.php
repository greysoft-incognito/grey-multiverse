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
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Staudenmeir\EloquentJsonRelations\Relations\BelongsToJson;
use ToneflixCode\LaravelFileable\Traits\Fileable;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int,FormField> $fields
 * @property \Illuminate\Database\Eloquent\Collection<int,FormData> $data
 * @property \Illuminate\Database\Eloquent\Collection<int,FormInfo> $infos
 * @property \Illuminate\Database\Eloquent\Collection<int,LearningPath> $learningPaths
 * @property \Illuminate\Support\Collection<int,\Illuminate\Support\Stringable> $data_emails
 * @property string $name
 * @property string $logo_url
 * @property string $logo_url
 * @property bool $dont_notify
 * @property \Carbon\Carbon $deadline
 * @property bool $require_auth
 * @property array{auto_assign_reviewers:bool,base_url:string,sort_fields:array<int,string>,chartables:array,statcards:array,fields_map:array{name:string,email:string,phone:string}} $config
 * @property array<string,array{url:string,icon:string,label:string,name:string}> $socials
 */
class Form extends Model
{
    use Fileable, HasFactory, HasJsonRelationships, ModelCanExtend;

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
        'config' => '{
            "chartables": [],
            "statcards": [],
            "fields_map": { "name":"name","email":"email","phone":"phone" },
            "base_url": "",
            "sort_fields": [],
            "auto_assign_reviewers": false
        }',
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
     * Get all of the FormData for the Form
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<FormData>
     */
    public function data(): HasMany
    {
        return $this->hasMany(FormData::class);
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
        return $this->hasMany(FormField::class)->orderBy('priority', 'desc');
    }

    /**
     * Get all of the fields for the Form
     */
    public function fieldGroups(): HasMany
    {
        return $this->hasMany(FormFieldGroup::class)->orderBy('priority', 'desc');
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

    /**
     * Get the total points.
     *
     * @return string
     */
    protected function totalPoints(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->fields->sum(function ($field): int {
                    $fieldPoints = (int) $field->points;
                    $optionsPoints = 0;

                    // Calculate options points if options exist
                    if (!empty($field->options)) {
                        foreach ($field->options as $opt) {
                            if (isset($opt['points']) && ((int) $opt['points']) > 0) {
                                $optionsPoints += (int) $opt['points'];
                            }
                        }
                    }

                    // Add points only if there's a contribution
                    return ($optionsPoints > 0 ? $fieldPoints + $optionsPoints : $fieldPoints);
                });
            },
        );
    }

    public function socials(): Attribute
    {
        $parser = static fn($value, $name) => [
            'url' => str($value)->before('?')->toString(),
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
     * The sort fields for the form.
     */
    public function sortFields(): BelongsToJson
    {
        return $this->belongsToJson(FormField::class, 'config->sort_fields');
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
