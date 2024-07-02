<?php

namespace Cubeta\CubetaStarter\Enums;

enum MiddlewareArrayGroupEnum
{
    case GLOBAL;
    case WEB;
    case API;
    case ALIAS;

    public static function getAllValues()
    {
        return array_column(self::cases(), 'value');
    }
}
