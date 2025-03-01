<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FormFieldGroup extends Model
{
    /** @use HasFactory<\Database\Factories\FormGroupFactory> */
    use HasFactory;

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
