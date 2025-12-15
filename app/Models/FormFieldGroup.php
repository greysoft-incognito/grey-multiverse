<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FormFieldGroup extends Model
{
    /** @use HasFactory<\Database\Factories\FormGroupFactory> */
    use HasFactory;
    use \App\Traits\Logger;


    public static function booted(): void
    {
        static::bootLogger();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'icon',
        'priority',
        'description',
        'authenticator',
        'requires_auth',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'name' => 'Default',
        'icon' => 'fas fa-home',
        'priority' => 0,
        'description' => '',
        'authenticator' => false,
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts()
    {
        return [
            'authenticator' => 'boolean',
            'requires_auth' => 'boolean',
        ];
    }

    /**
     * The users assigned as reviewers for the form.
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(FormField::class, 'form_field_group_form_field', null, 'form_field_id')
            ->using(FormFieldFieldGroup::class)
            ->orderBy('priority', 'desc')
            ->withTimestamps();
    }
}
