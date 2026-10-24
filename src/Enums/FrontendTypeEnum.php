<?php

namespace Cubeta\CubetaStarter\Enums;

enum FrontendTypeEnum: string
{
    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    case BLADE = 'Blade';
    case NONE = 'None';
    case REACT_TS = 'React & Typescript';
}
