<?php

namespace Cubeta\CubetaStarter\LogsMessages;

use Exception;

class Log
{
    private static array $logs = [];

    public static function add(Exception|Error|Info|Warning|string $log): void
    {
        self::$logs[] = $log;
    }

    public static function logs(): array
    {
        return self::$logs;
    }
}
