<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelScopeMethod;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\TraitString;
use Cubeta\CubetaStarter\Stub\Builders\Models\ModelStubBuilder;
use Illuminate\Database\Eloquent\Builder;

class ModelGenerator extends AbstractGenerator
{
    public static string $key = 'model';
    private ModelStubBuilder $builder;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null, bool $override = false)
    {
        parent::__construct($fileName, $attributes, $relations, $nullables, $uniques, $actor, $generatedFor, $version, $override);
        $this->builder = ModelStubBuilder::make();
    }

    public function run(bool $override = false): void
    {
        $modelPath = $this->table->getModelPath();

        $this->generateModelClassAttributes();
        $this->builder
            ->namespace(config('cubeta-starter.model_namespace'))
            ->modelName($this->table->modelName)
            ->generate($modelPath, $this->override);

        CodeSniffer::make()
            ->setModel($this->table)
            ->setActor($this->actor)
            ->checkForModelsRelations();
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
                        new PhpImportString("App\\Traits\\HasMedia")
                    ))
                )->when(
                    $attribute instanceof HasModelScopeMethod,
                    function ($builder) use ($attribute) {
                        $scope = $attribute->modelScopeMethod();
                        $builder->method($scope)
                            ->dockBlock(new DocBlockPropertyString(
                                name: str($scope->name)->replace("scope", "")->camel()->append("()"),
                                type: "Builder",
                                tag: "method",
                                imports: new PhpImportString(Builder::class)
                            ));
                    }
                );
        });

        $this->table->relations()->each(function (CubeRelation $relation) use (&$relationsFunctions, &$relationsSearchable, &$exportables, &$properties) {
            $relatedTable = $relation->relationModel();

            $this->builder->when(
                $relation instanceof HasModelRelationMethod && $relation->exists(),
                fn($builder) => $builder->method($relation->modelRelationMethod()),
            )->when(
                $relation instanceof HasDocBlockProperty && $relation->exists(),
                fn($builder) => $builder->dockBlock($relation->docBlockProperty())
            )->when(
                $relation->isBelongsTo() && $relation->exists(),
                fn($builder) => $builder->exportable("{$relation->method()}." . $relation->relationModel()->titleable()->name),
                fn($builder) => $builder->exportable($relation->keyName())
            )->when(
                $relation->getModelPath()->exist(),
                fn($builder) => $builder->relationSearchable($relation->method(), $relatedTable->searchables())
            );
        });
    }
}
