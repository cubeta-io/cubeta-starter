<?php

namespace App\Serializers;

use Exception;
use JsonSerializable;

class Translatable implements JsonSerializable
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
        if (is_string($value)) {
            $this->data = json_decode($value, true);
        } else {
            $this->data = $value;
        }
        $this->validateLocaleKeys();
    }

    public function translate(?string $locale = null)
    {
        $locale = $locale ?? config('cubeta-starter.defaultLocale');
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


    public static function fake($fakerType = "word"): bool|string
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
}
