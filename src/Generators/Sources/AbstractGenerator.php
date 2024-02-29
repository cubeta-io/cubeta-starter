<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class AbstractGenerator
{
    public static string $key = '';
    public static string $configPath = '';
    public array $logs = [];
    protected array $attributes;
    protected array $relations;
    protected array $nullables;
    protected array $uniques;
    protected string $generatedFor;
    protected ?string $actor = null;
    protected string $fileName;
    protected CubetaTable $table;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '')
    {
        $this->fileName = trim($fileName);
        $this->attributes = $attributes;
        $this->relations = $relations;
        $this->nullables = $nullables;
        $this->uniques = $uniques;
        $this->actor = $actor;
        $this->generatedFor = $generatedFor === '' ? ContainerType::BOTH : $generatedFor;

        $this->mergeRelations();

        $this->table = CubetaTable::create(
            $this->fileName,
            $this->attributes,
            $this->relations,
            $this->uniques,
            $this->nullables
        );
    }

    protected function mergeRelations(): void
    {
        $belongToRelations = [];
        foreach ($this->attributes as $attribute => $type) {
            if ($type === ColumnTypeEnum::KEY->value) {
                $belongToRelations["$attribute"] = RelationsTypeEnum::BelongsTo->value;
            }
        }
        $this->relations = array_merge($this->relations, $belongToRelations);
    }

    public function run(): void
    {

    }

    function addImportStatement(string $importStatement, string $filePath): void
    {
        $contents = file_get_contents($filePath);

        if (Str::contains($contents, $importStatement)) {
            return;
        }

        // Check if import statement already exists
        $fileLines = File::lines($filePath);
        foreach ($fileLines as $line) {
            $cleanLine = trim($line);
            if (Str::contains($cleanLine, $importStatement)) {
                return;
            }
        }

        // Find the last "use" statement and insert the new import statement after it
        $lastUseIndex = strrpos($contents, 'use ');
        $insertIndex = $lastUseIndex !== false ? $lastUseIndex - 1 : 0;
        $contents = substr_replace($contents, "\n" . $importStatement . "\n", $insertIndex, 0);

        // Write the updated contents back to the file
        file_put_contents($filePath, $contents);
    }

    protected function getGeneratingPath(string $fileName): string
    {
        $path = base_path(config(self::$configPath)) . $this->getAdditionalPath();
        $this->ensureDirectoryExists($path);
        return "{$path}/{$fileName}" . '.php';
    }

    protected function getAdditionalPath(): string
    {
        return "";
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0775, true, true);
        }
    }

    /**
     * @param array $stubProperties
     * @param string $path
     * @param bool $override
     * @param string|null $otherStubsPath
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    protected function generateFileFromStub(array $stubProperties, string $path, bool $override = false, string $otherStubsPath = null): void
    {
        CreateFile::make()
            ->setPath($path)
            ->setStubPath($otherStubsPath ?? $this->stubsPath())
            ->setStubProperties($stubProperties)
            ->callFileGenerateFunctions($override);
    }

    protected function stubsPath(): string
    {
        return "";
    }

    /**
     * @return CubetaTable
     */
    protected function addToJsonFile(): CubetaTable
    {
        return Settings::make()->serialize($this->fileName, $this->attributes, $this->relations, $this->nullables, $this->uniques);
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
