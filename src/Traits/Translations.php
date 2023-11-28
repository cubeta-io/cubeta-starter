<?php

namespace App\Traits;

use Exception;

/**
 * @method getRawOriginal(string $translationColumn)
 * @method save()
 */
trait Translations
{
    /**
     * this function will update the corresponding translation for the provided locale
     * if the locale isn't provided it will work for the project current locale
     * if a corresponding translation didn't exist it will create one
     * if the provided local isn't in the cubeta-starter config file in the available locales array it will return an exception
     * @param string $translatableColumn
     * @param mixed $value
     * @param string|null $locale
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

    /**
     * it returns the corresponding value for the provided locale if it is not provided so for the current locale
     * and if there isn't a value corresponding to them, it returns the corresponding value for the default locale
     * defined in the config file of the package and if there isn't a corresponding value for it,
     * it returns a message informing you that there isn't
     *
     * @param string $translationColumn must be a json string
     * @param  ?string $locale
     * @return mixed|null
     */
    public function getTranslation(string $translationColumn, string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $translationArray = json_decode($this->getRawOriginal($translationColumn), true);

        if ($locale) {
            return $translationArray[$locale] ?? null;
        }

        return $translationArray[config('cubeta-starter.defaultLocale')] ?? 'there is no value correspond to the current locale or the default locale';
    }

    /**
     * translate the value directly
     * @param string|null $value must be a json string
     * @param string|null $locale
     * @return string|null
     */
    protected function translateValue(?string $value = null, ?string $locale = null): ?string
    {
        if (!$value) {
            return null;
        }

        $locale ??= app()->getLocale();

        $translationArray = json_decode($value, true);

        if ($locale) {
            return $translationArray[$locale] ?? null;
        }

        return $translationArray[config('cubeta-starter.defaultLocale')] ?? null;
    }

    /**
     * generate a fake data for translated column
     * @param string $fakerType the generated data type i.e : sentence|title|word....etc
     * @return bool|string the response would be json encoded
     */
    public function fakeTranslation(string $fakerType): bool|string
    {
        $result = [];
        foreach (config('cubeta-starter.available_locales') as $locale) {
            if ($locale == 'ar') {
                $result["$locale"] = fake('ar_SA')->{"$fakerType"};
            } else {
                $result["$locale"] = fake()->{"$fakerType"};
            }
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
