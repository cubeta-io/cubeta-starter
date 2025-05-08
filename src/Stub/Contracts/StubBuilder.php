<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use BadMethodCallException;
use Closure;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\Makable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Mockery\Exception;

abstract class StubBuilder
{
    use Makable;

    protected array $stubProperties = [
        "{{imports}}" => ""
    ];
    public array $imports = [];

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
     * @param string|string[]|TsImportString[]|PhpImportString|PhpImportString[] $import
     * @return $this
     */
    public function import(string|array|PhpImportString|TsImportString $import): static
    {
        $this->imports = array_merge($import, Arr::wrap($this->imports));
        $this->stubProperties["{{imports}}"] = collect($this->imports)
            ->map(fn($import) => $import instanceof PhpImportString || $import instanceof TsImportString
                ? trim($import->__toString())
                : trim($import)
            )->unique(fn($import) => $import instanceof PhpImportString || $import instanceof TsImportString
                ? $import->__toString()
                : $import
            )->implode("\n");

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
            $path->format();
            return true;
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return false;
        }
    }

    /**
     * @param bool|Closure(self $builder):bool|mixed $condition
     * @param Closure(self $builder):void            $then
     * @param Closure(self $builder):void|null       $else
     * @return static
     */
    public function when(mixed $condition, Closure $then, ?Closure $else = null): static
    {
        if (is_callable($condition)) {
            $condition = boolval($condition($this));
        }

        if ($condition) {
            $then($this);
        } else {
            if ($else) {
                $else($this);
            }
        }

        return $this;
    }

    /**
     * change the calling chain target to another object
     * @template VALUE
     * @param VALUE|Closure($this):VALUE $value
     * @return VALUE
     * @noinspection PhpMissingParamTypeInspection
     */
    public function tap($value)
    {
        if (is_callable($value)) {
            return $value($this);
        }

        return $value;
    }
}