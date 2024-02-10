<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

abstract class AbstractGenerator
{
    public static string $key = '';
    protected array $attributes;
    protected array $relations;
    protected array $nullables;
    protected array $uniques;
    protected string $fileName;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [])
    {
        $this->fileName = $fileName;
        $this->attributes = $attributes;
        $this->relations = $relations;
        $this->nullables = $nullables;
        $this->uniques = $uniques;
    }

    public function run(): void
    {

    }

    public function generatedFileName(): string
    {
        return "";
    }

    protected function stubsPath(): string
    {
        return "";
    }

    protected function tableName(string $name): string
    {
        return strtolower(Str::plural(Str::snake($name)));
    }

    protected function columnName(string $name): string
    {
        return strtolower(Str::snake($name));
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0775, true, true);
        }
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    protected function generateFileFromStub(array $stubProperties, string $path, bool $override = false): void
    {
        CreateFile::make()
            ->setPath($path)
            ->setStubPath($this->stubsPath())
            ->setStubProperties($stubProperties)
            ->callFileGenerateFunctions($override);
    }
}