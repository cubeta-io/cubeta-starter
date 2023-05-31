<?php

/**
 * this function return current locale correspond translation for the provided column
 *
 * @param  string  $translationColumn must be a json string
 * @return mixed|null
 */
//TODO:remember to put a locale parameter and remember to add accept language to the postman collection
function getTranslation(string $translationColumn): mixed
{
    $locale = app()->getLocale();
    $translationArray = json_decode($translationColumn, true);

    return $translationArray[$locale] ?? ($translationArray[config('cubeta-starter.defaultLocale')] ?? 'there is no value correspond to the current locale or the default locale');
}
