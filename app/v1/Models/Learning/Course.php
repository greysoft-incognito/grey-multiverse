<?php

namespace V1\Models\Learning;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    /**
     * Get the learning_path that owns the Courses
     */
    public function learning_path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }
}
