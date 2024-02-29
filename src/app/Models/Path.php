<?php

namespace Cubeta\CubetaStarter\app\Models;

use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\LogsMessages\Errors\AlreadyExist;
use Cubeta\CubetaStarter\LogsMessages\Log;

class Path
{
    public string $inProjectPath;
    public string $fullPath;
    public string $fullDirectory;
    public string $inProjectDirectory;
    public ?string $fileName = null;

    /**
     * @param string $inProjectFilePath
     */
    public function __construct(string $inProjectFilePath)
    {
        $this->inProjectPath = $inProjectFilePath;
        $this->fullPath = base_path($inProjectFilePath);
        $this->fullDirectory = dirname($this->fullPath);
        $this->inProjectDirectory = dirname($inProjectFilePath);
        $this->fileName = pathinfo($this->fullPath, PATHINFO_BASENAME) ?? null;
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
        Log::add(new AlreadyExist($this->fullPath, $happenedWhen));
    }

    public function format(): void
    {
        FileUtils::formatFile($this->fullPath);
        Log::add("The File : [{$this->fullPath}] \n Formatted Successfully");
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
