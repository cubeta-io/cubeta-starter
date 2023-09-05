<?php

namespace App\Traits;

use Exception;

trait Translations
{
    /**
     * this function will update the corresponding translation for the provided locale
     * if the locale isn't provided it will work for the project current locale
     * if a corresponding translation didn't exist it will create one
     * if the provided local isn't in the cubeta-starter config file in the available locales array it will return an exception
     * @param  string      $translatableColumn
     * @param  mixed       $value
     * @param  string|null $locale
     * @return self
     * @throws Exception
     */
    public function updateTranslation(string $translatableColumn, mixed $value, string $locale = null): self
    {
        if (!$locale || $locale == '') {
            $locale = app()->getLocale();
        }

        if (!in_array($locale, config('cubeta-starter.available_locales'))) {
            throw new Exception(
                "The Provided locale isn't in Your Available Locales Array in the cubeta-starter config file",
                404
            );
        }

        $translationsArray = json_decode($this->getRawOriginal($translatableColumn), true);
        $translationsArray[$locale] = $value;

        $this->{"{$translatableColumn}"} = json_encode($translationsArray);
        $this->save();

        return $this;
    }
}
