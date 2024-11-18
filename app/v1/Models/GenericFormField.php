<?php

namespace V1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericFormField extends Model
{
    use HasFactory;

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
}
