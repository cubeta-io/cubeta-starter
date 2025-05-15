<?php

namespace Cubeta\CubetaStarter\Logs;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\WrongEnvironment;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessGenerating;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Logs\Warnings\ContentNotFound;
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

    public static function handleExceptionsAsErrors(): void
    {
        foreach (self::$logs as $key => $log) {
            if ($log instanceof Exception or $log instanceof Throwable) {
                self::$logs[$key] = new CubeError($log->getMessage(), $log->getFile());
            }
        }
    }

    public static function contentAppended(string $content, string|CubePath $path): void
    {
        self::add(new ContentAppended(
            $content,
            $path instanceof CubePath
                ? $path->fullPath
                : $path
        ));
    }

    public static function contentAlreadyExists(string $content, string|CubePath $path, ?string $context = null): void
    {
        self::add(new ContentAlreadyExist(
            $content,
            $path instanceof CubePath
                ? $path->fullPath
                : $path,
            $context
        ));
    }

    public static function contentNotFound(string $content, string $filePath, ?string $context = null): void
    {
        self::add(
            new ContentNotFound(
                $content,
                $filePath,
                $context
            )
        );
    }

    public static function fileAlreadyExists(string $filePath, ?string $happenedWhen = null): void
    {
        self::add(new AlreadyExist(
            $filePath,
            $happenedWhen ?? ""
        ));
    }

    public static function generatedSuccessfully(string $fileName, ?string $filePath = null, ?string $context = null): void
    {
        self::add(new SuccessGenerating(
            $fileName,
            $filePath,
            $context
        ));
    }

    public static function success(string $message): void
    {
        self::add(
            new SuccessMessage($message)
        );
    }

    public static function error(string $message, ?string $affectedPath = null, ?string $context = null): void
    {
        self::add(new CubeError(
            $message,
            $affectedPath,
            $context
        ));
    }

    public static function failedAppending(string $content, string $filePath = null, ?string $context = null): void
    {
        self::add(
            new FailedAppendContent(
                $content,
                $filePath,
                $context
            )
        );
    }

    public static function notFound(string $content, string $filePath = null, ?string $context = null): void
    {
        self::add(new ContentNotFound(
            $content,
            $filePath,
            $context
        ));
    }

    public static function wrongEnvironment(string $happenedWhen): void
    {
        self::add(new WrongEnvironment(
            $happenedWhen
        ));
    }

    public static function warning(string $message, ?string $context = null): void
    {
        self::add(new CubeWarning($message, $context));
    }
}
