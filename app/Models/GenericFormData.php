<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected static function booted()
    {
        // static::creating(function ($item) {
        // });
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
    public function nameAttribute(): Attribute
    {
        return new Attribute(
            get: function () {
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

    public function scopeScanned($query, $scanned = true)
    {
        if ($scanned) {
            return $query->whereHas('scans');
        } else {
            return $query->whereDoesntHave('scans');
        }
    }
}
