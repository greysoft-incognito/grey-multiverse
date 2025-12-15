<?php

namespace V1\Models\Portal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Card extends Model
{
    use Fileable, HasFactory;

    protected $casts = [
        'infos' => 'array',
    ];

    public function registerFileable()
    {
        $this->fileableLoader([
            'image' => 'default',
        ]);
    }

    /**
     * Get the section that owns the Card
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
