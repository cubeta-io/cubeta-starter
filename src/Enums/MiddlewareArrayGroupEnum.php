<?php

namespace Cubeta\CubetaStarter\Enums;

enum MiddlewareArrayGroupEnum
{
    public static function getAllValues()
    {
        return array_column(self::cases(), 'value');
    }
    case ALIAS;
    case API;
    case GLOBAL;
    case WEB;
}
