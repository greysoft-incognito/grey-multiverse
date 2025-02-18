<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FormReviewer extends Pivot
{
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
