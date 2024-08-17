<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;
use Cubeta\CubetaStarter\Logs\Info\SuccessGenerating;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Mockery\Exception;

abstract class AbstractGenerator
{
    public static string $key = '';
    public string $type = '';
    public FrontendTypeEnum $frontType;

    public array $logs = [];
    protected array $attributes;
    protected array $relations;
    protected array $nullables;
    protected array $uniques;
    protected string $generatedFor;
    protected ?string $actor = null;
    protected string $fileName;
    protected string $version;
    protected CubeTable $table;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null)
    {
        $this->fileName = trim($fileName);
        $this->attributes = $attributes;
        $this->relations = $relations;
        $this->nullables = $nullables;
        $this->uniques = $uniques;
        $this->actor = $actor;
        $this->generatedFor = $generatedFor === '' ? ContainerType::BOTH : $generatedFor;
        $this->version = $version ?? config('cubeta-starter.version');
        $this->frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        $this->mergeRelations();

        if ($this->type != 'installer') {
            $this->table = Settings::make()->serialize($this->fileName, $this->attributes, $this->relations, $this->nullables, $this->uniques, $this->version);
        }
    }

    protected function mergeRelations(): void
    {
        $belongToRelations = [];
        foreach ($this->attributes as $attribute => $type) {
            if ($type == ColumnTypeEnum::KEY->value) {
                $belongToRelations["$attribute"] = RelationsTypeEnum::BelongsTo->value;
            }
        }
        $this->relations = array_merge($this->relations, $belongToRelations);
    }

    public function run(bool $override = false): void
    {

    }

    /**
     * @param array       $stubProperties
     * @param string      $path
     * @param bool        $override
     * @param string|null $otherStubsPath
     * @return void
     */
    protected function generateFileFromStub(array $stubProperties, string $path, bool $override = false, string $otherStubsPath = null): void
    {
        if (file_exists($path) and !$override) {
            CubeLog::add(new AlreadyExist($path, "Trying To Generate : [" . pathinfo($path, PATHINFO_BASENAME) . "]"));
            return;
        }

        CubePath::make($path)->ensureDirectoryExists();

        try {
            FileUtils::generateFileFromStub($stubProperties, $path, $otherStubsPath ?? $this->stubsPath(), $override);
            CubeLog::add(new SuccessGenerating(pathinfo($path, PATHINFO_BASENAME), $path));
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return;
        }
    }

    protected function stubsPath(): string
    {
        return "";
    }
}
