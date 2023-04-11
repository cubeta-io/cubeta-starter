<?php

namespace Cubeta\CubetaStarter;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

class CreateFile
{
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

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function __construct(array $stubProperties, string $path, string $stubPath)
    {
        $this->stubPath = $stubPath;
        $this->stubProperties = $stubProperties;
        $this->path = $path;
        $this->files = app()->make(Filesystem::class);
        $this->fileExists();
        $this->createStub();
    }

    /**
     * Check if file already exists
     */
    private function fileExists(): void
    {
        $this->files->exists($this->path) ? new Exception('The class exists!') : false;
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
}
