<?php

use Illuminate\Database\Eloquent\Model;

/**
 * this function return current locale correspond translation for the provided column
 * @param string $translationColumn must be a json string
 * @return mixed|null
 */
function getTranslation(string $translationColumn): mixed
{
    $locale = app()->getLocale();
    $translationArray = json_decode($translationColumn, true);
    return $translationArray[$locale] ?? ($translationArray[config('cubeta-starter.defaultLocale')] ?? 'there is no value correspond to the current locale or the default locale');
}
