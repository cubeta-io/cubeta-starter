<?php

namespace Cubeta\CubetaStarter\Logs;

use Exception;
use Throwable;

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

    public static function splitExceptions(): array
    {
        $logs = array_filter(self::$logs, fn($log) => (!$log instanceof Exception and !$log instanceof Throwable));
        $exceptions = array_filter(self::$logs, fn($log) => ($log instanceof Exception or $log instanceof Throwable));

        return [$logs, $exceptions];
    }

    public static function getExceptionMessage(Exception|Throwable $exception): string
    {
        return "Message : {$exception->getMessage()} \nFile: {$exception->getFile()}\nLine: {$exception->getLine()}\n";
    }

    public static function exceptionToHtml(Exception|Throwable $exception): string
    {
        return "<div class='w-100 d-flex gap-1 flex-column justify-content-between p-2 border border-danger rounded-3 border-2'>
                    <div class='w-100'><span class='bg-danger rounded-2 p-1 fw-bold'>Error</span> : {$exception->getMessage()}</div>
                    <div class='w-100'><span class='bg-danger-light'>File</span> : {$exception->getFile()}</div>
                    <div class='w-100'><span class='bg-danger-light'>Line</span> : {$exception->getLine()}</div>
                </div>";
    }
}
