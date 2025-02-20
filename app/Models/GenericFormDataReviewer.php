<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GenericFormDataReviewer extends Pivot
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'form_data_reviewer';
    }
}
