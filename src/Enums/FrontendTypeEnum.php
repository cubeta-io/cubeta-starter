<?php

namespace Cubeta\CubetaStarter\Enums;

enum FrontendTypeEnum: string
{
    case BLADE = "Blade";
    case REACT_TS = "React & Typescript";
    case NONE = "None";

    public static function getAllValues(): array
    {
        return array_column(self::cases() , 'value');
    }
}
