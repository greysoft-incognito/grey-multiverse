<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber;
use ToneflixCode\DbConfig\Models\Fileable;

/**
 * Class FormData
 *
 * @additions @property int $user_id
 */
class FormData extends Model
{
    use HasFactory, Notifiable, \App\Traits\Logger;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_country',
        'scan_date',
        'form_id',
        'user_id',
        'status',
        'draft',
        'data',
        'rank',
        'key',
    ];

    /**
     * The attributes to be appended
     *
     * @var array
     */
    protected $appends = [
        'fullname',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'data' => '{}',
        'draft' => '{"draft_form_data": true}',
        'phone_country' => 'NG',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => \App\Casts\AsFormDataCollection::class,
            'draft' => \Illuminate\Database\Eloquent\Casts\AsArrayObject::class,
            'rank' => 'integer',
            'scan_date' => 'datetime',
        ];
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'form_data';
    }

    public static function booted(): void
    {
        static::bootLogger();
        static::addGlobalScope('submitted', function (Builder $builder) {
            $builder->whereNot('status', 'pending');
            $builder->orWhereNull('draft');
            $builder->orWhereNull('draft->draft_form_data');
            $builder->orWhereJsonContains('draft->draft_form_data', false);
        });

        static::created(function (self $model) {
            if (Arr::get($model->form->config, 'auto_assign_reviewers')) {
                $ids = $model->form->reviewers()->take(dbconfig('auto_assign_reviewers', 2) ?: 2)->pluck('users.id');
                $model->reviewers()->sync($ids);
            }
        });

        static::saving(function (self $model) {
            $model->rank = $model->calculatePoints();
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
        return $this
            ->withDraft()
            ->where('id', $value)
            ->orWhere('key', $value)
            ->firstOrFail();
    }

    /**
     * Calculate the total point earned by the user
     * based on their form entries.
     */
    protected function calculatePoints(): int
    {
        return $this->form->fields->reduce(function (int $total, $field): int {
            $fieldValue = $this->data[$field->name] ?? null;

            // Skip if no value exists
            if ($fieldValue === null) {
                return $total;
            }

            $fieldPoints = (int) $field->points;
            $optionsPoints = 0;

            // Calculate options points if options exist
            if (! empty($field->options)) {
                if (is_array($fieldValue)) {
                    $optionsPoints = array_reduce(
                        $field->options,
                        function (int $sum, $opt) use ($fieldValue): int {
                            return $sum + (isset($opt['value'], $opt['points']) &&
                                in_array($opt['value'], $fieldValue, true)
                                ? (int) $opt['points']
                                : 0);
                        },
                        0
                    );
                } else {
                    foreach ($field->options as $opt) {
                        if (
                            isset($opt['value'], $opt['points']) &&
                            $opt['value'] === $fieldValue
                        ) {
                            $optionsPoints = (int) $opt['points'];
                            break;
                        }
                    }
                }
            }

            // Add points only if there's a contribution
            return $total + ($optionsPoints > 0 ? $fieldPoints + $optionsPoints : $fieldPoints);
        }, 0);
    }

    /**
     * get the points percentage score for this submission
     */
    public function score(): Attribute
    {
        return Attribute::make(
            get: fn() => round((($this->rank ?: 1) / ($this->form->total_points ?: 1)) * 100, 1)
        );
    }

    /**
     * Get the form that owns the FormData
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the name of user from the FormData field
     */
    public function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->form) {
                    return '';
                }

                if (isset($this->form->config['fields_map']['name'])) {
                    return $this->data[$this->form->config['fields_map']['name'] ?? '--'] ?? '';
                }

                $fname_field = $this->form->fields()->fname()->first();
                $lname_field = $this->form->fields()->lname()->first();
                $fullname_field = $this->form->fields()->fullname()->first();
                $email_field = $this->form->fields()->email()->first();
                $name = collect([
                    $this->data[$fname_field->name ?? '--'] ?? '',
                    $this->data[$lname_field->name ?? '--'] ?? '',
                    ! $fname_field && ! $lname_field ? ($this->data[$fullname_field->name ?? $email_field->name ?? '--'] ?? '') : '',
                ])->filter(fn($name) => $name !== '')->implode(' ');

                return $name;
            },
        );
    }

    /**
     * Get the firstname of user from the FormData field
     */
    public function firstname(): Attribute
    {
        return Attribute::make(
            get: fn() => str($this->name)->before(' ')->toString()
        );
    }

    /**
     * Get the lastname of user from the FormData field
     */
    public function lastname(): Attribute
    {
        return Attribute::make(
            get: fn() => str($this->name)->explode(' ')->count() > 1 ? str($this->name)->after(' ')->toString() : ''
        );
    }

    /**
     * Get the email of user from the FormData field
     */
    public function email(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->form) {
                    return '';
                }

                if (isset($this->form->config['fields_map']['email'])) {
                    return $this->data[$this->form->config['fields_map']['email'] ?? '--'] ?? '';
                }

                $field = $this->form->fields()->email()->first();

                return $this->data[$field->name ?? ''] ?? null;
            },
        );
    }

    /**
     * Get the phone number of user from the FormData field
     */
    public function phone(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->form) {
                    return '';
                }

                if (isset($this->form->config['fields_map']['phone'])) {
                    $phone = $this->data[$this->form->config['fields_map']['phone'] ?? '--'] ?? null;
                } else {
                    $field = $this->form->fields()->phone()->first();

                    $phone = $this->data[$field->name ?? ''] ?? null;
                }

                try {
                    // return $phone ? new PhoneNumber($phone, [$this->phone_country ?: 'NG', 'INTERNATIONAL']) : null;
                } catch (NumberParseException) {
                    return $phone;
                }
            },
        );
    }

    /**
     * Get the name of user from the FormData field
     */
    public function fullname(): Attribute
    {
        return $this->name();
    }

    /**
     * The users assigned as reviewers for the form data.
     */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'form_data_reviewer', 'form_data_id')
            ->using(GenericFormDataReviewer::class)
            ->withTimestamps();
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail()
    {
        // Return email address and name...
        if ($this->user) {
            return [$this->user->email => $this->user->fullname];
        } else {
            if (isset($this->email)) {
                return [$this->email => $this->name ?? $this->email];
            }
        }

        return false;
    }

    /**
     * Route notifications for the twillio channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForTwilio()
    {
        if ($this->user) {
            return [$this->user->phone];
        } else {
            return $this->phone;
        }
    }

    public function files()
    {
        return $this->morphMany(Fileable::class, 'fileable');
    }

    /**
     * Get the user that reviewed the FormData
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Load scans
    public function scans()
    {
        return $this->hasMany(ScanHistory::class, 'form_data_id', 'id');
    }

    /**
     * Get the user that owns the FormData
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to sort results by ranking
     *
     * @param  string<'top'|'least'>  $type
     * @return void
     */
    public function scopeRanked(Builder $query, string $type)
    {
        return $query->reorder()->orderBy('rank', $type === 'top' ? 'desc' : 'asc');
    }

    /**
     * Scope to return only draft
     *
     * @param  string<'top'|'least'>  $type
     * @return void
     */
    public function scopeDrafts(Builder $query)
    {
        $query->withoutGlobalScope('submitted');
        $query->where('status', 'pending');
        $query->orWhereJsonContains('draft->draft_form_data', true);
    }

    public function scopeForReviewer(Builder $query, User|string $user): void
    {
        if ($user instanceof $user) {
            $user = $user->id;
        }

        $query->whereHas('reviewers', fn($q) => $q->where('users.id', $user));
    }

    public function scopeScanned(Builder $query, bool $scanned = true)
    {
        if ($scanned) {
            return $query->whereHas('scans');
        } else {
            return $query->whereDoesntHave('scans');
        }
    }

    public function scopeWithDraft(Builder $query)
    {
        $query->withoutGlobalScope('submitted');
    }

    public function scopeSorted(Builder $query, string $sort_field, string $sort_value)
    {
        $query->whereJsonContains("data->{$sort_field}", $sort_value);
    }

    /**
     * Scope to search for user.
     */
    public function scopeDoSearch(Builder $query, ?string $search, ?Form $form): void
    {
        if (! $search) {
            return;
        }

        $nameField = collect($this->form->config['fields_map'] ?? $form->config['fields_map'] ?? [])->get('name', 'name');

        $query->where("data->{$nameField}", 'like', "%{$search}%");
    }
}
