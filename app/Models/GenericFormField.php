<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenericFormField extends Model
{
    use HasFactory;

    protected $table = 'form_fields';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
        'restricted' => 'boolean',
        'required' => 'boolean',
    ];

    /**
     * Get the form that owns the GenericFormData
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function scopeEmail($query)
    {
        $query->where('type', 'email');
        $query->orWhere('name', 'email');
        $query->orWhere('name', 'email_address');
        $query->orWhere('name', 'like', '%emailaddress%');
        $query->orWhere('name', 'like', '%email_address%');

        return $query;
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
        $query->where('name', 'like', '%fullname%')
            ->orWhere('name', 'like', '%full_name%')
            ->where('name', 'like', '%name%');

        return $query;
    }

    public function scopePhone($query)
    {
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
     * Determine the expected value type based on element, type, and options.
     *
     * @return string
     */
    public function ExpectedValueType(): Attribute
    {
        // Define the expected types based on element and type
        $typeMapping = [
            'select' => 'string',
            'checkboxgroup' => 'array',
            'radiogroup' => 'string',
            'input' => [
                'text' => 'string',
                'number' => 'integer',
                'email' => 'string',
                'password' => 'string',
                'checkbox' => 'boolean',
                'date' => 'string', // or Carbon instance depending on your use case
            ],
        ];

        return Attribute::make(function () use ($typeMapping) {
            // Handle specific elements
            if (isset($typeMapping[$this->element])) {
                if (is_array($typeMapping[$this->element])) {
                    // Handle input type mapping
                    return $typeMapping[$this->element][$this->type] ?? 'mixed';
                }

                return $typeMapping[$this->element];
            }

            // Default to string if no mapping is found
            return 'string';
        });
    }
}
