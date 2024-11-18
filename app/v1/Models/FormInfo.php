<?php

namespace V1\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class FormInfo extends Model
{
    use Fileable, HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'list' => 'array',
    ];

    public function registerFileable()
    {
        $this->fileableLoader('image', 'default');
    }

    /**
     * Get the URL to the fruit bay category's photo.
     *
     * @return string
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->files['image'],
        );
    }
}
