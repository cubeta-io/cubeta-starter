<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\LogsMessages\CubeLog;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Mockery\Exception;

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

        $this->table = $this->addToJsonFile();
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

    /**
     * @param array $stubProperties
     * @param string $path
     * @param bool $override
     * @param string|null $otherStubsPath
     * @return void
     */
    protected function generateFileFromStub(array $stubProperties, string $path, bool $override = false, string $otherStubsPath = null): void
    {
        try {
            FileUtils::generateFileFromStub($stubProperties, $path, $otherStubsPath ?? $this->stubsPath(), $override);
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return;
        }
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
}
