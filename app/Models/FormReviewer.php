<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FormReviewer extends Pivot
{
    use \App\Traits\Logger;

    public static function booted(): void
    {
        static::bootLogger();
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'form_reviewer';
    }
}
