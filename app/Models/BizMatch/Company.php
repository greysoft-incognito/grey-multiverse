<?php

namespace App\Models\BizMatch;

use App\Models\User;
use App\Traits\ModelCanExtend;
use Illuminate\Database\Eloquent\Builder;
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
    use Fileable;

    /** @use HasFactory<\Database\Factories\BizMatch\CompanyFactory> */
    use HasFactory;

    use ModelCanExtend;

    protected $fillable = [
        'name',
        'description',
        'industry_category',
        'country',
        'location',
        'services',
        'conference_objectives',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'services' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
        ];
    }

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

    /**
     * Retrieve the model for a bound value.
     *
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($value === 'authenticated') {
            return $this->where('user_id', auth('sanctum')->id())
                ->firstOrNew();
        }

        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasManyThrough
    {
        return $this->hasManyThrough(Appointment::class, User::class, 'invitee_id');
    }

    /**
     * Scope to search for user.
     */
    public function scopeDoSearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        if (stripos($search, '@') === 0) {
            $query->where('slug', str_ireplace('@', '', $search));
        } else {
            $query->where('name', 'like', "%{$search}%");
            $query->orWhereFullText('description', $search);
            $query->orWhereJsonContains('services', $search);
        }
    }
}
