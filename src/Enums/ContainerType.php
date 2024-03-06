<?php

namespace Cubeta\CubetaStarter\Enums;

final class ContainerType
{
    public const API = 'api';

    public const BOTH = 'both';

    public const WEB = 'web';

    public const ALL = [
        self::API,
        self::WEB,
        self::BOTH,
    ];

    public static function isApi(string $container): bool
    {
        return $container == self::API || $container == self::BOTH;
    }

    public static function isWeb(string $container): bool
    {
        return $container == self::WEB || $container == self::BOTH;
    }
}
