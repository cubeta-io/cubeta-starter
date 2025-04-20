<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use BadMethodCallException;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
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

    protected array $stubProperties = [];
    protected array $imports = [];

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        if (isset($arguments[0])) {
            $this->stubProperties["{{" . str($name)->snake()->toString() . "}}"] = $arguments[0];
            return $this;
        }

        throw new BadMethodCallException("Call to undefined method $name()");
    }

    /**
     * when providing a string or an array of strings,
     * it must be a full import string like this "use Illuminate\Http\UploadedFile ;"
     * @param string|array|ImportString|ImportString[] $import
     * @return $this
     */
    public function import(string|array|ImportString $import): static
    {
        if (is_array($import)) {
            $this->imports = array_merge($import, $this->imports);
        } else {
            $this->imports[] = $import;
        }

        $this->stubProperties["{{imports}}"] = implode("\n", $this->imports);
        return $this;
    }

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