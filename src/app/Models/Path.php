<?php

namespace Cubeta\CubetaStarter\app\Models;

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
}
