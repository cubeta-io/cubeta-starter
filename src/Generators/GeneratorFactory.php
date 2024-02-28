<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Generators\Sources\{
    ApiControllerGenerator,
    FactoryGenerator,
    MigrationGenerator,
    ModelGenerator,
    RepositoryGenerator,
    RequestGenerator,
    ResourceGenerator,
    SeederGenerator,
    ServiceGenerator
};
use Error;
use Throwable;

class GeneratorFactory
{
    private string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     * @throws Throwable
     */
    public function make(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = []): void
    {
        $generator = match ($this->source) {
            MigrationGenerator::$key => new MigrationGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques
            ),
            ModelGenerator::$key => new ModelGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
            ),
            RequestGenerator::$key => new RequestGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
            ),
            ResourceGenerator::$key => new ResourceGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
            ),
            FactoryGenerator::$key => new FactoryGenerator(
                fileName: $fileName,
                attributes: $attributes,
                relations: $relations,
                nullables: $nullables,
                uniques: $uniques,
            ),
            SeederGenerator::$key => new SeederGenerator(
                fileName: $fileName,
            ),
            RepositoryGenerator::$key => new RepositoryGenerator(
                fileName: $fileName,
            ),
            ServiceGenerator::$key => new ServiceGenerator(
                fileName: $fileName,
            ),
            ApiControllerGenerator::$key => new ApiControllerGenerator(
                fileName: $fileName,
            ),
            default => throw new Error("Not supported generator"),
        };
        $generator->run();
    }
}
