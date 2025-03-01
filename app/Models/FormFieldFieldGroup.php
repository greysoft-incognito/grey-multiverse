<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FormFieldFieldGroup extends Pivot
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'requires_auth' => 'boolean',
    ];
}
