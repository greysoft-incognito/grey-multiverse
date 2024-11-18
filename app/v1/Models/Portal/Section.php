<?php

namespace V1\Models\Portal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Section extends Model
{
    use Fileable, HasFactory;

    protected $casts = [
        'link' => 'array',
        'list' => 'array',
    ];

    public function registerFileable()
    {
        $this->fileableLoader([
            'image' => 'default',
            'image2' => 'default',
            'background' => 'default',
        ]);
    }

    /**
     * Get the portal_page that owns the Section
     */
    public function portal_page(): BelongsTo
    {
        return $this->belongsTo(PortalPage::class);
    }

    /**
     * Get all of the cards for the Section
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get all of the sliders for the Section
     */
    public function sliders(): HasMany
    {
        return $this->hasMany(Slider::class);
    }
}
