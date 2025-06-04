<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Stub\Builders\Repositories\BaseRepositoryStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Services\BaseServiceStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Traits\HasMediaTraitStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Traits\MakableTraitStubBuilder;

abstract class AbstractGenerator
{
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
    protected bool $override;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null, bool $override = false)
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
        $this->override = $override;

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

    public function run(): void
    {

    }

    protected function publishBaseService(): void
    {
        $publishPath = CubePath::make(config('cubeta-starter.service_path') . "/Contracts/BaseService.php");
        BaseServiceStubBuilder::make()
            ->namespace(config('cubeta-starter.service_namespace'))
            ->repositoryNamespace(config('cubeta-starter.repository_namespace'))
            ->generate($publishPath, $this->override);
    }

    protected function publishBaseRepository(): void
    {
        $publishPath = CubePath::make(config('cubeta-starter.repository_path') . "/Contracts/BaseRepository.php");
        BaseRepositoryStubBuilder::make()
            ->namespace(config('cubeta-starter.repository_namespace'))
            ->generate($publishPath, $this->override);
    }

    protected function publishMakableTrait(): void
    {
        $publishPath = CubePath::make(config('cubeta-starter.trait_path') . "/Makable.php");
        MakableTraitStubBuilder::make()
            ->namespace(config('cubeta-starter.trait_namespace'))
            ->generate($publishPath, $this->override);
    }

    protected function publishHasMediaTrait(): void
    {
        $mediaTraitPath = CubePath::make(config('cubeta-starter.trait_path') . '/HasMedia.php');
        HasMediaTraitStubBuilder::make()
            ->namespace(config('cubeta-starter.trait_namespace'))
            ->generate($mediaTraitPath, $this->override);
    }

    protected function registerHelpersFile(): void
    {
        $helperPath = CubePath::make('app/Helpers/helpers.php');

        if (!$helperPath->exist()) {
            return;
        }

        $composerPath = CubePath::make("composer.json");
        $json = json_decode($composerPath->getContent(), true);
        if (!$json) {
            CubeLog::error("Failed to register helpers file in the composer.json file", $composerPath->fullPath, "Installing api tools");
            return;
        }

        if (isset($json['autoload-dev']['files'])) {
            if (!in_array("app/Helpers/helpers.php", $json['autoload-dev']['files'])) {
                $json['autoload-dev']['files'][] = "app/Helpers/helpers.php";
            }
        } else {
            $json['autoload-dev']['files'] = [
                "app/Helpers/helpers.php"
            ];
        }

        $composerPath->putContent(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        FileUtils::executeCommandInTheBaseDirectory("composer dump-autoload");

        CubeLog::info(
            "Helpers file registered successfully",
            "Installing api tools"
        );
    }
}
