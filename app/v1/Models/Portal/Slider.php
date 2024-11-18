<?php

namespace V1\Models\Portal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Slider extends Model
{
    use Fileable, HasFactory;

    protected $casts = [
        'link' => 'array',
        'list' => 'array',
    ];

    public function registerFileable()
    {
        $this->fileableLoader([
            'image' => 'banner',
        ]);
    }

    /**
     * Get the section that owns the Slider
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
