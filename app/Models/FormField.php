<?php

namespace App\Models;

use App\Services\FormPointsCalculator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

/**
 * @property bool $has_options
 * @property array<int,array{points:int,label:string,value:string}> $options
 */
class FormField extends Model
{
    use HasFactory, HasJsonRelationships, \App\Traits\Logger;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'points_script',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, string|null>
     */
    protected $attributes = [
        'points_script' => null,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points' => 'integer',
        'options' => 'array',
        'restricted' => 'boolean',
        'required' => 'boolean',
        'points_script' => 'string',
    ];

    public static function booted(): void
    {
        static::bootLogger();
    }

    /**
     * Get the form that owns the FormField
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function scopeEmail($query): void
    {
        if (isset($this->form->config['fields_map']['email'])) {
            $field = $this->form->config['fields_map']['email'] ?? 'email';
            $query->where('name', $field);

            return;
        }

        $query->where('type', 'email');
        $query->orWhere('name', 'email');
        $query->orWhere('name', 'email_address');
        $query->orWhere('name', 'like', '%emailaddress%');
        $query->orWhere('name', 'like', '%email_address%');
    }

    public function scopeFname($query)
    {
        $query->where('name', 'like', '%firstname%')
            ->orWhere('name', 'like', '%first_name%');

        return $query;
    }

    public function scopeLname($query)
    {
        $query->where('name', 'like', '%lastname%')
            ->orWhere('name', 'like', '%last_name%');

        return $query;
    }

    public function scopeFullname($query)
    {
        if (isset($this->form->config['fields_map']['name'])) {
            $field = $this->form->config['fields_map']['name'] ?? 'fullname';
            $query->where('name', $field);

            return;
        }

        $query->where('name', 'like', '%fullname%')
            ->orWhere('name', 'like', '%full_name%')
            ->where('name', 'like', '%name%');

        return $query;
    }

    public function scopePhone($query)
    {
        if (isset($this->form->config['fields_map']['phone'])) {
            $field = $this->form->config['fields_map']['phone'] ?? 'phone';
            $query->where('name', $field);

            return;
        }

        $query->where(function ($q) {
            $q->where('type', 'tel')
                ->orWhere('type', 'number');
        })->where(function ($q) {
            $q->orWhere('name', 'phone')
                ->orWhere('name', 'phonenumber')
                ->orWhere('name', 'phone_number')
                ->orWhere('name', 'like', '%phone%')
                ->orWhere('name', 'like', '%phonenumber%')
                ->orWhere('name', 'like', '%phone_number%');
        });

        return $query;
    }

    /**
     * Cast the expected value to the expected value type.
     *
     * @return string
     */
    public function expectedValue(): Attribute
    {
        return Attribute::make(function ($val) {
            return $val ? match ($this->expected_value_type) {
                'array' => json_encode($val),
                'integer' => (int) $val,
                'boolean' => (bool) $val,
                default => $val,
            } : null;
        });
    }

    /**
     * Calculate the total points for the field.
     *
     * @return string
     */
    public function totalPoints(): Attribute
    {
        return Attribute::make(function () {
            return (new FormPointsCalculator())->calculateFieldTotalPoints($this);
        });
    }

    /**
     * Determine the expected value type based on element, type, and options.
     *
     * @return string
     */
    public function expectedValueType(): Attribute
    {
        return Attribute::make(function () {
            $typeMapping = [
                'select' => 'string',
                'checkboxgroup' => 'array',
                'radiogroup' => 'string',
                'locale' => 'string',
                'input' => [
                    'text' => 'string',
                    'number' => 'integer',
                    'email' => 'string',
                    'password' => 'string',
                    'checkbox' => 'boolean',
                    'date' => 'string', // Consider 'date' => 'string|Carbon' if you parse dates
                ],
            ];

            // Handle specific elements
            if (isset($typeMapping[$this->element]) && is_array($typeMapping[$this->element])) {
                // Handle input type mapping
                return $typeMapping[$this->element][$this->type] ?? 'mixed';
            }

            // Handle other elements
            return $typeMapping[$this->element] ?? 'string';
        });
    }

    /**
     * Determine the formfield has options
     *
     * @return string
     */
    public function hasOptions(): Attribute
    {
        return Attribute::make(function () {
            return in_array($this->element, ['select', 'checkboxgroup', 'radiogroup']);
        });
    }

    /**
     * The groups this field is attached to.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(FormFieldGroup::class, 'form_field_group_form_field', 'form_field_id')
            ->using(FormFieldFieldGroup::class)
            ->withTimestamps();
    }

    /**
     * Indicates if field has been added to a group
     *
     * @return string
     */
    public function isGrouped(): Attribute
    {
        return Attribute::make(fn() => $this->groups()->exists() || $this->form->fieldGroups()->doesntExist());
    }

    public function subValues(): Attribute
    {
        $name = $this->name ?? 'value';

        return Attribute::make(
            fn() => FormData::query()
                ->select("data->{$name} as {$name}")
                ->whereFormId($this->form_id)
                ->groupBy($name)
                ->pluck($name)
                ->map(fn($e) => valid_json($e ?? '') ? json_decode($e) : $e)
                ->flatten()
                ->unique()
        );
    }
}
