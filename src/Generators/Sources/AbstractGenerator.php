<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

abstract class AbstractGenerator
{
    public static string $key = '';
    public static string $configPath = '';
    protected array $attributes;
    protected array $relations;
    protected array $nullables;
    protected array $uniques;
    protected array $options;
    protected string $generatedFor;
    protected array $actors;
    protected string $fileName;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], array $options = [], array $actors = [], string $generatedFor = '')
    {
        $this->fileName = trim($fileName);
        $this->attributes = $attributes;
        $this->relations = $relations;
        $this->nullables = $nullables;
        $this->uniques = $uniques;
        $this->options = $options;
        $this->actors = $actors;
        $this->generatedFor = $generatedFor === '' ? ContainerType::BOTH : $generatedFor;
    }

    public function run(): void
    {

    }

    protected function generatedFileName(): string
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

    protected function modelName(string $name): string
    {
        return ucfirst(Str::singular(Str::studly($name)));
    }

    protected function columnName(string $name): string
    {
        return strtolower(Str::snake($name));
    }

    protected function getGeneratingPath(string $fileName): string
    {
        $path = base_path(config(self::$configPath));
        $this->ensureDirectoryExists($path);
        return "{$path}/{$fileName}" . '.php';
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

    protected function addToJsonFile(): void
    {
        Settings::make()->serialize($this->fileName, $this->attributes, $this->relations, $this->nullables, $this->uniques);
    }

    protected function formatFile(string $filePath): ?string
    {
        $command = base_path() . "./vendor/bin/pint {$filePath}";
        $output = $this->executeCommandInTheBaseDirectory($command);
        if (is_string($output)) return $output;
        return null;
    }

    private function executeCommandInTheBaseDirectory(string $command): bool|string|null
    {
        if (app()->environment('local')) {
            $rootDirectory = base_path();
            $fullCommand = sprintf('cd %s && %s', escapeshellarg($rootDirectory), $command);

            return shell_exec($fullCommand);
        }
        return false;
    }
}