<?php

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTranslatableJson implements ValidationRule
{
    public function hasNestedArrays($array): bool
    {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }

        return false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            if (!$value) {
                return;
            }

            if (!is_array($value)) {
                $translationArray = json_decode($value, true);
            } else {
                $translationArray = $value;
            }

            // Check if the decoded JSON data is a simple object (not nested)
            if ($this->hasNestedArrays($translationArray)) {
                $fail('The :attribute must be a simple not nested json object');
            }

            $translationLanguages = array_keys($translationArray);
            $availableLanguages = config('cubeta-starter.available_locales');

            $theDifferenceBetweenTheProvidedLanguages = array_diff($translationLanguages, $availableLanguages);

            if (!count($theDifferenceBetweenTheProvidedLanguages) == 0) {
                $fail(implode(',', $theDifferenceBetweenTheProvidedLanguages) . " doesn't exist in your project locales");
            }
        } catch (Exception) {
            $fail("invalid {$attribute}");
        }
    }
}
