<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Generators\Sources\MigrationGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ModelGenerator;
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
    public function make(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], array $actors = [], string $generatedFor = ''): void
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
                actors: $actors,
                generatedFor: $generatedFor
            ),
            default => throw new \Error("Not supported generator"),
        };
        $generator->run();
    }
}