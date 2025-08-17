<?php

namespace App\Serializers;

use Exception;
use Stringable;
use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

class Translatable implements JsonSerializable, Arrayable, Stringable
{
    private array $data = [];

    public function __get(string $name)
    {
        if (!in_array($name, config('cubeta-starter.available_locales'))) {
            throw new Exception("Undefined Property [$name]  , try to add it to the cubeta-starter config file in available_locals array");
        }

        return $this->data["$name"] ?? "";
    }

    public function __set(string $name, mixed $value)
    {
        if (!in_array($name, config('cubeta-starter.available_locales'))) {
            throw new Exception("Undefined Property [$name]  , try to add it to the cubeta-starter config file in available_locals array");
        }

        if (!is_string($value)) {
            throw new Exception("Only String Values Allowed To Be Stored As Translatable Property");
        }

        $this->data["$name"] = $value;
    }

    /**
     * @throws Exception
     */
    public function __construct(string|array $value)
    {
        if (is_string($value) && Str::isJson($value)) {
            $this->data = json_decode($value, true);
        } elseif (is_string($value) && !Str::isJson($value)){
            $this->data[config('cubeta-starter.default_locale')] = $value;
        } else {
            $this->data = $value;
        }

        $this->validateLocaleKeys();
    }

    /**
     * @throws Exception
     */
    public static function create(string|array $value): Translatable
    {
        return new static($value);
    }

    public function translate(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{$locale};
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson(): bool|string
    {
        return json_encode($this->data, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toJson();
    }


    public static function fake($fakerType = "word"): Translatable
    {
        $result = [];
        foreach (config('cubeta-starter.available_locales') as $locale) {
            if ($locale == 'ar') {
                $result["$locale"] = fake('ar_SA')->{"$fakerType"};
            } else {
                $result["$locale"] = fake()->{"$fakerType"};
            }
        }

        return new self($result);
    }

    public function __toString(): string
    {
        return $this->translate();
    }

    /**
     * @throws Exception
     */
    private function validateLocaleKeys(): void
    {
        foreach ($this->data as $locale => $value) {
            if (!in_array($locale, config('cubeta-starter.available_locales'))) {
                throw new Exception("Undefined locale [$locale]  , try to add it to the cubeta-starter config file in available_locals array");
            }
        }
    }

    /**
     * this method returns a value even if a translation for the selected locale is not found.
     * if a corresponding value for the requested locale doesn't exist, it will loop on the available locales defined
     * in the config and get the first one with a value, finally if there is no value with any locale it will returns an
     * empty string
     * @param string|null $locale
     * @return string
     */
    public function forceTranslate(?string $locale = null): string
    {
        $value = $this->translate($locale);

        if ($value) {
            return $value;
        }

        foreach (config('cubeta-starter.available_locales') as $localeItem) {
            $value = $this->translate($localeItem);
            if (!empty($value)) {
                return $value;
            }
        }

        return "";
    }
}
