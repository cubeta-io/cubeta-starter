<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;

class CubePath
{
    public string $inProjectPath;
    public ?string $fullPath = null;
    public ?string $fullDirectory = null;
    public ?string $inProjectDirectory = null;
    public ?string $fileName = null;

    /**
     * the file directory inside your project starting from the root directory
     * @param string $inProjectFilePath
     */
    public function __construct(string $inProjectFilePath)
    {
        $inProjectFilePath = str_replace('/', DIRECTORY_SEPARATOR, $inProjectFilePath);
        $inProjectFilePath = str_replace(base_path(), '', $inProjectFilePath);
        $this->inProjectPath = str_replace('//', '/', $inProjectFilePath);
        $this->initialize();
    }

    private function initialize(): void
    {
        $this->fullPath = base_path($this->inProjectPath);
        $this->fullDirectory = dirname($this->fullPath);
        $this->inProjectDirectory = dirname($this->inProjectPath);
        $this->fileName = pathinfo($this->fullPath, PATHINFO_BASENAME) ?? null;
    }

    public static function make(string $inProjectFilePath): CubePath
    {
        return new self($inProjectFilePath);
    }

    public function ensureDirectoryExists(): void
    {
        FileUtils::ensureDirectoryExists($this->fullDirectory);
    }

    public function exist(): bool
    {
        return file_exists($this->fullPath);
    }

    public function logAlreadyExist(?string $happenedWhen = null): void
    {
        CubeLog::add(new AlreadyExist($this->fullPath, $happenedWhen));
    }

    public function format(): void
    {
        if (str($this->fileName)->contains('.blade.php')) {
            FileUtils::formatWithPrettier($this->fullPath);
        }

        if ($this->getFileExtension() == "php") {
            FileUtils::formatWithPint($this->fullPath);
        } else {
            FileUtils::formatWithPrettier($this->fullPath);
        }
    }

    public function getContent(): bool|string
    {
        return file_get_contents($this->fullPath);
    }

    public function putContent($data, int $flags = 0): bool|int
    {
        return file_put_contents($this->fullPath, $data, $flags);
    }

    public function getFileExtension(): ?string
    {
        return pathinfo($this->fullPath, PATHINFO_EXTENSION) ?: null;
    }

    /**
     * @param non-empty-string $stubPath
     * @return string
     */
    public static function stubPath(string $stubPath): string
    {
        return realpath(
            __DIR__ .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            'Stub' .
            DIRECTORY_SEPARATOR .
            'stubs' .
            (
            (str_starts_with($stubPath, "/") || str_starts_with($stubPath, "\\"))
                ? ''
                : DIRECTORY_SEPARATOR
            ) .
            str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                str_replace(
                    '\\',
                    DIRECTORY_SEPARATOR,
                    $stubPath
                )
            )
        );
    }

    public function __toString(): string
    {
        return $this->fullPath;
    }

    public function getFileNameWithoutExtension(): string
    {
        return str($this->fileName)
            ->replace('.' . $this->getFileExtension(), '')
            ->toString();
    }
}
