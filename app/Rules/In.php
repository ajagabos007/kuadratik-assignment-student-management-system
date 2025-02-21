<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class In implements ValidationRule
{
    protected $values;
    protected $caseSensitive;

    /**
     * Create a new rule instance.
     *
     * @param array $values
     * @param bool $caseSensitive
     */
    public function __construct(array $values, bool $caseSensitive = false)
    {
        $this->values = $values;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         // If case-sensitive, directly check the array
         if ($this->caseSensitive) {
            if (!in_array($value, $this->values)) {
                $fail("The $attribute must be one of the allowed values.");
            }
        } else {
            // If case-insensitive, normalize the case for both the value and the array
            $normalizedValue = strtolower($value);
            $normalizedValues = array_map('strtolower', $this->values);

            if (!in_array($normalizedValue, $normalizedValues)) {
                $fail("The $attribute must be one of the allowed values (case-insensitive).");
            }
        }
    }
}