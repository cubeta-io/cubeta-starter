<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;

trait WebGeneratorHelper
{
    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function getInputTagType(CubeAttribute $attribute): string
    {
        if (str_contains($attribute->name, "email")) {
            return "email";
        } elseif ($attribute->name == "password") {
            return "password";
        } elseif (in_array($attribute->name, ['phone', 'phone_number', 'home_number', 'work_number', 'tel', 'telephone'])
            || str_contains($attribute->name, "phone")) {
            return "tel";
        } elseif (str_contains($attribute->name, "url")) {
            return "url";
        } elseif (ColumnTypeEnum::isNumericType($attribute->type)) {
            return "number";
        } elseif (in_array($attribute->type, [ColumnTypeEnum::JSON->value, ColumnTypeEnum::STRING->value])) {
            return "text";
        } elseif (in_array($attribute->type, [ColumnTypeEnum::DATETIME->value, ColumnTypeEnum::TIMESTAMP->value])) {
            return "datetime-local";
        } elseif ($attribute->type == ColumnTypeEnum::DATE->value) {
            return "date";
        } elseif ($attribute->type == ColumnTypeEnum::TIME->value) {
            return "time";
        } elseif ($attribute->isFile()) {
            return "file";
        } else {
            return "text";
        }

    }
}
