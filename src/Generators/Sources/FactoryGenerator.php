<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Stub\Builders\Factories\FactoryStubBuilder;
use Cubeta\CubetaStarter\Traits\StringsGenerator;

class FactoryGenerator extends AbstractGenerator
{
    use StringsGenerator;

    public static string $key = 'factory';
    private FactoryStubBuilder $builder;

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', ?string $version = null, bool $override = false)
    {
        parent::__construct(
            $fileName,
            $attributes,
            $relations,
            $nullables,
            $uniques,
            $actor,
            $generatedFor,
            $version,
            $override
        );

        $this->builder = FactoryStubBuilder::make();
    }

    public function run(bool $override = false): void
    {
        $factoryPath = $this->table->getFactoryPath();

        $this->generateFields();
        $this->builder
            ->namespace(config('cubeta-starter.factory_namespace'))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->modelName($this->table->modelName)
            ->generate($factoryPath, $this->override);

        CodeSniffer::make()->setModel($this->table)->checkForFactoryRelations();
    }

    private function generateFields(): void
    {
        $this->table->attributes()->each(function (CubeAttribute $attribute) {
            if ($attribute instanceof HasFakeMethod) {
                $this->builder->row($attribute->fakeMethod());
            }
        });

        $this->table->relations()->each(function (CubeRelation $rel) {
            if (
                ($rel->isHasMany() || $rel->isManyToMany())
                && $rel->getModelPath()->exist()
                && $rel instanceof HasFactoryRelationMethod
            ) {
                $this->builder->method($rel->factoryRelationMethod());
            }
        });
    }
}
