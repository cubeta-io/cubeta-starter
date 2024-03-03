<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\LogsMessages\CubeLog;
use Cubeta\CubetaStarter\LogsMessages\Errors\AlreadyExist;
use Cubeta\CubetaStarter\LogsMessages\Info\SuccessMessage;

class CubePath
{
    private static ?CubePath $instance = null;
    public string $inProjectPath;
    public ?string $fullPath = null;
    public ?string $fullDirectory = null;
    public ?string $inProjectDirectory = null;
    public ?string $fileName = null;

    public function __construct(string $inProjectFilePath)
    {
        $this->inProjectPath = $inProjectFilePath;
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
        if (!self::$instance) {
            self::$instance = new self($inProjectFilePath);
        } else {
            self::$instance->inProjectPath = $inProjectFilePath;
            self::$instance->initialize();
        }
        return self::$instance;
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
        FileUtils::formatFile($this->fullPath);
        CubeLog::add(new SuccessMessage("The File : [{$this->fullPath}] \n Formatted Successfully"));
    }

    public function getContent(): bool|string
    {
        return file_get_contents($this->fullPath);
    }

    public function putContent($data, int $flags = 0): bool|int
    {
        return file_put_contents($this->fullPath, $data, $flags);
    }
}
