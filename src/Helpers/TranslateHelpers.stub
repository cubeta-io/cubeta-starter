<?php

/**
 * it returns the corresponding value for the provided locale if it is not provided so for the current locale
 * and if there isn't a value corresponding to them, it returns the corresponding value for the default locale
 * defined in the config file of the package and if there isn't a corresponding value for it,
 * it returns a message informing you that there isn't
 *
 * @param  string     $translationColumn must be a json string
 * @param  ?string    $locale
 * @return mixed|null
 */
function getTranslation(string $translationColumn, string $locale = null): mixed
{
    $locale ??= app()->getLocale();
    $translationArray = json_decode($translationColumn, true);

    if ($locale) {
        return $translationArray[$locale] ?? null;
    }

    return $translationArray[config('cubeta-starter.defaultLocale')] ?? 'there is no value correspond to the current locale or the default locale';
}
