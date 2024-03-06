<?php

namespace Cubeta\CubetaStarter\Logs;

use Exception;

class CubeLog
{
    private static array $logs = [];

    public static function add(Exception|CubeError|CubeInfo|CubeWarning|string $log): void
    {
        self::$logs[] = $log;
    }

    public static function logs(): array
    {
        return self::$logs;
    }

    public static function flush(): void
    {
        self::$logs = [];
    }
}
