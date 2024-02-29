<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

class CreateFile
{
    private static $instance;
    /**
     * The filesystem instance.
     *
     * @property Filesystem
     */
    protected FileSystem $files;
    /**
     * The path to create file at
     *
     * @property string $path
     */
    private string $path;

    /**
     * Path to the stub file
     *
     * @property string $stubPath
     */
    private string $stubPath;

    /**
     * The stubProperties to replace
     *
     * @property array $stubProperties
     */
    private array $stubProperties;

    private function __construct()
    {
        //
    }

    /**
     * @param bool $override
     * @return $this
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function callFileGenerateFunctions(bool $override = false): static
    {
        $this->files = app()->make(Filesystem::class);
        if (!$override) {
            if ($this->files->exists($this->path)) {
                return $this;
            }
        }
        $this->createStub();

        return $this;
    }

    public static function make(): CreateFile
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create the stub file by replacing all the keys
     *
     * @throws FileNotFoundException
     */
    private function createStub(): void
    {
        $stub = $this->getStub();
        $populatedStub = $this->populateStub($stub);
        $this->writeFile($populatedStub);
    }

    /**
     * @throws FileNotFoundException
     */
    private function getStub(): string
    {
        return $this->files->get($this->stubPath);
    }

    /**
     * @return array|string|string[]
     */
    private function populateStub(string $stub): array|string
    {
        foreach ($this->stubProperties as $replacer => $replaceBy) {
            $stub = str_replace($replacer, $replaceBy, $stub);
        }

        return $stub;
    }

    /**
     * Write to the file specified in the path
     *
     * @param string|mixed $stub
     */
    private function writeFile(mixed $stub): void
    {
        $this->files->put($this->path, $stub);
    }

    /**
     * @return $this
     */
    public function setPath($path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return $this
     */
    public function setStubPath($stubPath): static
    {
        $this->stubPath = $stubPath;

        return $this;
    }

    /**
     * @return $this
     */
    public function setStubProperties($stubProperties): static
    {
        $this->stubProperties = $stubProperties;

        return $this;
    }
}
