<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use JetBrains\PhpStorm\ArrayShape;

trait WebGeneratorHelper
{
    /**
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'show' => 'string', 'edit' => 'string', 'destroy' => 'string', 'store' => 'string', 'create' => 'string', 'data' => 'string', 'update' => 'string', 'base' => 'string', 'export' => 'string', 'import' => 'string', 'example' => 'example'])]
    protected function getRoutesNames(CubeTable $model, ?string $actor = null): array
    {
        $baseRouteName = $this->getRouteName($model, ContainerType::WEB, $actor);

        return [
            'index' => $baseRouteName . '.index',
            'show' => $baseRouteName . '.show',
            'edit' => $baseRouteName . '.edit',
            'destroy' => $baseRouteName . '.destroy',
            'store' => $baseRouteName . '.store',
            'create' => $baseRouteName . '.create',
            'data' => $baseRouteName . '.data',
            'update' => $baseRouteName . '.update',
            'export' => $baseRouteName . '.export',
            'import' => $baseRouteName . '.import',
            'example' => $baseRouteName . '.get.example',
            'base' => $baseRouteName,
        ];
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function getInputTagType(CubeAttribute $attribute): string
    {
        if (str_contains($attribute->name, "email")) return "email";
        elseif ($attribute->name == "password") return "password";
        elseif (in_array($attribute->name, ['phone', 'phone_number', 'home_number', 'work_number', 'tel', 'telephone'])
            || str_contains($attribute->name, "phone")) return "tel";
        elseif (str_contains($attribute->name, "url")) return "url";
        elseif (ColumnTypeEnum::isNumericType($attribute->type)) return "number";
        elseif (in_array($attribute->type, [ColumnTypeEnum::JSON->value, ColumnTypeEnum::STRING->value])) return "text";
        elseif (in_array($attribute->type, [ColumnTypeEnum::DATETIME->value, ColumnTypeEnum::TIMESTAMP->value])) return "datetime-local";
        elseif ($attribute->type == ColumnTypeEnum::DATE->value) return "date";
        elseif ($attribute->type == ColumnTypeEnum::TIME->value) return "time";
        elseif ($attribute->isFile()) return "file";
        else return "text";
    }
}
