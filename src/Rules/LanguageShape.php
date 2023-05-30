<?php

namespace Cubeta\CubetaStarter\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class LanguageShape implements ValidationRule
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }

        $translationArray = json_decode($value, true);

        // Check if the decoded JSON data is a simple object (not nested)
        if ($this->hasNestedArrays($translationArray)) {
            $fail("The :attribute must be a simple not nested json object");
        }

        $translationLanguages = array_keys($translationArray);
        $availableLanguages = config('cubeta-starter.available_locales');

        $theDifferenceBetweenTheProvidedLanguages = array_diff($translationLanguages, $availableLanguages);

        if (!count($theDifferenceBetweenTheProvidedLanguages) == 0) {
            $fail(implode(',', $theDifferenceBetweenTheProvidedLanguages) . " don't exist in your project languages");
        }

    }

    public function hasNestedArrays($array): bool
    {
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false;
    }
}
