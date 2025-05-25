<?php

namespace Cubeta\CubetaStarter\Stub;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\Makable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Mockery\Exception;

class Publisher
{
    use Makable;

    private string $source;
    private string $destination;

    public function source(CubePath|string $source): static
    {
        $this->source = $source instanceof CubePath
            ? $source->fullPath
            : $source;

        return $this;
    }

    public function destination(CubePath|string $destination): static
    {
        $this->destination = $destination instanceof CubePath
            ? $destination->fullPath
            : $destination;

        return $this;
    }


    public function publish(bool $override = false): bool
    {
        if (file_exists($this->destination) and !$override) {
            CubeLog::fileAlreadyExists($this->destination, "Trying To Generate : [" . $this->destination . "]");
            return false;
        }

        FileUtils::ensureDirectoryExists(dirname($this->destination));

        try {
            FileUtils::generateFileFromStub(
                [],
                $this->destination,
                $this->source,
                $override
            );

            CubeLog::generatedSuccessfully(pathinfo($this->destination, PATHINFO_BASENAME), $this->destination);
            return true;
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return false;
        }
    }
}