<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

/**
 * Class GenericFormData
 *
 * @additions @property int $user_id
 */
class GenericFormData extends Model
{
    use HasFactory, Notifiable; 

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'scan_date' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'scan_date',
        'form_id',
        'user_id',
        'data',
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
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'form_data';
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
            ->orWhere('key', $value)
            ->firstOrFail();
    }

    /**
     * Get the form that owns the GenericFormData
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the name of user from the GenericFormData field
     */
    public function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->form) {
                    return '';
                }

                $fname_field = $this->form->fields()->fname()->first();
                $lname_field = $this->form->fields()->lname()->first();
                $fullname_field = $this->form->fields()->fullname()->first();
                $email_field = $this->form->fields()->email()->first();
                $name = collect([
                    $this->data[$fname_field->name ?? '--'] ?? '',
                    $this->data[$lname_field->name ?? '--'] ?? '',
                    ! $fname_field && ! $lname_field ? ($this->data[$fullname_field->name ?? $email_field->name ?? '--'] ?? '') : '',
                ])->filter(fn ($name) => $name !== '')->implode(' ');

                return $name;
            },
        );
    }

    /**
     * Get the name of user from the GenericFormData field
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
        if ($this->user) {
            return [$this->user->email => $this->user->fullname];
        } else {
            $email_field = $this->form->fields()->email()->first();
            $fname_field = $this->form->fields()->fname()->first();
            $lname_field = $this->form->fields()->lname()->first();
            $fullname_field = $this->form->fields()->fullname()->first();

            $name = collect([
                $this->data[$fname_field->name ?? '--'] ?? '',
                $this->data[$lname_field->name ?? '--'] ?? '',
                ! $fname_field && ! $lname_field ? $this->data[$fullname_field->name ?? $email_field->name ?? '--'] : '',
            ])->filter(fn ($name) => $name !== '')->implode(' ');

            // Return email address and name...
            if (isset($this->data[$email_field->name ?? '--'])) {
                return [$this->data[$email_field->name] ?? null => $name];
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
            $phone_field = $this->form->fields()->phone()->first();

            return $this->data[$phone_field->name] ?? null;
        }
    }

    // Load scans
    public function scans()
    {
        return $this->hasMany(ScanHistory::class, 'form_data_id', 'id');
    }

    /**
     * Get the user that owns the GenericFormData
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeScanned(Builder $query, bool $scanned = true)
    {
        if ($scanned) {
            return $query->whereHas('scans');
        } else {
            return $query->whereDoesntHave('scans');
        }
    }

    public function scopeForReviewer(Builder $query, User|string $user): void
    {
        if ($user instanceof $user) {
            $user = $user->id;
        }

        $query->whereHas('reviewers', fn($q) => $q->where('users.id', $user));
    }
}
