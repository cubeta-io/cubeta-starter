<?php

namespace Cubeta\CubetaStarter\Stub;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\Makable;
use Exception;

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

        try {
            if (!file_exists($this->source)) {
                throw new Exception("File doesn't exists : [$this->source]");
            }

            FileUtils::ensureDirectoryExists(dirname($this->destination));

            FileUtils::generateFileFromStub(
                [],
                $this->destination,
                $this->source,
                $override
            );

            CubeLog::generatedSuccessfully(pathinfo($this->destination, PATHINFO_BASENAME), $this->destination);
            $this->formatDestination();
            return true;
        } catch (Exception $e) {
            CubeLog::add($e);
            return false;
        }
    }

    private function formatDestination(): void
    {
        if (!file_exists($this->destination)) {
            return;
        }

        if (str($this->destination)->contains('.blade.php')) {
            FileUtils::formatWithPrettier($this->destination);
        } elseif (str($this->destination)->contains(".php")) {
            FileUtils::formatWithPint($this->destination);
        } else {
            FileUtils::formatWithPrettier($this->destination);
        }
    }
}