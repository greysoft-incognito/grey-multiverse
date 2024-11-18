<?php

namespace App\Models\Portal;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Blog extends Model
{
    use Fileable, HasFactory;

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    public function registerFileable()
    {
        $this->fileableLoader([
            'image' => 'banner',
        ]);
    }

    /**
     * Get the portal that owns the Blog
     */
    public function portal(): BelongsTo
    {
        return $this->belongsTo(Portal::class);
    }

    /**
     * Get the user that owns the Blog
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
