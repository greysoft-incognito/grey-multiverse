<?php

namespace App\Services\PointsScript\Rules;

use App\Services\PointsScript\PointsScriptValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PointsScript implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_null($value)) {
            $validator = new PointsScriptValidator();

            try {
                $validator->validate($value);
            } catch (\InvalidArgumentException $e) {
                $fail($e->getMessage());
            }
        }
    }
}
