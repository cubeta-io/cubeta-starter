<?php

namespace App\Casts;

use App\Serializers\Translatable as SerializersTranslatable;
use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Translatable implements CastsAttributes
{
    /**
     * Cast the given value.
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, mixed $value, array $attributes): mixed
    {
        return new SerializersTranslatable($value);
    }

    /**
     * Prepare the given value for storage.
     * @param array<string, mixed> $attributes
     * @throws Exception
     */
    public function set($model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof SerializersTranslatable) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
        } else {
            $arrVal = json_decode($value, true);
            if ($arrVal) {
                return $arrVal;
            } else {
                throw new Exception("Invalid Translatable Data , it should be either : array , json string , Translatable Object");
            }
        }
    }
}
