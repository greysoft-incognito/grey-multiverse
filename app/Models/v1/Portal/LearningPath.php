<?php

namespace App\Models\v1\Portal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class LearningPath extends Model
{
    use HasFactory, Fileable;

    public function registerFileable()
    {
        $this->fileableLoader([
            'image' => 'default',
            'video' => 'default',
            'background' => 'default',
        ]);
    }

    /**
     * Get the transactable that owns the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function learnable()
    {
        return $this->morphTo();
    }

    /**
     * Get all of the courses for the LearningPath
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Courses::class);
    }
}
