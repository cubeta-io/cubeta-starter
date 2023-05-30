<?php

namespace Cubeta\CubetaStarter;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

class CreateFile
{
    private static $instance;

    /**
     * The path to create file at
     *
     * @property string $path
     */
    private string $path;

    /**
     * The stubProperties to replace
     *
     * @property array $stubProperties
     */
    private array $stubProperties;

    /**
     * Path to the stub file
     *
     * @property string $stubPath
     */
    private string $stubPath;

    /**
     * The filesystem instance.
     *
     * @property Filesystem
     */
    protected FileSystem $files;

    private function __construct()
    {
        //
    }

    /**
     * @throws Exception
     */
    private function fileExists(): void
    {
        if ($this->files->exists($this->path)) {
            throw new Exception('The class exists!');
        }
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
     * @throws FileNotFoundException
     */
    private function getStub(): string
    {
        return $this->files->get($this->stubPath);
    }

    /**
     * Write to the file specified in the path
     *
     * @param  string|mixed  $stub
     */
    private function writeFile(mixed $stub): void
    {
        $this->files->put($this->path, $stub);
    }

    public static function make(): CreateFile
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
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
    public function setStubProperties($stubProperties): static
    {
        $this->stubProperties = $stubProperties;

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
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function callFileGenerateFunctions(): static
    {
        $this->files = app()->make(Filesystem::class);
        $this->fileExists();
        $this->createStub();

        return $this;
    }
}
