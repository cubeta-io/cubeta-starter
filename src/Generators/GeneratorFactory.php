<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Generators\Sources\FactoryGenerator;
use Cubeta\CubetaStarter\Generators\Sources\MigrationGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ModelGenerator;
use Cubeta\CubetaStarter\Generators\Sources\RepositoryGenerator;
use Cubeta\CubetaStarter\Generators\Sources\RequestGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ResourceGenerator;
use Cubeta\CubetaStarter\Generators\Sources\SeederGenerator;
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
            default => throw new \Error("Not supported generator"),
        };
        $generator->run();
    }
}