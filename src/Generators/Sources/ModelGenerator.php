<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasModelRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasModelScopeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\TraitString;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Stub\Builders\Models\ModelStubBuilder;
use Cubeta\CubetaStarter\Traits\StringsGenerator;

class ModelGenerator extends AbstractGenerator
{
    public static string $key = 'model';
    private ModelStubBuilder $builder;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null, bool $override = false)
    {
        parent::__construct($fileName, $attributes, $relations, $nullables, $uniques, $actor, $generatedFor, $version, $override);
        $this->builder = ModelStubBuilder::make();
    }

    use StringsGenerator;

    public function run(bool $override = false): void
    {
        $modelPath = $this->table->getModelPath();

        if ($modelPath->exist()) {
            $modelPath->logAlreadyExist("Generating {$this->table->modelName} Model");
            return;
        }

        $modelPath->ensureDirectoryExists();

        $this->generateModelClassAttributes();
        $this->builder
            ->namespace(config('cubeta-starter.model_namespace'))
            ->modelName($this->table->modelName)
            ->generate($modelPath, $this->override);

        CodeSniffer::make()->setModel($this->table)->checkForModelsRelations();
    }


    private function generateModelClassAttributes(): void
    {
        $this->table->attributes()->each(function (CubeAttribute $attribute) {
            $this->builder
                ->fillable($attribute->name)
                ->when(
                    !$attribute->isKey(),
                    fn($builder) => $builder->exportable($attribute->name),
                )->when(
                    $attribute instanceof HasModelCastColumn,
                    fn($builder) => $builder->cast($attribute->modelCastColumn())
                )->when(
                    $attribute instanceof HasDocBlockProperty,
                    fn($builder) => $builder->dockBlock($attribute->docBlockProperty())
                )->when(
                    $attribute->isString(),
                    fn($builder) => $builder->searchable($attribute->name)
                )->when(
                    $attribute->isFile(),
                    fn($builder) => $builder->trait(new TraitString(
                        "HasMedia",
                        new ImportString("App\\Traits\\HasMedia")
                    ))
                )->when(
                    $attribute instanceof HasModelScopeMethod,
                    fn($builder) => $builder->method($attribute->modelScopeMethod())
                );
        });

        $this->table->relations()->each(function (CubeRelation $relation) use (&$relationsFunctions, &$relationsSearchable, &$exportables, &$properties) {
            $relatedTable = $relation->getTable();

            $this->builder->when(
                $relation instanceof HasModelRelationMethod && $relation->exists(),
                fn($builder) => $builder->method($relation->modelRelationMethod()),
            )->when(
                $relation instanceof HasDocBlockProperty && $relation->exists(),
                fn($builder) => $builder->dockBlock($relation->docBlockProperty())
            )->when(
                $relation->isBelongsTo() && $relation->exists() && $relatedTable,
                fn($builder) => $builder->exportable("{$relation->method()}." . $relation->getTable()->titleable()->name),
                fn($builder) => $builder->exportable($relation->keyName())
            )->when(
                $relation->getModelPath()->exist(),
                fn($builder) => $builder->relationSearchable($relation->method(), $relatedTable->searchables())
            );
        });
    }
}
