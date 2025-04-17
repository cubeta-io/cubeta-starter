<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\Makable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Mockery\Exception;

abstract class StubBuilder
{
    use Makable;

    abstract protected function stubPath(): string;

    abstract protected function getStubPropertyArray(): array;

    public function toString(): string
    {
        return FileUtils::generateStringFromStub(
            $this->stubPath(),
            $this->getStubPropertyArray()
        );
    }

    public function generate(CubePath $path, bool $override = false): bool
    {
        if ($path->exist() and !$override) {
            CubeLog::fileAlreadyExists($path->fullPath, "Trying To Generate : [" . $path->fileName . "]");
            return false;
        }

        $path->ensureDirectoryExists();

        try {
            FileUtils::generateFileFromStub(
                $this->getStubPropertyArray(),
                $path->fullPath,
                $this->stubPath(),
                $override
            );
            CubeLog::generatedSuccessfully($path->fileName, $path->fullPath);
            return true;
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return false;
        }
    }
}