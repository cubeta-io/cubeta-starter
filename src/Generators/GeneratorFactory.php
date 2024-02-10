<?php

namespace Cubeta\CubetaStarter\Generators;

use Cubeta\CubetaStarter\Generators\Sources\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\MigrationGenerator;
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
            MigrationGenerator::$key => new MigrationGenerator($fileName, $attributes, $relations, $nullables, $uniques),
            default => throw new \Error("Not supported generator"),
        };
        $generator->run();
    }
}